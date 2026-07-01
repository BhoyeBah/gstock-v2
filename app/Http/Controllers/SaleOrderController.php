<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleOrderRequest;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Quote;
use App\Models\SaleOrder;
use App\Models\Warehouse;
use App\Services\SaleOrderConversionService;
use App\Services\SaleOrderService;
use Illuminate\Http\Request;

class SaleOrderController extends Controller
{
    public function __construct(
        private readonly SaleOrderService $saleOrderService,
        private readonly SaleOrderConversionService $saleOrderConversionService
    ) {
    }

    public function index(Request $request)
    {
        $saleOrders = SaleOrder::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with('contact', 'quote')
            ->latest()
            ->paginate(10);

        return view('back.documents.index', [
            'title' => 'Commandes clients',
            'subtitle' => 'Créer et suivre les commandes clients.',
            'records' => $saleOrders,
            'showRoute' => 'sale-orders.show',
            'createRoute' => 'sale-orders.create',
            'emptyMessage' => 'Aucune commande client créée pour le moment.',
        ]);
    }

    public function create(Request $request)
    {
        return view('back.documents.form', [
            'title' => 'Créer une commande client',
            'record' => new SaleOrder(['order_date' => now()->toDateString()]),
            'storeRoute' => 'sale-orders.store',
            'updateRoute' => null,
            'contacts' => Contact::query()->where('tenant_id', $request->user()->tenant_id)->where('type', 'client')->orderBy('fullname')->get(),
            'products' => Product::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'quotes' => Quote::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('quote_number')->get(),
            'mode' => 'sale_order',
        ]);
    }

    public function store(SaleOrderRequest $request)
    {
        $saleOrder = $this->saleOrderService->create($request->validated(), $request->user());
        return redirect()->route('sale-orders.show', $saleOrder)->with('success', 'Commande client créée.');
    }

    public function show(Request $request, SaleOrder $saleOrder)
    {
        $saleOrder = $this->resolveSaleOrder($request, $saleOrder);
        $saleOrder->load(['items.product', 'contact', 'quote', 'invoice']);

        return view('back.documents.show', [
            'title' => 'Commande client',
            'record' => $saleOrder,
            'items' => $saleOrder->items,
            'type' => 'sale_order',
            'documentKind' => 'sale_order',
        ]);
    }

    public function edit(Request $request, SaleOrder $saleOrder)
    {
        $saleOrder = $this->resolveSaleOrder($request, $saleOrder);

        return view('back.documents.form', [
            'title' => 'Modifier la commande client',
            'record' => $saleOrder->load('items'),
            'storeRoute' => null,
            'updateRoute' => 'sale-orders.update',
            'contacts' => Contact::query()->where('tenant_id', $request->user()->tenant_id)->where('type', 'client')->orderBy('fullname')->get(),
            'products' => Product::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'mode' => 'sale_order',
        ]);
    }

    public function update(SaleOrderRequest $request, SaleOrder $saleOrder)
    {
        $saleOrder = $this->resolveSaleOrder($request, $saleOrder);
        $saleOrder = $this->saleOrderService->update($saleOrder, $request->validated(), $request->user());
        return redirect()->route('sale-orders.show', $saleOrder)->with('success', 'Commande client mise à jour.');
    }

    public function confirm(Request $request, SaleOrder $saleOrder)
    {
        $saleOrder = $this->resolveSaleOrder($request, $saleOrder);
        $this->saleOrderService->confirm($saleOrder);
        return back()->with('success', 'Commande confirmée.');
    }

    public function cancel(Request $request, SaleOrder $saleOrder)
    {
        $saleOrder = $this->resolveSaleOrder($request, $saleOrder);
        $this->saleOrderService->cancel($saleOrder);
        return back()->with('success', 'Commande annulée.');
    }

    public function createDelivery(Request $request, SaleOrder $saleOrder)
    {
        $saleOrder = $this->resolveSaleOrder($request, $saleOrder);
        $delivery = $this->saleOrderConversionService->toDelivery($saleOrder);
        return redirect()->route('delivery-notes.show', $delivery)->with('success', 'Bon de livraison créé.');
    }

    public function createInvoice(Request $request, SaleOrder $saleOrder)
    {
        $saleOrder = $this->resolveSaleOrder($request, $saleOrder);
        $invoice = $this->saleOrderConversionService->toInvoice($saleOrder);
        return redirect()->route('invoices.show', ['type' => 'clients', 'invoice' => $invoice])->with('success', 'Facture créée depuis la commande.');
    }

    public function print(Request $request, SaleOrder $saleOrder)
    {
        $saleOrder = $this->resolveSaleOrder($request, $saleOrder);
        $saleOrder->load(['items.product', 'contact', 'quote', 'invoice']);

        return view('back.documents.print', [
            'title' => 'Commande client',
            'record' => $saleOrder,
            'items' => $saleOrder->items,
        ]);
    }

    private function resolveSaleOrder(Request $request, SaleOrder $saleOrder): SaleOrder
    {
        return SaleOrder::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereKey($saleOrder->id)
            ->firstOrFail();
    }
}
