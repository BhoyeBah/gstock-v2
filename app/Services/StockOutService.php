<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\DeliveryNoteItem;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class StockOutService
{
    public function consume(DeliveryNoteItem $item, string $tenantId, string $userId): void
    {
        $quantityToRemove = (int) $item->quantity_remaining;
        if ($quantityToRemove <= 0) {
            return;
        }

        $batches = Batch::query()
            ->where('tenant_id', $tenantId)
            ->where('warehouse_id', $item->warehouse_id)
            ->where('product_id', $item->product_id)
            ->where('remaining', '>', 0)
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        $availableStock = (int) $batches->sum('remaining');
        if ($availableStock < $quantityToRemove) {
            throw ValidationException::withMessages([
                'stock' => 'Stock insuffisant pour le produit sélectionné.',
            ]);
        }

        foreach ($batches as $batch) {
            if ($quantityToRemove <= 0) {
                break;
            }

            $available = (int) $batch->remaining;
            if ($available <= 0) {
                continue;
            }

            $used = min($available, $quantityToRemove);
            $before = $available;
            $after = $available - $used;
            $salePrice = (int) $item->saleOrderItem?->unit_price_ht ?? 0;
            $cost = (int) $batch->unit_price;
            $profit = ($salePrice - $cost) * $used;

            DB::table('batches')
                ->where('id', $batch->id)
                ->update([
                    'remaining' => $after,
                    'benefit' => ((int) $batch->benefit) + $profit,
                    'updated_at' => now(),
                ]);

            InventoryMovement::create([
                'tenant_id' => $tenantId,
                'invoice_item_id' => null,
                'invoice_id' => null,
                'batch_id' => $batch->id,
                'product_id' => $item->product_id,
                'warehouse_id' => $item->warehouse_id,
                'quantity' => $used,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'variance' => -$used,
                'movement_type' => 'delivery_out',
                'source_type' => DeliveryNoteItem::class,
                'source_id' => $item->id,
                'user_id' => $userId,
                'movement_date' => now(),
                'reason' => 'livraison',
            ]);

            $quantityToRemove -= $used;
        }
    }
}
