<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReceiptService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly StockInService $stockInService
    ) {
    }

    public function create(array $data, $user): GoodsReceipt
    {
        return DB::transaction(function () use ($data, $user) {
            $purchaseOrder = PurchaseOrder::query()
                ->where('tenant_id', $user->tenant_id)
                ->whereKey($data['purchase_order_id'])
                ->with('items')
                ->firstOrFail();

            $receipt = GoodsReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'contact_id' => $purchaseOrder->contact_id,
                'warehouse_id' => $data['warehouse_id'],
                'receipt_number' => $this->documentNumberService->generate('goods_receipt', $user->tenant),
                'status' => 'draft',
                'receipt_date' => $data['receipt_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($purchaseOrder->items as $purchaseOrderItem) {
                $receipt->items()->create([
                    'purchase_order_item_id' => $purchaseOrderItem->id,
                    'product_id' => $purchaseOrderItem->product_id,
                    'warehouse_id' => $purchaseOrderItem->warehouse_id ?? $data['warehouse_id'],
                    'quantity_ordered' => $purchaseOrderItem->quantity_ordered,
                    'quantity_received' => 0,
                    'quantity_remaining' => $purchaseOrderItem->quantity_remaining,
                    'unit_cost_ht' => $purchaseOrderItem->unit_cost_ht,
                    'expiration_date' => $purchaseOrderItem->expiration_date,
                ]);
            }

            return $receipt->fresh(['items.product', 'purchaseOrder.items.product', 'contact', 'warehouse']);
        });
    }

    public function validate(GoodsReceipt $receipt, $user): GoodsReceipt
    {
        return DB::transaction(function () use ($receipt, $user) {
            $receipt->refresh()->load('items.purchaseOrderItem');

            if ($receipt->status === 'validated') {
                throw ValidationException::withMessages([
                    'goods_receipt' => 'Ce bon de réception a déjà été validé.',
                ]);
            }

            foreach ($receipt->items as $item) {
                $this->stockInService->consume($item, $receipt->tenant_id, $user->id);
                if ($item->purchaseOrderItem) {
                    $item->purchaseOrderItem->quantity_received += $item->quantity_remaining;
                    $item->purchaseOrderItem->quantity_remaining = max($item->purchaseOrderItem->quantity_ordered - $item->purchaseOrderItem->quantity_received, 0);
                    $item->purchaseOrderItem->save();
                }

                $item->quantity_received = $item->quantity_remaining;
                $item->quantity_remaining = 0;
                $item->save();
            }

            $receipt->update([
                'status' => 'validated',
                'validated_at' => now(),
                'validated_by' => $user->id,
            ]);

            $purchaseOrder = $receipt->purchaseOrder()->with('items')->first();
            if ($purchaseOrder) {
                $received = $purchaseOrder->items->sum('quantity_received');
                $ordered = $purchaseOrder->items->sum('quantity_ordered');
                $purchaseOrder->status = $received >= $ordered ? 'received' : 'partially_received';
                $purchaseOrder->save();
            }

            return $receipt->fresh(['items.product', 'purchaseOrder.items', 'contact']);
        });
    }

    public function cancel(GoodsReceipt $receipt, $user): GoodsReceipt
    {
        $receipt->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $user->id,
        ]);

        return $receipt;
    }
}
