<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockInService
{
    public function consume(GoodsReceiptItem $item, string $tenantId, string $userId): void
    {
        $quantity = (int) ($item->quantity_received ?: $item->quantity_remaining);

        $batch = Batch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'warehouse_id' => $item->warehouse_id,
            'product_id' => $item->product_id,
            'unit_price' => $item->unit_cost_ht,
            'quantity' => $quantity,
            'remaining' => $quantity,
            'expiration_date' => $item->expiration_date,
            'origin' => 'receipt',
            'source_type' => GoodsReceiptItem::class,
            'source_id' => $item->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        InventoryMovement::create([
            'tenant_id' => $tenantId,
            'invoice_item_id' => null,
            'invoice_id' => null,
            'batch_id' => $batch->id,
            'product_id' => $item->product_id,
            'warehouse_id' => $item->warehouse_id,
            'quantity' => $quantity,
            'quantity_before' => 0,
            'quantity_after' => $quantity,
            'variance' => $quantity,
            'movement_type' => 'receipt_in',
            'source_type' => GoodsReceiptItem::class,
            'source_id' => $item->id,
            'user_id' => $userId,
            'movement_date' => now(),
            'reason' => 'réception',
        ]);
    }
}
