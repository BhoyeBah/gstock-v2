<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\walletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $type)
    {
        //

        $this->validateType($type);

        $payments = Payment::with(['invoice', 'contact'])
            ->where('payment_source', rtrim($type, 's'))
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Charger les factures du même type (client ou supplier)
        $invoices = Invoice::where('tenant_id', auth()->user()->tenant_id)
            ->where('type', rtrim($type, 's'))
            ->orderBy('invoice_number', 'desc')
            ->where('balance', '>', 0)
            ->get();
        $wallets = Wallet::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();

        return view('back.payments.index', compact('payments', 'wallets', 'type', 'invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {

        $invoice = Invoice::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($request->invoice_id);
        $amountPaid = (int) $request->input('amount_paid');
        $payment_date = $request->input('payment_date');

        if ($amountPaid > $invoice->balance) {
            return back()->with('error', "Montant trop élevé. Solde restant : {$invoice->balance} FCFA");
        }

        try {
            DB::beginTransaction();

            $wallet = Wallet::where('tenant_id', auth()->user()->tenant_id)
                ->where('id', $request->wallet_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->type !== 'client' && $wallet->current_balance < $amountPaid) {
                return back()->with('error', 'Solde insuffisant dans le wallet '.$wallet->name);
            }
            $beforeBalance = $wallet->current_balance;

            if ($invoice->type === 'client') {
                $wallet->increment('current_balance', $amountPaid);
            } else {
                $wallet->decrement('current_balance', $amountPaid);
            }

            $invoice->balance -= $amountPaid;
            if ($invoice->balance > 0) {
                $invoice->status = 'partial';

            } elseif ($invoice->balance == 0) {
                $invoice->status = 'paid';
            }

            $invoice->save();
            $payment = Payment::create([
                'wallet_id' => $wallet->id,
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'contact_id' => $invoice->contact_id,
                'amount_paid' => $amountPaid,
                'remaining_amount' => $invoice->balance,
                'payment_date' => $payment_date,
                'payment_type' => $wallet->name,
                'payment_source' => $invoice->type,
            ]);

            // Enregistrement transaction wallet (IN ou OUT)
            walletTransaction::create([
                'tenant_id' => $invoice->tenant_id,
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'user_id' => auth()->user()->id,
                'type' => $invoice->type === 'client' ? 'in' : 'out',
                'transaction_type' => 'payment',
                'amount' => $amountPaid,
                'balance_before' => $beforeBalance,
                'balance_after' => $wallet->current_balance,
                'source_type' => $wallet->name,
                'source_id' => $invoice->id,
                'description' => 'Paiement '.$invoice->invoice_number,
                'note' => 'Paiement '.($invoice->type === 'client' ? 'client' : 'fournisseur').' sur facture '.$invoice->invoice_number,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Erreur lors du paiement : '.$e->getMessage());
        }

        return back()->with('success', "Paiement de $amountPaid FCFA de la facture numéro $invoice->invoice_number enregistré avec succès !");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $type, Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $type, Payment $payment)
    {
        $this->validateType($type);

        if ($payment->tenant_id !== auth()->user()->tenant_id) {
            abort(403, "Action non autorisée.");
        }

        if ($payment->amount_paid <= 0) {
            return back()->with('error', 'Vous ne pouvez pas supprimer le paiement initial.');
        }

        try {
            DB::transaction(function () use ($payment) {
                $invoice = Invoice::where('tenant_id', auth()->user()->tenant_id)
                    ->whereKey($payment->invoice_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $wallet = Wallet::where('tenant_id', auth()->user()->tenant_id)
                    ->whereKey($payment->wallet_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $beforeBalance = $wallet->current_balance;

                if ($invoice->type === 'client') {
                    $wallet->decrement('current_balance', $payment->amount_paid);
                    $transactionType = 'out';
                } else {
                    $wallet->increment('current_balance', $payment->amount_paid);
                    $transactionType = 'in';
                }

                walletTransaction::create([
                    'tenant_id' => $payment->tenant_id,
                    'wallet_id' => $wallet->id,
                    'payment_id' => $payment->id,
                    'user_id' => auth()->user()->id,
                    'type' => $transactionType,
                    'transaction_type' => 'payment_reversal',
                    'amount' => $payment->amount_paid,
                    'balance_before' => $beforeBalance,
                    'balance_after' => $wallet->current_balance,
                    'source_type' => $wallet->name,
                    'source_id' => $invoice->id,
                    'description' => 'Annulation du paiement '.$payment->id,
                    'note' => 'Annulation du paiement #' . $payment->id . ' sur facture ' . $invoice->invoice_number,
                ]);

                $invoice->balance += $payment->amount_paid;

                if ($invoice->balance >= $invoice->total_invoice) {
                    $invoice->status = 'validated';
                } elseif ($invoice->balance > 0) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'paid';
                }

                $invoice->save();
                $payment->delete();
            });

            return back()->with('success', 'Paiement supprimé et solde/wallet réajustés avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression du paiement : '.$e->getMessage());
        }
    }

    protected function validateType(string $type): void
    {
        if (! in_array($type, ['clients', 'suppliers'])) {
            abort(404, 'Page inexistante');
        }
    }
}
