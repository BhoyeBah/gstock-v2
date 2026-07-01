<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderRequest;
use App\Models\Contact;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\PurchaseOrderConversionService;
use App\Services\PurchaseOrderService;
use App\Services\ReceiptService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $purchaseOrderService)
    {
    }

    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with('contact')
            ->latest()
            ->paginate(10);

        return view('back.documents.index', [
            'title' => 'Commandes fournisseurs',
            'subtitle' => 'Créer et suivre les commandes fournisseurs.',
            'records' => $purchaseOrders,
            'showRoute' => 'purchase-orders.show',
            'createRoute' => 'purchase-orders.create',
            'emptyMessage' => 'Aucune commande fournisseur créée pour le moment.',
        ]);
    }

    public function create(Request $request)
    {
        return view('back.documents.form', [
            'title' => 'Créer une commande fournisseur',
            'record' => new PurchaseOrder(['purchase_date' => now()->toDateString()]),
            'storeRoute' => 'purchase-orders.store',
            'updateRoute' => null,
            'contacts' => Contact::query()->where('tenant_id', $request->user()->tenant_id)->where('type', 'supplier')->orderBy('fullname')->get(),
            'products' => Product::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'mode' => 'purchase_order',
        ]);
    }

    public function store(PurchaseOrderRequest $request)
    {
        $purchaseOrder = $this->purchaseOrderService->create($request->validated(), $request->user());
        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Commande fournisseur créée.');
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->tenant_id === $request->user()->tenant_id, 404);
        $purchaseOrder->load(['items.product', 'contact']);

        return view('back.documents.show', [
            'title' => 'Commande fournisseur',
            'record' => $purchaseOrder,
            'items' => $purchaseOrder->items,
            'type' => 'purchase_order',
        ]);
    }

    public function edit(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->tenant_id === $request->user()->tenant_id, 404);

        return view('back.documents.form', [
            'title' => 'Modifier la commande fournisseur',
            'record' => $purchaseOrder->load('items'),
            'storeRoute' => null,
            'updateRoute' => 'purchase-orders.update',
            'contacts' => Contact::query()->where('tenant_id', $request->user()->tenant_id)->where('type', 'supplier')->orderBy('fullname')->get(),
            'products' => Product::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'mode' => 'purchase_order',
        ]);
    }

    public function update(PurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->tenant_id === $request->user()->tenant_id, 404);
        $purchaseOrder = $this->purchaseOrderService->update($purchaseOrder, $request->validated(), $request->user());
        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Commande fournisseur mise à jour.');
    }

    public function confirm(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->tenant_id === $request->user()->tenant_id, 404);
        $this->purchaseOrderService->confirm($purchaseOrder);
        return back()->with('success', 'Commande fournisseur confirmée.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->tenant_id === $request->user()->tenant_id, 404);
        $this->purchaseOrderService->cancel($purchaseOrder);
        return back()->with('success', 'Commande fournisseur annulée.');
    }

    public function createReceipt(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->tenant_id === $request->user()->tenant_id, 404);
        $warehouseId = $purchaseOrder->items->first()?->warehouse_id
            ?? Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->value('id');
        $receipt = app(ReceiptService::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'warehouse_id' => $warehouseId,
            'receipt_date' => now()->toDateString(),
            'notes' => $purchaseOrder->notes,
        ], $request->user());

        return redirect()->route('goods-receipts.show', $receipt)->with('success', 'Bon de réception créé.');
    }

    public function createSupplierInvoice(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($purchaseOrder->tenant_id === $request->user()->tenant_id, 404);
        $invoice = app(PurchaseOrderConversionService::class)->toSupplierInvoice($purchaseOrder->load('items'));
        return redirect()->route('invoices.show', ['type' => 'suppliers', 'invoice' => $invoice])->with('success', 'Facture fournisseur créée.');
    }
}
