<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\CustomerReturn;
use App\Models\CustomerReturnItem;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerReturnService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly CustomerCreditNoteService $customerCreditNoteService,
        private readonly InvoicePaymentStatusService $invoicePaymentStatusService
    ) {
    }

    public function create(array $data, User $user): CustomerReturn
    {
        return DB::transaction(function () use ($data, $user) {
            $tenantId = $user->tenant_id;
            $source = $this->resolveSource($data, $tenantId);

            $return = CustomerReturn::create([
                'tenant_id' => $tenantId,
                'return_number' => $this->documentNumberService->generate('customer_return', $user->tenant),
                'contact_id' => $source['contact_id'],
                'invoice_id' => $source['invoice_id'],
                'delivery_note_id' => $source['delivery_note_id'],
                'warehouse_id' => $source['warehouse_id'],
                'status' => 'draft',
                'return_date' => $data['return_date'] ?? now()->toDateString(),
                'reason' => $data['reason'] ?? null,
                'created_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($source['items'] as $sourceItem) {
                $quantityReturned = (int) ($data['items'][$sourceItem['id']]['quantity_returned'] ?? 0);
                if ($quantityReturned <= 0) {
                    continue;
                }

                $this->createItemFromSource($return, $sourceItem, $quantityReturned);
            }

            return $return->load(['items', 'contact', 'invoice', 'deliveryNote', 'warehouse']);
        });
    }

    public function validateReturn(CustomerReturn $return, User $user): CustomerReturn
    {
        return DB::transaction(function () use ($return, $user) {
            $return = CustomerReturn::where('tenant_id', $user->tenant_id)
                ->whereKey($return->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($return->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Ce bon de retour client a déjà été traité.',
                ]);
            }

            $return->load('items.product');

            foreach ($return->items as $item) {
                if ($item->quantity_returned <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Chaque ligne doit contenir une quantité retournée positive.',
                    ]);
                }

                $maxQuantity = $this->sourceQuantity($item);
                if ($item->quantity_returned > $maxQuantity) {
                    throw ValidationException::withMessages([
                        'items' => "La quantité retournée pour {$item->product->name} dépasse la quantité vendue.",
                    ]);
                }
            }

            foreach ($return->items as $item) {
                $batch = null;

                if ($item->batch_id) {
                    $batch = Batch::query()
                        ->where('tenant_id', $return->tenant_id)
                        ->whereKey($item->batch_id)
                        ->lockForUpdate()
                        ->first();
                }

                if (! $batch) {
                    $batch = Batch::query()
                        ->where('tenant_id', $return->tenant_id)
                        ->where('warehouse_id', $return->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->orderByDesc('created_at')
                        ->lockForUpdate()
                        ->first();
                }

                if ($batch) {
                    $beforeQuantity = (int) $batch->remaining;
                    $batch->quantity += $item->quantity_returned;
                    $batch->remaining += $item->quantity_returned;
                    $batch->save();

                    DB::table('inventory_movements')->insert([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $return->tenant_id,
                        'invoice_item_id' => $item->invoice_item_id,
                        'invoice_id' => $return->invoice_id,
                        'batch_id' => $batch->id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $return->warehouse_id,
                        'quantity' => $item->quantity_returned,
                        'quantity_before' => $beforeQuantity,
                        'quantity_after' => $beforeQuantity + $item->quantity_returned,
                        'variance' => $item->quantity_returned,
                        'profit' => 0,
                        'movement_type' => 'customer_return_in',
                        'source_type' => CustomerReturn::class,
                        'source_id' => $return->id,
                        'user_id' => $user->id,
                        'movement_date' => Carbon::now(),
                        'reason' => 'Retour client',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                } else {
                    $sourceInvoiceId = $return->invoice_id
                        ?? $return->deliveryNote?->saleOrder?->invoice_id
                        ?? $return->deliveryNote?->saleOrder?->invoice?->id;

                    $batch = Batch::create([
                        'invoice_id' => $sourceInvoiceId,
                        'tenant_id' => $return->tenant_id,
                        'warehouse_id' => $return->warehouse_id,
                        'product_id' => $item->product_id,
                        'unit_price' => (int) $item->unit_price_ht,
                        'quantity' => $item->quantity_returned,
                        'remaining' => $item->quantity_returned,
                        'benefit' => 0,
                        'expiration_date' => null,
                        'source_type' => CustomerReturn::class,
                        'source_id' => $return->id,
                        'origin' => 'customer_return',
                    ]);

                    DB::table('inventory_movements')->insert([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $return->tenant_id,
                        'invoice_item_id' => $item->invoice_item_id,
                        'invoice_id' => $return->invoice_id,
                        'batch_id' => $batch->id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $return->warehouse_id,
                        'quantity' => $item->quantity_returned,
                        'quantity_before' => 0,
                        'quantity_after' => $item->quantity_returned,
                        'variance' => $item->quantity_returned,
                        'profit' => 0,
                        'movement_type' => 'customer_return_in',
                        'source_type' => CustomerReturn::class,
                        'source_id' => $return->id,
                        'user_id' => $user->id,
                        'movement_date' => Carbon::now(),
                        'reason' => 'Retour client',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                }
            }

            CustomerReturn::where('tenant_id', $return->tenant_id)
                ->whereKey($return->id)
                ->update([
                    'status' => 'validated',
                    'validated_at' => now(),
                    'validated_by' => $user->id,
                    'updated_at' => now(),
                ]);

            $return->refresh();
            $this->customerCreditNoteService->createFromReturn($return->fresh(['items.product', 'invoice']), $user);

            return $return->fresh(['items', 'contact', 'invoice', 'deliveryNote', 'warehouse', 'movements.batch', 'creditNote.items.product', 'creditNote.invoice']);
        });
    }

    public function cancel(CustomerReturn $return, User $user): CustomerReturn
    {
        return DB::transaction(function () use ($return, $user) {
            $return = CustomerReturn::where('tenant_id', $user->tenant_id)
                ->whereKey($return->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($return->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Ce bon de retour client est déjà annulé.',
                ]);
            }

            if ($return->status === 'validated') {
                $return->load('movements.batch', 'items', 'creditNote.invoice');
                foreach ($return->movements as $movement) {
                    if (! $movement->batch) {
                        continue;
                    }

                    $movement->batch->quantity = max(0, $movement->batch->quantity - $movement->quantity);
                    $movement->batch->remaining = max(0, $movement->batch->remaining - $movement->quantity);
                    $movement->batch->save();
                }

                if ($return->creditNote) {
                    $creditNote = $return->creditNote;
                    $creditedInvoice = $creditNote->invoice;

                    $creditNote->forceFill([
                        'status' => 'cancelled',
                        'applied_amount' => 0,
                        'remaining_amount' => 0,
                        'cancelled_at' => now(),
                        'cancelled_by' => $user->id,
                    ])->save();

                    if ($creditedInvoice) {
                        $this->invoicePaymentStatusService->recalculate($creditedInvoice->fresh());
                    }
                }
            }

            CustomerReturn::where('tenant_id', $return->tenant_id)
                ->whereKey($return->id)
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => $user->id,
                    'updated_at' => now(),
                ]);

            $return->refresh();

            return $return->fresh(['items', 'contact', 'invoice', 'deliveryNote', 'warehouse', 'creditNote']);
        });
    }

    private function resolveSource(array $data, string $tenantId): array
    {
        $invoiceId = $data['invoice_id'] ?? null;
        $deliveryNoteId = $data['delivery_note_id'] ?? null;

        if (! $invoiceId && ! $deliveryNoteId) {
            throw ValidationException::withMessages([
                'source' => 'Veuillez sélectionner une facture ou un bon de livraison source.',
            ]);
        }

        if ($invoiceId) {
            $invoice = Invoice::where('tenant_id', $tenantId)->with('items')->whereKey($invoiceId)->firstOrFail();

            return [
                'contact_id' => $invoice->contact_id,
                'invoice_id' => $invoice->id,
                'delivery_note_id' => null,
                'warehouse_id' => $data['warehouse_id'] ?? $invoice->items->first()?->warehouse_id,
                'items' => $invoice->items->map(function (InvoiceItem $item) use ($tenantId) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity_sold' => (int) $item->quantity,
                        'unit_price_ht' => (int) $item->unit_price,
                        'tax_rate' => (float) $item->tax_rate,
                        'tax_amount' => (int) $item->tax_amount,
                        'total_ttc' => (int) $item->total_ttc,
                        'invoice_item_id' => $item->id,
                        'delivery_note_item_id' => null,
                        'batch_id' => $this->resolveInvoiceItemBatchId($tenantId, $item->id),
                    ];
                })->all(),
            ];
        }

        $deliveryNote = DeliveryNote::where('tenant_id', $tenantId)->with('items')->whereKey($deliveryNoteId)->firstOrFail();

        return [
            'contact_id' => $deliveryNote->contact_id,
            'invoice_id' => null,
            'delivery_note_id' => $deliveryNote->id,
            'warehouse_id' => $data['warehouse_id'] ?? $deliveryNote->warehouse_id,
            'items' => $deliveryNote->items->map(function ($item) use ($tenantId) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity_sold' => (int) ($item->quantity_delivered ?? $item->quantity_ordered ?? 0),
                    'unit_price_ht' => (int) ($item->unit_price ?? $item->unit_price_ht ?? 0),
                    'tax_rate' => (float) ($item->tax_rate ?? 0),
                    'tax_amount' => (int) ($item->tax_amount ?? 0),
                    'total_ttc' => (int) ($item->total_ttc ?? 0),
                    'invoice_item_id' => null,
                    'delivery_note_item_id' => $item->id,
                    'batch_id' => $this->resolveDeliveryItemBatchId($tenantId, $item->id),
                ];
            })->all(),
        ];
    }

    private function createItemFromSource(CustomerReturn $return, array $sourceItem, int $quantityReturned): void
    {
        CustomerReturnItem::create([
            'tenant_id' => $return->tenant_id,
            'customer_return_id' => $return->id,
            'product_id' => $sourceItem['product_id'],
            'invoice_item_id' => $sourceItem['invoice_item_id'],
            'delivery_note_item_id' => $sourceItem['delivery_note_item_id'],
            'batch_id' => $sourceItem['batch_id'] ?? null,
            'quantity_sold' => $sourceItem['quantity_sold'],
            'quantity_returned' => $quantityReturned,
            'unit_price_ht' => $sourceItem['unit_price_ht'],
            'tax_id' => null,
            'tax_rate' => $sourceItem['tax_rate'],
            'tax_amount' => $sourceItem['tax_amount'],
            'total_ttc' => $sourceItem['total_ttc'],
            'reason' => $return->reason,
        ]);
    }

    private function sourceQuantity(CustomerReturnItem $item): int
    {
        return (int) $item->quantity_sold;
    }

    private function resolveInvoiceItemBatchId(string $tenantId, string $invoiceItemId): ?string
    {
        return InventoryMovement::query()
            ->where('tenant_id', $tenantId)
            ->where('invoice_item_id', $invoiceItemId)
            ->whereNotNull('batch_id')
            ->latest('created_at')
            ->value('batch_id');
    }

    private function resolveDeliveryItemBatchId(string $tenantId, string $deliveryNoteItemId): ?string
    {
        return InventoryMovement::query()
            ->where('tenant_id', $tenantId)
            ->where('source_type', DeliveryNoteItem::class)
            ->where('source_id', $deliveryNoteItemId)
            ->whereNotNull('batch_id')
            ->latest('created_at')
            ->value('batch_id');
    }

    public function update(CustomerReturn $return, array $data, User $user): CustomerReturn
    {
        return DB::transaction(function () use ($return, $data, $user) {
            $return = CustomerReturn::where('tenant_id', $user->tenant_id)
                ->whereKey($return->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($return->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Seul un bon de retour en brouillon peut être modifié.',
                ]);
            }

            $source = $this->resolveSource($data, $user->tenant_id);

            $return->forceFill([
                'contact_id' => $source['contact_id'],
                'invoice_id' => $source['invoice_id'],
                'delivery_note_id' => $source['delivery_note_id'],
                'warehouse_id' => $source['warehouse_id'],
                'return_date' => $data['return_date'] ?? $return->return_date,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
            ])->save();

            $return->items()->delete();

            foreach ($source['items'] as $sourceItem) {
                $quantityReturned = (int) ($data['items'][$sourceItem['id']]['quantity_returned'] ?? 0);
                if ($quantityReturned <= 0) {
                    continue;
                }

                $this->createItemFromSource($return, $sourceItem, $quantityReturned);
            }

            return $return->fresh(['items', 'contact', 'invoice', 'deliveryNote', 'warehouse']);
        });
    }
}
