<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\walletTransaction;
use App\Services\DocumentNumberService;
use App\Services\InvoicePaymentStatusService;
use App\Services\PaymentCancellationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly InvoicePaymentStatusService $invoicePaymentStatusService,
        private readonly PaymentCancellationService $paymentCancellationService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(string $type)
    {
        $this->validateType($type);

        $payments = Payment::with(['invoice', 'contact'])
            ->where('payment_source', rtrim($type, 's'))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Charger les factures du même type (client ou supplier)
        $invoices = Invoice::where('type', rtrim($type, 's'))
            ->orderBy('invoice_number', 'desc')
            ->where('balance', '>', 0)
            ->get();
        $wallets = Wallet::orderBy('name')->get();

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
        $this->validateType($request->route('type'));

        $tenantId = $request->user()->tenant_id;
        $invoiceType = rtrim($request->route('type'), 's');
        $amountPaid = (int) $request->input('amount_paid');

        try {
            $invoice = null;
            $payment = null;

            DB::transaction(function () use ($request, $tenantId, $invoiceType, $amountPaid, &$invoice, &$payment) {
                $invoice = Invoice::where('tenant_id', $tenantId)
                    ->where('type', $invoiceType)
                    ->whereKey($request->invoice_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($amountPaid > $invoice->balance) {
                    throw ValidationException::withMessages([
                        'amount_paid' => "Montant trop élevé. Solde restant : {$invoice->balance} FCFA",
                    ]);
                }

                $wallet = Wallet::where('tenant_id', $tenantId)
                    ->whereKey($request->wallet_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($invoice->type !== Invoice::TYPE_CLIENT && $wallet->current_balance < $amountPaid) {
                    throw ValidationException::withMessages([
                        'wallet_id' => 'Solde insuffisant dans le wallet '.$wallet->name,
                    ]);
                }

                $beforeBalance = (int) $wallet->current_balance;

                if ($invoice->type === Invoice::TYPE_CLIENT) {
                    $wallet->current_balance = $beforeBalance + $amountPaid;
                    $legacyType = 'in';
                    $transactionType = 'payment_in';
                } else {
                    $wallet->current_balance = $beforeBalance - $amountPaid;
                    $legacyType = 'out';
                    $transactionType = 'payment_out';
                }

                $wallet->save();

                $payment = Payment::create([
                    'payment_number' => $this->documentNumberService->generate('payment', $invoice->tenant),
                    'wallet_id' => $wallet->id,
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $invoice->tenant_id,
                    'contact_id' => $invoice->contact_id,
                    'amount_paid' => $amountPaid,
                    'remaining_amount' => max($invoice->balance - $amountPaid, 0),
                    'payment_date' => $request->input('payment_date'),
                    'payment_type' => $wallet->name,
                    'payment_source' => $invoice->type,
                    'status' => 'completed',
                ]);

                walletTransaction::create([
                    'tenant_id' => $tenantId,
                    'wallet_id' => $wallet->id,
                    'payment_id' => $payment->id,
                    'user_id' => $request->user()->id,
                    'type' => $legacyType,
                    'transaction_type' => $transactionType,
                    'amount' => $amountPaid,
                    'balance_before' => $beforeBalance,
                    'balance_after' => $wallet->current_balance,
                    'source_type' => Payment::class,
                    'source_id' => $payment->id,
                    'note' => 'Paiement '.($invoice->type === Invoice::TYPE_CLIENT ? 'client' : 'fournisseur').' sur facture '.$invoice->invoice_number,
                    'description' => 'Paiement '.($invoice->type === Invoice::TYPE_CLIENT ? 'client' : 'fournisseur').' sur facture '.$invoice->invoice_number,
                ]);

                $this->invoicePaymentStatusService->recalculate($invoice);
                $payment->remaining_amount = $invoice->fresh()->balance;
                $payment->save();
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {

            return back()->with('error', 'Erreur lors du paiement : '.$e->getMessage());
        }

        return back()->with('success', "Paiement {$payment->payment_number} de $amountPaid FCFA sur la facture $invoice->invoice_number enregistre avec succes !");
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
    public function destroy(Request $request, string $type, Payment $payment)
    {
        $this->validateType($type);

        try {
            $this->paymentCancellationService->cancel(
                $payment,
                $request->user(),
                $request->input('cancellation_reason')
            );

            return back()->with('success', 'Paiement annulé et wallet réajusté avec succès.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l’annulation du paiement : '.$e->getMessage());
        }

    }

    protected function validateType(string $type): void
    {
        if (! in_array($type, ['clients', 'suppliers'])) {
            abort(404, 'Page inexistante');
        }
    }
}
