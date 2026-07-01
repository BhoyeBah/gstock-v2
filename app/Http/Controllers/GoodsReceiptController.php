<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoodsReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Services\ReceiptService;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    public function __construct(private readonly ReceiptService $receiptService)
    {
    }

    public function index(Request $request)
    {
        $goodsReceipts = GoodsReceipt::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with('contact', 'purchaseOrder')
            ->latest()
            ->paginate(10);

        return view('back.documents.index', [
            'title' => 'Bons de réception',
            'subtitle' => 'Réceptions et entrées de stock.',
            'records' => $goodsReceipts,
            'showRoute' => 'goods-receipts.show',
            'createRoute' => 'goods-receipts.create',
            'emptyMessage' => 'Aucun bon de réception créé pour le moment.',
        ]);
    }

    public function create(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        return view('back.documents.form', [
            'title' => 'Créer un bon de réception',
            'record' => new GoodsReceipt(['receipt_date' => now()->toDateString()]),
            'storeRoute' => 'goods-receipts.store',
            'updateRoute' => null,
            'purchaseOrders' => PurchaseOrder::query()->where('tenant_id', $tenantId)->orderBy('purchase_number')->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $tenantId)->orderBy('name')->get(),
            'mode' => 'receipt',
        ]);
    }

    public function store(GoodsReceiptRequest $request)
    {
        $receipt = $this->receiptService->create($request->validated(), $request->user());
        return redirect()->route('goods-receipts.show', $receipt)->with('success', 'Bon de réception créé.');
    }

    public function show(Request $request, GoodsReceipt $goodsReceipt)
    {
        abort_unless($goodsReceipt->tenant_id === $request->user()->tenant_id, 404);
        $goodsReceipt->load(['items.product', 'purchaseOrder.items.product', 'contact']);

        return view('back.documents.show', [
            'title' => 'Bon de réception',
            'record' => $goodsReceipt,
            'items' => $goodsReceipt->items,
            'type' => 'goods_receipt',
        ]);
    }

    public function validateReceipt(Request $request, GoodsReceipt $goodsReceipt)
    {
        abort_unless($goodsReceipt->tenant_id === $request->user()->tenant_id, 404);
        $this->receiptService->validate($goodsReceipt, $request->user());
        return back()->with('success', 'Réception validée.');
    }

    public function cancel(Request $request, GoodsReceipt $goodsReceipt)
    {
        abort_unless($goodsReceipt->tenant_id === $request->user()->tenant_id, 404);
        $this->receiptService->cancel($goodsReceipt, $request->user());
        return back()->with('success', 'Réception annulée.');
    }

    public function print(Request $request, GoodsReceipt $goodsReceipt)
    {
        abort_unless($goodsReceipt->tenant_id === $request->user()->tenant_id, 404);
        $goodsReceipt->load(['items.product', 'purchaseOrder.contact']);
        return view('back.documents.print', [
            'title' => 'Bon de réception',
            'record' => $goodsReceipt,
            'items' => $goodsReceipt->items,
        ]);
    }
}
