<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierReturnRequest;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\SupplierReturn;
use App\Models\Warehouse;
use App\Services\SupplierReturnService;
use Illuminate\Http\Request;

class SupplierReturnController extends Controller
{
    public function __construct(
        private readonly SupplierReturnService $supplierReturnService
    ) {
    }

    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $returns = SupplierReturn::query()
            ->where('tenant_id', $tenantId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('return_number', 'like', "%{$search}%")
                        ->orWhereHas('contact', fn ($cq) => $cq->where('fullname', 'like', "%{$search}%"));
                });
            })
            ->with(['contact', 'supplierInvoice', 'goodsReceipt', 'warehouse', 'creditNote'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('back.returns.index', [
            'title' => 'Bons de retour fournisseur',
            'subtitle' => 'Retours fournisseurs, validations et sorties de stock.',
            'records' => $returns,
            'module' => 'supplier',
            'createRoute' => 'supplier-returns.create',
            'showRoute' => 'supplier-returns.show',
            'emptyMessage' => 'Aucun bon de retour fournisseur pour le moment.',
        ]);
    }

    public function create(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $sourceType = $request->input('source_type');
        $sourceId = $request->input('source_id');
        $sourceContext = $this->loadSourceContext($tenantId, $sourceType, $sourceId);

        return view('back.returns.form', [
            'title' => 'Créer un bon de retour fournisseur',
            'module' => 'supplier',
            'record' => new SupplierReturn(['return_date' => now()->toDateString()]),
            'storeRoute' => 'supplier-returns.store',
            'updateRoute' => null,
            'sourceType' => $sourceType,
            'sourceId' => $sourceId,
            'sourceContext' => $sourceContext,
            'supplierInvoices' => Invoice::query()->where('tenant_id', $tenantId)->where('type', Invoice::TYPE_SUPPLIER)->with('items.product')->latest()->get(),
            'goodsReceipts' => GoodsReceipt::query()->where('tenant_id', $tenantId)->with('items.product')->latest()->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $tenantId)->orderBy('name')->get(),
        ]);
    }

    public function store(SupplierReturnRequest $request)
    {
        $return = $this->supplierReturnService->create($request->validated(), $request->user());

        return redirect()->route('supplier-returns.show', $return)->with('success', 'Bon de retour fournisseur créé.');
    }

    public function show(Request $request, SupplierReturn $supplierReturn)
    {
        abort_unless($supplierReturn->tenant_id === $request->user()->tenant_id, 404);

        $supplierReturn->load([
            'items.product',
            'items.invoiceItem.invoice',
            'items.goodsReceiptItem.goodsReceipt',
            'supplierInvoice.items.product',
            'goodsReceipt.items.product',
            'contact',
            'warehouse',
            'movements.batch',
            'creditNote.items.product',
            'creditNote.invoice',
        ]);

        return view('back.returns.show', [
            'title' => 'Bon de retour fournisseur',
            'record' => $supplierReturn,
            'module' => 'supplier',
            'items' => $supplierReturn->items,
        ]);
    }

    public function edit(Request $request, SupplierReturn $supplierReturn)
    {
        abort_unless($supplierReturn->tenant_id === $request->user()->tenant_id, 404);
        abort_unless($supplierReturn->status === 'draft', 403);

        $supplierReturn->load(['items', 'supplierInvoice.items.product', 'goodsReceipt.items.product', 'warehouse']);
        $sourceType = $supplierReturn->supplier_invoice_id ? 'invoice' : 'goods_receipt';
        $sourceId = $supplierReturn->supplier_invoice_id ?? $supplierReturn->goods_receipt_id;

        return view('back.returns.form', [
            'title' => 'Modifier le bon de retour fournisseur',
            'module' => 'supplier',
            'record' => $supplierReturn,
            'storeRoute' => null,
            'updateRoute' => 'supplier-returns.update',
            'sourceType' => $sourceType,
            'sourceId' => $sourceId,
            'sourceContext' => $this->loadSourceContext($request->user()->tenant_id, $sourceType, $sourceId),
            'supplierInvoices' => Invoice::query()->where('tenant_id', $request->user()->tenant_id)->where('type', Invoice::TYPE_SUPPLIER)->with('items.product')->latest()->get(),
            'goodsReceipts' => GoodsReceipt::query()->where('tenant_id', $request->user()->tenant_id)->with('items.product')->latest()->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
        ]);
    }

    public function update(SupplierReturnRequest $request, SupplierReturn $supplierReturn)
    {
        $return = $this->supplierReturnService->update($supplierReturn, $request->validated(), $request->user());

        return redirect()->route('supplier-returns.show', $return)->with('success', 'Bon de retour fournisseur mis à jour.');
    }

    public function validateReturn(Request $request, SupplierReturn $supplierReturn)
    {
        $return = $this->supplierReturnService->validateReturn($supplierReturn, $request->user());

        $message = 'Bon de retour fournisseur validé.';
        if ($return->creditNote) {
            if ($return->creditNote->applied_amount > 0) {
                $message = "Bon de retour fournisseur validé. Un avoir fournisseur de ".number_format($return->creditNote->applied_amount, 0, ',', ' ').' FCFA a été appliqué.';
                if ($return->creditNote->remaining_amount > 0) {
                    $message .= ' Crédit restant disponible : '.number_format($return->creditNote->remaining_amount, 0, ',', ' ').' FCFA.';
                }
            } else {
                $message = "Bon de retour fournisseur validé. Un avoir fournisseur de ".number_format($return->creditNote->total_ttc, 0, ',', ' ').' FCFA est disponible.';
            }
        }

        return redirect()->route('supplier-returns.show', $return)->with('success', $message);
    }

    public function cancel(Request $request, SupplierReturn $supplierReturn)
    {
        $return = $this->supplierReturnService->cancel($supplierReturn, $request->user());

        return redirect()->route('supplier-returns.show', $return)->with('success', 'Bon de retour fournisseur annulé.');
    }

    public function print(Request $request, SupplierReturn $supplierReturn)
    {
        abort_unless($supplierReturn->tenant_id === $request->user()->tenant_id, 404);
        $supplierReturn->load([
            'items.product',
            'items.invoiceItem.invoice',
            'items.goodsReceiptItem.goodsReceipt',
            'supplierInvoice',
            'goodsReceipt',
            'contact',
            'warehouse',
            'movements.batch',
        ]);

        return view('back.returns.print', [
            'title' => 'Bon de retour fournisseur',
            'record' => $supplierReturn,
            'module' => 'supplier',
            'items' => $supplierReturn->items,
        ]);
    }

    private function loadSourceContext(string $tenantId, ?string $sourceType, ?string $sourceId): ?array
    {
        if (! $sourceType || ! $sourceId) {
            return null;
        }

        if ($sourceType === 'invoice') {
            $invoice = Invoice::query()->where('tenant_id', $tenantId)->where('type', Invoice::TYPE_SUPPLIER)->with('items.product')->find($sourceId);

            if (! $invoice) {
                return null;
            }

            return ['source' => $invoice, 'type' => 'invoice'];
        }

        if ($sourceType === 'goods_receipt') {
            $goodsReceipt = GoodsReceipt::query()->where('tenant_id', $tenantId)->with('items.product')->find($sourceId);

            if (! $goodsReceipt) {
                return null;
            }

            return ['source' => $goodsReceipt, 'type' => 'goods_receipt'];
        }

        return null;
    }
}
