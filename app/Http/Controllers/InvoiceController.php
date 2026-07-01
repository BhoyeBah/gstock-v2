<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Http\Requests\ReturnRequestProduct;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Batch;
use App\Models\Contact;
use App\Models\CustomerCreditNote;
use App\Models\CustomerReturn;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnProduct;
use App\Models\SupplierCreditNote;
use App\Models\SupplierReturn;
use App\Models\Wallet;
use App\Models\walletTransaction;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public InvoiceService $service;

    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $type)
    {
        $this->validateType($type);
        $required_permission_name = $type == 'clients' ? 'manage_client_invoices' : 'manage_supplier_invoices';
        if (! auth()->user()->can($required_permission_name)) {
            abort(403, "Vous n'avez pas la permission d'acceder à cette fonctionnalité");
        }
        $status_list = ['draft', 'validated', 'partial', 'paid', 'credited', 'partially_credited', 'cancelled'];

        $status = $request->input('status');

        $query = in_array($status, $status_list) ? Invoice::type(rtrim($type, 's'))->where('status', $status) : Invoice::type(rtrim($type, 's'));

        $search_number = $request->input('search_number');
        $search_contact = $request->input('search_contact');

        $status = in_array($status, $status_list) ? $status : 'draft';

        if (! empty($search_contact)) {
            $query = $query->whereHas('contact', function ($query) use ($search_contact) {
                $query->where('fullname', 'like', "%$search_contact%")
                    ->orWhere('phone_number', 'like', "%$search_contact%");
            });
        }

        if (! empty($search_number)) {
            $query = $query->where('invoice_number', 'like', "%$search_number%");

        }

        $query = $query->orderBy('created_at', 'desc');

        $invoiceType = $type === 'clients' ? 'Clients' : 'Fournisseurs';

        $invoices = $query->paginate(10);
        $products = Product::orderBy('name', 'ASC')->get();

        $contacts = Contact::orderBy('fullname')->type(rtrim($type, 's'))->get();

        $warehouses = Warehouse::orderBy('name', 'ASC')->get();
        $allInvoices = Invoice::where('type', rtrim($type, 's'))->get();
        $wallets = Wallet::orderBy('name')->get();

        return view('back.invoices.index', compact('invoices', 'wallets', 'invoiceType', 'type', 'products', 'contacts', 'warehouses', 'allInvoices'));

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
    public function store(StoreInvoiceRequest $request)
    {
        //
        $path = $request->path();
        $type = $request->type;

        // Vérifie que le type est bien dans le path
        if (! str_contains($path, $type)) {
            abort(403, "Action non autorisée : le type ne correspond pas à l'URL.");
        }
        $this->validateType($type.'s');
        $this->service->createInvoice($request->validated());

        return back()->with('success', 'Facture enregistré avec succés');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $type, string $id)
    {
        //
        $this->validateType($type);
        $tenantId = auth()->user()->tenant_id;
        $invoice = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->with(['items.returns', 'items.product', 'items.warehouse', 'contact', 'quote', 'saleOrder'])
            ->findOrFail($id);

        $this->checkAuthorization($invoice, $type);

        $batches = Batch::where('invoice_id', $invoice->id)->orderBy('remaining')->paginate(10);
        $payments = Payment::where('invoice_id', $invoice->id)->paginate(10);
        $availableCreditNotes = $this->availableCreditNotes($invoice);

        return view('back.invoices.show', compact('invoice', 'batches', 'payments', 'type', 'availableCreditNotes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $type, Invoice $invoice)
    {

        $this->validateType($type);
        $this->checkAuthorization($invoice, $type);

        $products = Product::all();
        $contacts = Contact::type(rtrim($type, 's'))->get();
        $warehouses = Warehouse::all();
        return view('back.invoices.edit', compact('invoice', 'products', 'warehouses', 'contacts', 'type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreInvoiceRequest $request, string $type, Invoice $invoice)
    {
        //

        $this->validateType($type);
        $this->checkAuthorization($invoice, $type);
        $invoice->delete();
        $new_invoice = $this->service->createInvoice($request->validated());

        return redirect()->route('invoices.index', [$type, $new_invoice->id])->with('success', 'Facture modifier avec succès');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $type, Invoice $invoice)
    {
        $current_user = auth()->user();

        $error_message = "Vous n'avez pas le droit du supprimer cette facture";
        //
        if ($invoice->status !== 'draft') {
            abort(403, 'Seule les factures en brouillon sont modifiables');
        }

        if ($invoice->type.'s' !== $type) {
            abort(403, $error_message);
        }

        if ($current_user->tenant_id !== $invoice->tenant_id) {
            abort(403, $error_message);
        }
        $invoice->delete();

        return back()->with('success', 'Facture supprimée avec succès');
    }

    public function validateInvoice(string $type, string $id)
    {

        $this->validateType($type);

        $invoice = Invoice::with('items')->findOrFail($id);

        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Cette facture est déjà validée');
        }

        $this->checkAuthorization($invoice, $type);

        try {
            $this->service->validateInvoice($invoice);

            return back()->with('success', 'Facture validée avec succès');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

    }

    public function validatePay(PaymentRequest $request, string $type, Invoice $invoice)
    {

        $this->validateType($type);
        $this->checkAuthorization($invoice, $type);
        $amount_paid = (int) $request->input('amount_paid');
        $payment_date = $request->input('payment_date');
        if ($amount_paid > $invoice->balance || $amount_paid <= 0) {
            return back()->with('error', "Impossible de payer $amount_paid pour cette facture.");
        }

        try {
            // code...
            DB::beginTransaction();
            $wallet = Wallet::where('id', $request->wallet_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->current_balance < $amount_paid && $type !== 'clients') {
                return back()->with('error', 'Solde insuffisant dans le wallet '.$wallet->name);
            }
            $beforeBalance = $wallet->current_balance;

            if ($type == 'clients') {
                $wallet->increment('current_balance', $amount_paid);
            } else {
                $wallet->decrement('current_balance', $amount_paid);
            }

            walletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => $invoice->type === 'client' ? 'in' : 'out',
                'amount' => $amount_paid,
                'balance_before' => $beforeBalance,
                'balance_after' => $wallet->current_balance,
                'source_type' => $wallet->name,
                'source_id' => $invoice->id,
                'note' => 'Paiement '.($invoice->type === 'client' ? 'client' : 'fournisseur').' sur facture '.$invoice->invoice_number,
            ]);

            $invoice->balance -= $amount_paid;

            if ($invoice->balance > 0) {
                $invoice->status = 'partial';

            } elseif ($invoice->balance == 0) {
                $invoice->status = 'paid';
            }
            $invoice->save();

            Payment::create([
                'payment_number' => app(DocumentNumberService::class)->generate('payment', $invoice->tenant),
                'wallet_id' => $wallet->id,
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'contact_id' => $invoice->contact_id,
                'amount_paid' => $amount_paid,
                'remaining_amount' => $invoice->balance,
                'payment_date' => $payment_date,
                'payment_type' => $wallet->name,
                'payment_source' => $invoice->type,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // throw $th;
            throw $e;
        }

        return back()
            ->with('success', "Vous venez de faire un paiement de $amount_paid FCFA sur la facture $invoice->invoice_number");

    }

    public function applyCreditNote(Request $request, string $type, Invoice $invoice)
    {
        $this->validateType($type);
        $this->checkAuthorization($invoice, $type);

        $invoiceType = rtrim($type, 's');
        $creditNoteClass = $invoiceType === Invoice::TYPE_CLIENT ? CustomerCreditNote::class : SupplierCreditNote::class;
        $creditNoteTable = (new $creditNoteClass())->getTable();
        $tenantId = $request->user()->tenant_id;

        $data = $request->validate([
            'credit_note_id' => [
                'required',
                'uuid',
                Rule::exists($creditNoteTable, 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('contact_id', $invoice->contact_id)
                    ->where('remaining_amount', '>', 0)
                    ->whereIn('status', ['validated', 'applied', 'partially_applied'])
                ),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($request, $invoice, $creditNoteClass, $data) {
                $creditNote = $creditNoteClass::query()
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->whereKey($data['credit_note_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $invoice = Invoice::query()
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->whereKey($invoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $amount = (int) $data['amount'];

                if ($amount <= 0 || $amount > $invoice->balance) {
                    throw ValidationException::withMessages([
                        'amount' => 'Montant d’avoir invalide pour cette facture.',
                    ]);
                }

                if ($amount > $creditNote->remaining_amount) {
                    throw ValidationException::withMessages([
                        'credit_note_id' => 'L’avoir sélectionné n’a pas assez de crédit disponible.',
                    ]);
                }

                $creditNote->applied_amount = min((int) $creditNote->total_ttc, (int) $creditNote->applied_amount + $amount);
                $creditNote->remaining_amount = max((int) $creditNote->remaining_amount - $amount, 0);
                $creditNote->status = $creditNote->remaining_amount > 0 ? 'partially_applied' : 'applied';
                $creditNote->save();

                $invoice->balance = max((int) $invoice->balance - $amount, 0);
                if ($invoice->balance === 0) {
                    $invoice->status = 'credited';
                } elseif ($amount > 0) {
                    $invoice->status = 'partially_credited';
                }

                $invoice->save();
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        return back()->with('success', 'Avoir appliqué à la facture avec succès.');
    }

    public function returnProduct(string $type, ReturnRequestProduct $request)
    {
        $validated = $request->validated();
        $invoiceItem = InvoiceItem::with('invoice')->findOrFail($validated['invoice_item_id']);
        $invoice = $invoiceItem->invoice;

        $quantity = (int) $request->input('quantity');
        $unitPrice = (int) $invoiceItem->unit_price;
        $amountToReturn = $quantity * $unitPrice;

        // Tous les batches du produit (FIFO)
        $batches = Batch::where('product_id', $invoiceItem->product_id)
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        if ($batches->isEmpty()) {
            return back()->with('error', 'Aucun lot trouvé pour ce produit.');
        }

        try {
            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | RETOUR CLIENT → ON AJOUTE AU STOCK
            |--------------------------------------------------------------------------
            */
            if ($type === 'client') {

                // On ajoute dans le batch le plus récent
                $batch = $batches->last();
                $batch->remaining += $quantity;
                $batch->save();

                $movement = InventoryMovement::create([
                    'invoice_item_id' => $invoiceItem->id,
                    'invoice_id' => $invoice->id,
                    'batch_id' => $batch->id,
                    'product_id' => $invoiceItem->product_id,
                    'quantity' => $quantity,
                    'reason' => 'Retour client',
                ]);

                ReturnProduct::create([
                    'invoice_item_id' => $invoiceItem->id,
                    'inventory_movement_id' => $movement->id,
                    'quantity' => $quantity,
                    'motif' => $request->input('motif'),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | RETOUR FOURNISSEUR → ON RETIRE DU STOCK (FIFO)
            |--------------------------------------------------------------------------
            */
            if ($type === 'supplier') {

                $totalStock = $batches->sum('remaining');

                if ($quantity > $totalStock) {
                    DB::rollBack();

                    return back()->with(
                        'error',
                        "Quantité supérieure au stock disponible ($totalStock)"
                    );
                }

                $remainingToProcess = $quantity;

                foreach ($batches as $batch) {
                    if ($remainingToProcess <= 0) {
                        break;
                    }
                    if ($batch->remaining <= 0) {
                        continue;
                    }

                    $deduct = min($batch->remaining, $remainingToProcess);

                    $batch->remaining -= $deduct;
                    $batch->quantity -= $deduct;
                    $batch->save();

                    $movement = InventoryMovement::create([
                        'invoice_item_id' => $invoiceItem->id,
                        'invoice_id' => $invoice->id,
                        'batch_id' => $batch->id,
                        'product_id' => $invoiceItem->product_id,
                        'quantity' => $deduct,
                        'reason' => 'Retour fournisseur',
                    ]);

                    ReturnProduct::create([
                        'invoice_item_id' => $invoiceItem->id,
                        'inventory_movement_id' => $movement->id,
                        'quantity' => $deduct,
                        'motif' => $request->input('motif'),
                    ]);

                    $remainingToProcess -= $deduct;
                }
            }

            // Mise à jour facture
            $invoice->balance -= $amountToReturn;
            $invoice->total_invoice -= $amountToReturn;

            if ($invoice->balance <= 0) {
                $invoice->status = 'paid';
            }

            $invoice->save();

            DB::commit();

            return back()->with('success', 'Retour enregistré avec succès');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Erreur lors du retour : '.$e->getMessage());
        }
    }

    // Printf
    public function print(string $type, Invoice $invoice, Request $request)
    {
        $orientation = $request->input('orientation', 'landscape');
        if (! in_array($orientation, ['portrait', 'landscape'])) {
            $orientation = 'landscape';
        }

        $this->validateType($type);
        $this->checkAuthorization($invoice, $type);
        if ($orientation == 'portrait') {
            return view('back.invoices.portrait', compact('invoice'));
        }

        return view('back.invoices.invoice', compact('invoice'));
    }

    public function unpaid(Request $request)
    {
        // Récupération des dates filtrées
        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;
        $invoices = Invoice::with('contact')
            ->where('balance', '>', 0)
            ->where('type', 'client')
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('invoice_date', [$start, $end]);
            })
            ->get();

        $totalMontant = $invoices->sum('total_invoice');
        $totalReste = $invoices->sum('balance');
        $totalPaye = $totalMontant - $totalReste;

        // $invoices->each(function ($invoice) {
        //     $invoice->balance = $invoice->total_amount - $invoice->total_paid;
        //     $invoice->days_overdue = Carbon::now()->diffInDays(Carbon::parse($invoice->due_date), false);
        // });

        return view('back.invoices.unpaid', compact('invoices', 'totalMontant', 'totalPaye', 'totalReste'));
    }

    protected function availableCreditNotes(Invoice $invoice)
    {
        if ($invoice->type === Invoice::TYPE_CLIENT) {
            return CustomerCreditNote::query()
                ->where('tenant_id', $invoice->tenant_id)
                ->where('contact_id', $invoice->contact_id)
                ->where('remaining_amount', '>', 0)
                ->whereIn('status', ['validated', 'applied', 'partially_applied'])
                ->latest()
                ->get();
        }

        return SupplierCreditNote::query()
            ->where('tenant_id', $invoice->tenant_id)
            ->where('contact_id', $invoice->contact_id)
            ->where('remaining_amount', '>', 0)
            ->whereIn('status', ['validated', 'applied', 'partially_applied'])
            ->latest()
            ->get();
    }

    protected function validateType(string $type): void
    {
        if (! in_array($type, ['clients', 'suppliers'])) {
            abort(404, 'Page inexistante');
        }
    }

    protected function checkAuthorization(Invoice $invoice, string $type)
    {
        if ($invoice->type !== rtrim($type, 's')) {
            abort(403, "Vous n'êtes pas autorisé à effectuer cette opération.");
        }

    }

    public function forceDestroy(string $type, Invoice $invoice)
    {
        $current_user = auth()->user();
        $error_message = "Vous n'avez pas le droit de supprimer cette facture";

        // Vérification type
        if ($invoice->type.'s' !== $type) {
            abort(403, $error_message);
        }

        // Vérification tenant
        if ($current_user->tenant_id !== $invoice->tenant_id) {
            abort(403, $error_message);
        }

        DB::transaction(function () use ($invoice) {
            InventoryMovement::where('invoice_id', $invoice->id)->delete();
            // Supprime tous les batches liés à la facture
            Batch::where('invoice_id', $invoice->id)->delete();

            // Supprime les items liés à la facture
            InvoiceItem::where('invoice_id', $invoice->id)->delete();
            Payment::where('invoice_id', $invoice->id)->delete();
            CustomerCreditNote::where('customer_invoice_id', $invoice->id)->delete();
            SupplierCreditNote::where('supplier_invoice_id', $invoice->id)->delete();
            CustomerReturn::where('invoice_id', $invoice->id)->delete();
            SupplierReturn::where('supplier_invoice_id', $invoice->id)->delete();

            // Supprime la facture elle-même
            $invoice->forceDelete();
        });

        return back()->with('success', 'Facture et ses données liées supprimées avec succès');
    }

    public function cancel(string $type, Invoice $invoice)
    {
        $current_user = auth()->user();
        $error_message = "Vous n'avez pas le droit de supprimer cette facture";

        if ($invoice->type.'s' !== $type) {
            abort(403, $error_message);
        }

        if ($current_user->tenant_id !== $invoice->tenant_id) {
            abort(403, $error_message);
        }

        try {
            DB::transaction(function () use ($invoice) {
                foreach ($invoice->items as $item) {
                    $movements = InventoryMovement::query()
                        ->where('invoice_id', $invoice->id)
                        ->where('invoice_item_id', $item->id)
                        ->whereNotNull('batch_id')
                        ->lockForUpdate()
                        ->get();

                    if ($movements->isNotEmpty()) {
                        foreach ($movements as $movement) {
                            $batch = Batch::query()
                                ->whereKey($movement->batch_id)
                                ->lockForUpdate()
                                ->first();

                            if (! $batch) {
                                abort(422, "Batch introuvable pour le produit {$item->product->name}");
                            }

                            if ($batch->remaining < $movement->quantity) {
                                abort(
                                    422,
                                    "Impossible d’annuler la facture : le stock du produit {$item->product->name} a déjà été consommé."
                                );
                            }

                            $batch->quantity += $movement->quantity;
                            $batch->remaining += $movement->quantity;
                            $batch->save();
                        }

                        continue;
                    }

                    $batch = Batch::query()
                        ->where('tenant_id', $invoice->tenant_id)
                        ->where('warehouse_id', $item->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->where('unit_price', $item->unit_price)
                        ->lockForUpdate()
                        ->first();

                    if (! $batch) {
                        abort(422, "Batch introuvable pour le produit {$item->product->name}");
                    }

                    if ($batch->remaining < $item->quantity) {
                        abort(
                            422,
                            "Impossible d’annuler la facture : le stock du produit {$item->product->name} a déjà été consommé."
                        );
                    }

                    $batch->quantity += $item->quantity;
                    $batch->remaining += $item->quantity;
                    $batch->save();
                }

                // Nettoyage logique
                InventoryMovement::where('invoice_id', $invoice->id)->delete();
                Payment::where('invoice_id', $invoice->id)->delete();

                // Annulation logique
                $invoice->status = 'cancelled';
                $invoice->save();
            });

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Facture annulée et stock corrigé avec succès');
    }
}
