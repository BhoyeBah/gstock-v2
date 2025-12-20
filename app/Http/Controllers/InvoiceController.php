<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Http\Requests\ReturnRequestProduct;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Batch;
use App\Models\Contact;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnProduct;
use App\Models\Warehouse;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $status_list = ['draft', 'validated', 'partial', 'paid', 'cancelled'];

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

        return view('back.invoices.index', compact('invoices', 'invoiceType', 'type', 'products', 'contacts', 'warehouses', 'allInvoices'));

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
        $invoice = Invoice::with('items')->findOrFail($id);

        $invoice = Invoice::with(['items.returns', 'items.product', 'items.warehouse', 'contact'])
            ->findOrFail($id);

        $this->checkAuthorization($invoice, $type);

        $batches = Batch::where('invoice_id', $invoice->id)->orderBy('remaining')->paginate(10);
        $payments = Payment::where('invoice_id', $invoice->id)->paginate(10);

        // dd($batches);
        return view('back.invoices.show', compact('invoice', 'batches', 'payments', 'type'));
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
        // dd($products, $contacts, $warehouses);

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

        if ($amount_paid > $invoice->balance || $amount_paid <= 0) {
            return back()->with('error', "Impossible de payer $amount_paid pour cette facture.");
        }

        try {
            // code...
            DB::beginTransaction();
            $invoice->balance -= $amount_paid;
            if ($invoice->balance > 0) {
                $invoice->status = 'partial';

            } elseif ($invoice->balance == 0) {
                $invoice->status = 'paid';
            }
            $invoice->save();

            Payment::create([
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'contact_id' => $invoice->contact_id,
                'amount_paid' => $amount_paid,
                'remaining_amount' => $invoice->balance,
                'payment_date' => now(),
                'payment_type' => $request->input('payment_type'),
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

    public function returnProduct(string $type, ReturnRequestProduct $request)
    {

        $validated = $request->validated();
        $invoiceItem = InvoiceItem::with('invoice')->find($validated['invoice_item_id']);
        $invoice = $invoiceItem->invoice;

        $batch = InventoryMovement::where('invoice_item_id', $invoiceItem->id)->first()->batch;

        $quantityToReturn = (int) $request->input('quantity');

        // $quantityToReturn = $type === 'clients' ? $quantityToReturn : -1 * $quantityToReturn;

        $purchasePrice = (int) $invoiceItem->unit_price;
        $balanceToReturn = $quantityToReturn * $purchasePrice;

        try {
            DB::beginTransaction();

            // Mise à jour de Batch
            if ($type == 'supplier') {
                $batch->quantity -= $quantityToReturn;
                $batch->remaining -= $quantityToReturn;
            } else {
                $batch->remaining += $quantityToReturn;
            }

            $invoice->balance -= $balanceToReturn;
            $invoice->total_invoice -= $balanceToReturn;
            // dd($batch->quantity, $batch->remaining);
            if ($batch->quantity >= $batch->remaining && $batch->remaining >= 0 && $invoice->balance >= 0) {

                $inventoryMovement = InventoryMovement::create([
                    'invoice_item_id' => $invoiceItem->id,
                    'invoice_id' => $invoice->id,
                    'batch_id' => $batch->id,
                    'product_id' => $invoiceItem->product_id,
                    'quantity' => $quantityToReturn,
                    'reason' => 'Retour produit',
                ]);

                ReturnProduct::create([
                    'invoice_item_id' => $invoiceItem->id,
                    'inventory_movement_id' => $inventoryMovement->id,
                    'quantity' => $quantityToReturn,
                    'motif' => $request->input('motif'),
                ]);

                if ($invoice->balance == 0) {
                    $invoice->status = 'paid';
                }

                $invoice->save();
                $batch->save();

                DB::commit();

                return back()->with('success', 'Rétour enrégistrée avec success');
            }

            return back()->with('error', 'Impossible de faire un rétour sur ce produit');

        } catch (\Exception $e) {
            throw $e;

            return back()->with('error', 'Impossible de faire un rétour sur ce produit');

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

        // dd($invoices);

        return view('back.invoices.unpaid', compact('invoices', 'totalMontant', 'totalPaye', 'totalReste'));
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

                // 1️⃣ VALIDATION MÉTIER (AUCUNE ÉCRITURE)
                foreach ($invoice->items as $item) {

                    $batch = Batch::where('product_id', $item->product_id)
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
                }

                // 2️⃣ EXÉCUTION (APRÈS VALIDATION COMPLÈTE)
                foreach ($invoice->items as $item) {

                    $batch = Batch::where('product_id', $item->product_id)
                        ->where('unit_price', $item->unit_price)
                        ->lockForUpdate()
                        ->first();

                    $batch->quantity -= $item->quantity;
                    $batch->remaining -= $item->quantity;
                    $batch->save();
                }

                // 3️⃣ Nettoyage logique
                InventoryMovement::where('invoice_id', $invoice->id)->delete();
                Payment::where('invoice_id', $invoice->id)->delete();

                // 4️⃣ Annulation logique
                $invoice->status = 'cancelled';
                $invoice->save();
            });

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Facture annulée et stock corrigé avec succès');
    }
}
