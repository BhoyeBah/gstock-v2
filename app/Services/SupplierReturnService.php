<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InventoryMovement;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupplierReturnService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService
    ) {
    }

    public function create(array $data, User $user): SupplierReturn
    {
        return DB::transaction(function () use ($data, $user) {
            $tenantId = $user->tenant_id;
            $source = $this->resolveSource($data, $tenantId);

            $return = SupplierReturn::create([
                'tenant_id' => $tenantId,
                'return_number' => $this->documentNumberService->generate('supplier_return', $user->tenant),
                'contact_id' => $source['contact_id'],
                'supplier_invoice_id' => $source['supplier_invoice_id'],
                'goods_receipt_id' => $source['goods_receipt_id'],
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

                SupplierReturnItem::create([
                    'tenant_id' => $tenantId,
                    'supplier_return_id' => $return->id,
                    'product_id' => $sourceItem['product_id'],
                    'goods_receipt_item_id' => $sourceItem['goods_receipt_item_id'],
                    'invoice_item_id' => $sourceItem['invoice_item_id'],
                    'batch_id' => $sourceItem['batch_id'] ?? null,
                    'quantity_received' => $sourceItem['quantity_received'],
                    'quantity_returned' => $quantityReturned,
                    'unit_cost_ht' => $sourceItem['unit_cost_ht'],
                    'tax_id' => null,
                    'tax_rate' => $sourceItem['tax_rate'],
                    'tax_amount' => $sourceItem['tax_amount'],
                    'total_ttc' => $sourceItem['total_ttc'],
                    'reason' => $return->reason,
                ]);
            }

            return $return->load(['items', 'contact', 'supplierInvoice', 'goodsReceipt', 'warehouse']);
        });
    }

    public function validateReturn(SupplierReturn $return, User $user): SupplierReturn
    {
        return DB::transaction(function () use ($return, $user) {
            $return = SupplierReturn::where('tenant_id', $user->tenant_id)
                ->whereKey($return->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($return->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Ce bon de retour fournisseur a déjà été traité.',
                ]);
            }

            $return->load('items.product', 'items.invoiceItem', 'items.goodsReceiptItem');

            foreach ($return->items as $item) {
                if ($item->quantity_returned <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Chaque ligne doit contenir une quantité retournée positive.',
                    ]);
                }

                $available = $this->availableStock($return->tenant_id, $item->product_id, $return->warehouse_id);
                if ($item->quantity_returned > $available) {
                    throw ValidationException::withMessages([
                        'items' => "Quantité supérieure au stock disponible pour {$item->product->name}.",
                    ]);
                }
            }

            foreach ($return->items as $item) {
                $batches = $this->resolveCandidateBatches($return->tenant_id, $return->warehouse_id, $item);

                if ($batches->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => "Aucun lot disponible pour {$item->product->name}.",
                    ]);
                }

                $remainingToRemove = $item->quantity_returned;

                foreach ($batches as $batch) {
                    if ($remainingToRemove <= 0) {
                        break;
                    }

                    $deduct = min((int) $batch->remaining, $remainingToRemove);
                    $beforeQuantity = (int) $batch->remaining;
                    $batch->remaining -= $deduct;
                    $batch->quantity = max(0, $batch->quantity - $deduct);
                    $batch->save();

                    DB::table('inventory_movements')->insert([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $return->tenant_id,
                        'invoice_item_id' => $item->invoice_item_id,
                        'invoice_id' => $item->invoiceItem?->invoice_id,
                        'batch_id' => $batch->id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $return->warehouse_id,
                        'quantity' => $deduct,
                        'quantity_before' => $beforeQuantity,
                        'quantity_after' => $beforeQuantity - $deduct,
                        'variance' => -$deduct,
                        'profit' => 0,
                        'movement_type' => 'supplier_return_out',
                        'source_type' => SupplierReturn::class,
                        'source_id' => $return->id,
                        'user_id' => $user->id,
                        'movement_date' => Carbon::now(),
                        'reason' => 'Retour fournisseur',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $remainingToRemove -= $deduct;
                }

                if ($remainingToRemove > 0) {
                    throw ValidationException::withMessages([
                        'items' => "Impossible de retourner {$item->quantity_returned} unités de {$item->product->name}.",
                    ]);
                }
            }

            SupplierReturn::where('tenant_id', $return->tenant_id)
                ->whereKey($return->id)
                ->update([
                    'status' => 'validated',
                    'validated_at' => now(),
                    'validated_by' => $user->id,
                    'updated_at' => now(),
                ]);

            $return->refresh();

            return $return->fresh(['items.product', 'contact', 'supplierInvoice', 'goodsReceipt', 'warehouse', 'movements.batch']);
        });
    }

    public function cancel(SupplierReturn $return, User $user): SupplierReturn
    {
        return DB::transaction(function () use ($return, $user) {
            $return = SupplierReturn::where('tenant_id', $user->tenant_id)
                ->whereKey($return->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($return->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Ce bon de retour fournisseur est déjà annulé.',
                ]);
            }

            if ($return->status === 'validated') {
                $return->load('movements.batch');
                foreach ($return->movements as $movement) {
                    if (! $movement->batch) {
                        continue;
                    }

                    $movement->batch->remaining += $movement->quantity;
                    $movement->batch->quantity += $movement->quantity;
                    $movement->batch->save();
                }
            }

            SupplierReturn::where('tenant_id', $return->tenant_id)
                ->whereKey($return->id)
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => $user->id,
                    'updated_at' => now(),
                ]);

            $return->refresh();

            return $return->fresh(['items', 'contact', 'supplierInvoice', 'goodsReceipt', 'warehouse']);
        });
    }

    private function resolveSource(array $data, string $tenantId): array
    {
        $supplierInvoiceId = $data['supplier_invoice_id'] ?? null;
        $goodsReceiptId = $data['goods_receipt_id'] ?? null;

        if (! $supplierInvoiceId && ! $goodsReceiptId) {
            throw ValidationException::withMessages([
                'source' => 'Veuillez sélectionner une facture fournisseur ou un bon de réception source.',
            ]);
        }

        if ($supplierInvoiceId) {
            $invoice = Invoice::where('tenant_id', $tenantId)->with('items')->whereKey($supplierInvoiceId)->firstOrFail();

            return [
                'contact_id' => $invoice->contact_id,
                'supplier_invoice_id' => $invoice->id,
                'goods_receipt_id' => null,
                'warehouse_id' => $data['warehouse_id'] ?? $invoice->items->first()?->warehouse_id,
                'items' => $invoice->items->map(function (InvoiceItem $item) use ($tenantId) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity_received' => (int) $item->quantity,
                        'unit_cost_ht' => (int) $item->unit_price,
                        'tax_rate' => (float) $item->tax_rate,
                        'tax_amount' => (int) $item->tax_amount,
                        'total_ttc' => (int) $item->total_ttc,
                        'goods_receipt_item_id' => null,
                        'invoice_item_id' => $item->id,
                        'batch_id' => $this->resolveInvoiceItemBatchId($tenantId, $item->id),
                    ];
                })->all(),
            ];
        }

        $receipt = GoodsReceipt::where('tenant_id', $tenantId)->with('items')->whereKey($goodsReceiptId)->firstOrFail();

        return [
            'contact_id' => $receipt->contact_id,
            'supplier_invoice_id' => null,
            'goods_receipt_id' => $receipt->id,
            'warehouse_id' => $data['warehouse_id'] ?? $receipt->warehouse_id,
            'items' => $receipt->items->map(function (GoodsReceiptItem $item) use ($tenantId) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity_received' => (int) $item->quantity_received,
                    'unit_cost_ht' => (int) ($item->unit_cost_ht ?? $item->unit_price_ht ?? 0),
                    'tax_rate' => (float) ($item->tax_rate ?? 0),
                    'tax_amount' => (int) ($item->tax_amount ?? 0),
                    'total_ttc' => (int) ($item->total_ttc ?? 0),
                    'goods_receipt_item_id' => $item->id,
                    'invoice_item_id' => null,
                    'batch_id' => $this->resolveReceiptItemBatchId($tenantId, $item->id),
                ];
            })->all(),
        ];
    }

    private function availableStock(string $tenantId, string $productId, ?string $warehouseId): int
    {
        return (int) Batch::query()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->sum('remaining');
    }

    private function resolveCandidateBatches(string $tenantId, ?string $warehouseId, SupplierReturnItem $item)
    {
        if ($item->batch_id) {
            $batch = Batch::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($item->batch_id)
                ->lockForUpdate()
                ->get();

            if ($batch->isNotEmpty()) {
                return $batch;
            }
        }

        return Batch::query()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $item->product_id)
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->where('remaining', '>', 0)
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();
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

    private function resolveReceiptItemBatchId(string $tenantId, string $goodsReceiptItemId): ?string
    {
        return InventoryMovement::query()
            ->where('tenant_id', $tenantId)
            ->where('source_type', GoodsReceiptItem::class)
            ->where('source_id', $goodsReceiptItemId)
            ->whereNotNull('batch_id')
            ->latest('created_at')
            ->value('batch_id');
    }

    public function update(SupplierReturn $return, array $data, User $user): SupplierReturn
    {
        return DB::transaction(function () use ($return, $data, $user) {
            $return = SupplierReturn::where('tenant_id', $user->tenant_id)
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
                'supplier_invoice_id' => $source['supplier_invoice_id'],
                'goods_receipt_id' => $source['goods_receipt_id'],
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

                SupplierReturnItem::create([
                    'tenant_id' => $return->tenant_id,
                    'supplier_return_id' => $return->id,
                    'product_id' => $sourceItem['product_id'],
                    'goods_receipt_item_id' => $sourceItem['goods_receipt_item_id'],
                    'invoice_item_id' => $sourceItem['invoice_item_id'],
                    'batch_id' => $sourceItem['batch_id'] ?? null,
                    'quantity_received' => $sourceItem['quantity_received'],
                    'quantity_returned' => $quantityReturned,
                    'unit_cost_ht' => $sourceItem['unit_cost_ht'],
                    'tax_id' => null,
                    'tax_rate' => $sourceItem['tax_rate'],
                    'tax_amount' => $sourceItem['tax_amount'],
                    'total_ttc' => $sourceItem['total_ttc'],
                    'reason' => $return->reason,
                ]);
            }

            return $return->fresh(['items', 'contact', 'supplierInvoice', 'goodsReceipt', 'warehouse']);
        });
    }
}
