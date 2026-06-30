<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryNoteRequest;
use App\Models\Contact;
use App\Models\DeliveryNote;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\Warehouse;
use App\Services\DeliveryService;
use App\Services\SaleOrderConversionService;
use Illuminate\Http\Request;

class DeliveryNoteController extends Controller
{
    public function __construct(private readonly DeliveryService $deliveryService)
    {
    }

    public function index(Request $request)
    {
        $deliveryNotes = DeliveryNote::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->with('contact', 'saleOrder')
            ->latest()
            ->paginate(10);

        return view('back.documents.index', [
            'title' => 'Bons de livraison',
            'subtitle' => 'Livraisons et sorties de stock.',
            'records' => $deliveryNotes,
            'showRoute' => 'delivery-notes.show',
            'createRoute' => 'delivery-notes.create',
            'emptyMessage' => 'Aucun bon de livraison créé pour le moment.',
        ]);
    }

    public function create(Request $request)
    {
        return view('back.documents.form', [
            'title' => 'Créer un bon de livraison',
            'record' => new DeliveryNote(['delivery_date' => now()->toDateString()]),
            'storeRoute' => 'delivery-notes.store',
            'updateRoute' => null,
            'saleOrders' => SaleOrder::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('order_number')->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'mode' => 'delivery',
        ]);
    }

    public function store(DeliveryNoteRequest $request)
    {
        $deliveryNote = $this->deliveryService->create($request->validated(), $request->user());
        return redirect()->route('delivery-notes.show', $deliveryNote)->with('success', 'Bon de livraison créé.');
    }

    public function show(Request $request, DeliveryNote $deliveryNote)
    {
        $deliveryNote = $this->resolveDeliveryNote($request, $deliveryNote);
        $deliveryNote->load(['items.product', 'saleOrder.items.product', 'contact', 'warehouse']);

        return view('back.documents.show', [
            'title' => 'Bon de livraison',
            'record' => $deliveryNote,
            'items' => $deliveryNote->items,
            'type' => 'delivery_note',
            'documentKind' => 'delivery_note',
        ]);
    }

    public function validateDelivery(Request $request, DeliveryNote $deliveryNote)
    {
        $deliveryNote = $this->resolveDeliveryNote($request, $deliveryNote);
        $this->deliveryService->validate($deliveryNote, $request->user());
        return back()->with('success', 'Livraison validée.');
    }

    public function cancel(Request $request, DeliveryNote $deliveryNote)
    {
        $deliveryNote = $this->resolveDeliveryNote($request, $deliveryNote);
        $this->deliveryService->cancel($deliveryNote, $request->user());
        return back()->with('success', 'Livraison annulée.');
    }

    public function print(Request $request, DeliveryNote $deliveryNote)
    {
        $deliveryNote = $this->resolveDeliveryNote($request, $deliveryNote);
        $deliveryNote->load(['items.product', 'saleOrder.contact']);

        return view('back.documents.print', [
            'title' => 'Bon de livraison',
            'record' => $deliveryNote,
            'items' => $deliveryNote->items,
        ]);
    }

    private function resolveDeliveryNote(Request $request, DeliveryNote $deliveryNote): DeliveryNote
    {
        return DeliveryNote::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereKey($deliveryNote->id)
            ->firstOrFail();
    }
}
