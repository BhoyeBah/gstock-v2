<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryReconciliationService
{
    /**
     * Reconcile one physical inventory line and persist stock impact atomically.
     */
    public function reconcileItem(string $inventoryItemId, User $user, int $realQuantity, ?string $reason = null): InventoryItem
    {
        return DB::transaction(function () use ($inventoryItemId, $user, $realQuantity, $reason) {
            $tenantId = $user->tenant_id;

            $item = InventoryItem::with(['inventory', 'product'])
                ->whereKey($inventoryItemId)
                ->whereHas('inventory', fn ($query) => $query->where('tenant_id', $tenantId))
                ->lockForUpdate()
                ->first();

            if (! $item || ! $item->inventory) {
                throw (new ModelNotFoundException())->setModel(InventoryItem::class, [$inventoryItemId]);
            }

            if ($item->validated || $item->status === 'reconciled' || $item->reconciled_at) {
                throw ValidationException::withMessages([
                    'inventory_item' => 'Cette ligne d’inventaire a déjà été réconciliée.',
                ]);
            }

            $inventory = Inventory::where('tenant_id', $tenantId)
                ->whereKey($item->inventory_id)
                ->lockForUpdate()
                ->firstOrFail();

            $warehouseId = $inventory->warehouse_id;
            $theoreticalQuantity = (int) $item->theoretical_qty;
            $variance = $realQuantity - $theoreticalQuantity;
            $reason = $reason ?: 'Réconciliation inventaire '.$inventory->inventory_number;

            if ($variance < 0) {
                $this->decreaseStockFifo(
                    tenantId: $tenantId,
                    inventory: $inventory,
                    item: $item,
                    user: $user,
                    quantityToRemove: abs($variance),
                    reason: $reason
                );
            }

            if ($variance > 0) {
                $this->increaseStockFromInventoryGain(
                    tenantId: $tenantId,
                    inventory: $inventory,
                    item: $item,
                    user: $user,
                    quantityToAdd: $variance,
                    reason: $reason
                );
            }

            $item->forceFill([
                'real_qty' => $realQuantity,
                'variance' => $variance,
                'validated' => true,
                'status' => 'reconciled',
                'validated_at' => now(),
                'validated_by' => $user->id,
                'reconciled_at' => now(),
                'reconciled_by' => $user->id,
                'reason' => $reason,
            ])->save();

            $this->completeInventoryIfAllItemsAreReconciled($inventory);

            return $item->fresh(['inventory', 'product', 'movements.batch']);
        });
    }

    private function decreaseStockFifo(
        string $tenantId,
        Inventory $inventory,
        InventoryItem $item,
        User $user,
        int $quantityToRemove,
        string $reason
    ): void {
        $batches = Batch::where('tenant_id', $tenantId)
            ->where('warehouse_id', $inventory->warehouse_id)
            ->where('product_id', $item->product_id)
            ->where('remaining', '>', 0)
            ->orderByRaw('expiration_date IS NULL')
            ->orderBy('expiration_date')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        if ($batches->sum('remaining') < $quantityToRemove) {
            throw ValidationException::withMessages([
                'real_qty' => 'Stock insuffisant pour appliquer cet ajustement sans rendre un lot négatif.',
            ]);
        }

        $remainingToRemove = $quantityToRemove;

        foreach ($batches as $batch) {
            if ($remainingToRemove <= 0) {
                break;
            }

            $quantityConsumed = min((int) $batch->remaining, $remainingToRemove);
            $quantityBefore = (int) $batch->remaining;
            $quantityAfter = $quantityBefore - $quantityConsumed;

            $batch->forceFill(['remaining' => $quantityAfter])->save();

            $this->createMovement(
                tenantId: $tenantId,
                inventory: $inventory,
                item: $item,
                batch: $batch,
                user: $user,
                quantity: $quantityConsumed,
                quantityBefore: $quantityBefore,
                quantityAfter: $quantityAfter,
                variance: -$quantityConsumed,
                movementType: 'inventory_adjustment_out',
                reason: $reason
            );

            $remainingToRemove -= $quantityConsumed;
        }
    }

    private function increaseStockFromInventoryGain(
        string $tenantId,
        Inventory $inventory,
        InventoryItem $item,
        User $user,
        int $quantityToAdd,
        string $reason
    ): void {
        $unitPrice = (int) Batch::where('tenant_id', $tenantId)
            ->where('product_id', $item->product_id)
            ->latest()
            ->value('unit_price');

        $batch = Batch::create([
            'tenant_id' => $tenantId,
            'invoice_id' => null,
            'warehouse_id' => $inventory->warehouse_id,
            'product_id' => $item->product_id,
            'unit_price' => $unitPrice,
            'quantity' => $quantityToAdd,
            'benefit' => 0,
            'remaining' => $quantityToAdd,
            'expiration_date' => null,
            'source_type' => InventoryItem::class,
            'source_id' => $item->id,
            'origin' => 'inventory_gain',
        ]);

        $this->createMovement(
            tenantId: $tenantId,
            inventory: $inventory,
            item: $item,
            batch: $batch,
            user: $user,
            quantity: $quantityToAdd,
            quantityBefore: 0,
            quantityAfter: $quantityToAdd,
            variance: $quantityToAdd,
            movementType: 'inventory_adjustment_in',
            reason: $reason
        );
    }

    private function createMovement(
        string $tenantId,
        Inventory $inventory,
        InventoryItem $item,
        Batch $batch,
        User $user,
        int $quantity,
        int $quantityBefore,
        int $quantityAfter,
        int $variance,
        string $movementType,
        string $reason
    ): InventoryMovement {
        return InventoryMovement::create([
            'tenant_id' => $tenantId,
            'inventory_id' => $inventory->id,
            'inventory_item_id' => $item->id,
            'invoice_item_id' => null,
            'invoice_id' => null,
            'batch_id' => $batch->id,
            'product_id' => $item->product_id,
            'warehouse_id' => $inventory->warehouse_id,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'variance' => $variance,
            'movement_type' => $movementType,
            'source_type' => InventoryItem::class,
            'source_id' => $item->id,
            'user_id' => $user->id,
            'movement_date' => now(),
            'reason' => $reason,
        ]);
    }

    private function completeInventoryIfAllItemsAreReconciled(Inventory $inventory): void
    {
        $hasPendingItems = InventoryItem::where('inventory_id', $inventory->id)
            ->where(function ($query) {
                $query->where('validated', false)
                    ->orWhereNull('validated');
            })
            ->exists();

        if ($hasPendingItems) {
            return;
        }

        $inventory->forceFill([
            'closed_at' => now(),
            'status' => 'completed',
        ])->save();
    }
}
