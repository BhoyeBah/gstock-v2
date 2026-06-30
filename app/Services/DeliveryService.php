<?php

namespace App\Services;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\SaleOrder;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly StockOutService $stockOutService
    ) {
    }

    public function create(array $data, $user): DeliveryNote
    {
        return DB::transaction(function () use ($data, $user) {
            $saleOrder = SaleOrder::query()
                ->where('tenant_id', $user->tenant_id)
                ->whereKey($data['sale_order_id'])
                ->with('items')
                ->firstOrFail();

            $delivery = DeliveryNote::create([
                'sale_order_id' => $saleOrder->id,
                'contact_id' => $saleOrder->contact_id,
                'warehouse_id' => $data['warehouse_id'],
                'delivery_number' => $this->documentNumberService->generate('delivery_note', $user->tenant),
                'status' => 'draft',
                'delivery_date' => $data['delivery_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($saleOrder->items as $saleOrderItem) {
                $delivery->items()->create([
                    'sale_order_item_id' => $saleOrderItem->id,
                    'product_id' => $saleOrderItem->product_id,
                    'warehouse_id' => $saleOrderItem->warehouse_id ?? $data['warehouse_id'],
                    'quantity_ordered' => $saleOrderItem->quantity_ordered,
                    'quantity_delivered' => 0,
                    'quantity_remaining' => $saleOrderItem->quantity_remaining,
                ]);
            }

            return $delivery->fresh(['items.product', 'saleOrder.items.product', 'contact', 'warehouse']);
        });
    }

    public function validate(DeliveryNote $deliveryNote, $user): DeliveryNote
    {
        return DB::transaction(function () use ($deliveryNote, $user) {
            $deliveryNote->refresh()->load('items.saleOrderItem');

            if ($deliveryNote->status === 'validated') {
                throw ValidationException::withMessages([
                    'delivery_note' => 'Ce bon de livraison a déjà été validé.',
                ]);
            }

            foreach ($deliveryNote->items as $item) {
                $this->stockOutService->consume($item, $deliveryNote->tenant_id, $user->id);
                $saleOrderItem = $item->saleOrderItem;
                if ($saleOrderItem) {
                    $saleOrderItem->quantity_delivered += $item->quantity_remaining;
                    $saleOrderItem->quantity_remaining = max($saleOrderItem->quantity_ordered - $saleOrderItem->quantity_delivered, 0);
                    $saleOrderItem->save();
                }

                $item->quantity_delivered = $item->quantity_remaining;
                $item->quantity_remaining = 0;
                $item->save();
            }

            $deliveryNote->update([
                'status' => 'validated',
                'validated_at' => now(),
                'validated_by' => $user->id,
            ]);

            $saleOrder = $deliveryNote->saleOrder()->with('items')->first();
            if ($saleOrder) {
                $delivered = $saleOrder->items->sum('quantity_delivered');
                $ordered = $saleOrder->items->sum('quantity_ordered');
                $saleOrder->status = $delivered >= $ordered ? 'delivered' : 'partially_delivered';
                $saleOrder->save();
            }

            return $deliveryNote->fresh(['items.product', 'saleOrder.items', 'contact']);
        });
    }

    public function cancel(DeliveryNote $deliveryNote, $user): DeliveryNote
    {
        $deliveryNote->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $user->id,
        ]);

        return $deliveryNote;
    }
}
