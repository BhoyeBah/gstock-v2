<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerReturnRequest;
use App\Models\CustomerReturn;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\Warehouse;
use App\Services\CustomerReturnService;
use Illuminate\Http\Request;

class CustomerReturnController extends Controller
{
    public function __construct(
        private readonly CustomerReturnService $customerReturnService
    ) {
    }

    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $returns = CustomerReturn::query()
            ->where('tenant_id', $tenantId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->where('return_number', 'like', "%{$search}%")
                        ->orWhereHas('contact', fn ($cq) => $cq->where('fullname', 'like', "%{$search}%"));
                });
            })
            ->with(['contact', 'invoice', 'deliveryNote', 'warehouse'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('back.returns.index', [
            'title' => 'Bons de retour client',
            'subtitle' => 'Retours clients, validations et réintégration de stock.',
            'records' => $returns,
            'module' => 'customer',
            'createRoute' => 'customer-returns.create',
            'showRoute' => 'customer-returns.show',
            'emptyMessage' => 'Aucun bon de retour client pour le moment.',
        ]);
    }

    public function create(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $sourceType = $request->input('source_type');
        $sourceId = $request->input('source_id');
        $sourceContext = $this->loadSourceContext($tenantId, $sourceType, $sourceId);

        return view('back.returns.form', [
            'title' => 'Créer un bon de retour client',
            'module' => 'customer',
            'record' => new CustomerReturn(['return_date' => now()->toDateString()]),
            'storeRoute' => 'customer-returns.store',
            'updateRoute' => null,
            'sourceType' => $sourceType,
            'sourceId' => $sourceId,
            'sourceContext' => $sourceContext,
            'invoices' => Invoice::query()->where('tenant_id', $tenantId)->where('type', Invoice::TYPE_CLIENT)->with('items.product')->latest()->get(),
            'deliveryNotes' => DeliveryNote::query()->where('tenant_id', $tenantId)->with('items.product')->latest()->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $tenantId)->orderBy('name')->get(),
        ]);
    }

    public function store(CustomerReturnRequest $request)
    {
        $return = $this->customerReturnService->create($request->validated(), $request->user());

        return redirect()->route('customer-returns.show', $return)->with('success', 'Bon de retour client créé.');
    }

    public function show(Request $request, CustomerReturn $customerReturn)
    {
        abort_unless($customerReturn->tenant_id === $request->user()->tenant_id, 404);

        $customerReturn->load([
            'items.product',
            'items.invoiceItem.invoice',
            'items.deliveryNoteItem.deliveryNote',
            'invoice.items.product',
            'deliveryNote.items.product',
            'contact',
            'warehouse',
            'movements.batch',
        ]);

        return view('back.returns.show', [
            'title' => 'Bon de retour client',
            'record' => $customerReturn,
            'module' => 'customer',
            'items' => $customerReturn->items,
        ]);
    }

    public function edit(Request $request, CustomerReturn $customerReturn)
    {
        abort_unless($customerReturn->tenant_id === $request->user()->tenant_id, 404);
        abort_unless($customerReturn->status === 'draft', 403);

        $customerReturn->load(['items', 'invoice.items.product', 'deliveryNote.items.product', 'warehouse']);
        $sourceType = $customerReturn->invoice_id ? 'invoice' : 'delivery_note';
        $sourceId = $customerReturn->invoice_id ?? $customerReturn->delivery_note_id;

        return view('back.returns.form', [
            'title' => 'Modifier le bon de retour client',
            'module' => 'customer',
            'record' => $customerReturn,
            'storeRoute' => null,
            'updateRoute' => 'customer-returns.update',
            'sourceType' => $sourceType,
            'sourceId' => $sourceId,
            'sourceContext' => $this->loadSourceContext($request->user()->tenant_id, $sourceType, $sourceId),
            'invoices' => Invoice::query()->where('tenant_id', $request->user()->tenant_id)->where('type', Invoice::TYPE_CLIENT)->with('items.product')->latest()->get(),
            'deliveryNotes' => DeliveryNote::query()->where('tenant_id', $request->user()->tenant_id)->with('items.product')->latest()->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
        ]);
    }

    public function update(CustomerReturnRequest $request, CustomerReturn $customerReturn)
    {
        $return = $this->customerReturnService->update($customerReturn, $request->validated(), $request->user());

        return redirect()->route('customer-returns.show', $return)->with('success', 'Bon de retour client mis à jour.');
    }

    public function validateReturn(Request $request, CustomerReturn $customerReturn)
    {
        $return = $this->customerReturnService->validateReturn($customerReturn, $request->user());

        return redirect()->route('customer-returns.show', $return)->with('success', 'Bon de retour client validé.');
    }

    public function cancel(Request $request, CustomerReturn $customerReturn)
    {
        $return = $this->customerReturnService->cancel($customerReturn, $request->user());

        return redirect()->route('customer-returns.show', $return)->with('success', 'Bon de retour client annulé.');
    }

    public function print(Request $request, CustomerReturn $customerReturn)
    {
        abort_unless($customerReturn->tenant_id === $request->user()->tenant_id, 404);
        $customerReturn->load([
            'items.product',
            'items.invoiceItem.invoice',
            'items.deliveryNoteItem.deliveryNote',
            'invoice',
            'deliveryNote',
            'contact',
            'warehouse',
            'movements.batch',
        ]);

        return view('back.returns.print', [
            'title' => 'Bon de retour client',
            'record' => $customerReturn,
            'module' => 'customer',
            'items' => $customerReturn->items,
        ]);
    }

    private function loadSourceContext(string $tenantId, ?string $sourceType, ?string $sourceId): ?array
    {
        if (! $sourceType || ! $sourceId) {
            return null;
        }

        if ($sourceType === 'invoice') {
            $invoice = Invoice::query()->where('tenant_id', $tenantId)->where('type', Invoice::TYPE_CLIENT)->with('items.product')->find($sourceId);

            if (! $invoice) {
                return null;
            }

            return ['source' => $invoice, 'type' => 'invoice'];
        }

        if ($sourceType === 'delivery_note') {
            $deliveryNote = DeliveryNote::query()->where('tenant_id', $tenantId)->with('items.product')->find($sourceId);

            if (! $deliveryNote) {
                return null;
            }

            return ['source' => $deliveryNote, 'type' => 'delivery_note'];
        }

        return null;
    }
}
