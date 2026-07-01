<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public function __construct(private readonly DocumentNumberService $documentNumberService)
    {
    }

    public function create(array $data, $user): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $user) {
            $contact = $this->resolveContact($user->tenant_id, $data['contact_id']);
            $taxRate = (float) (Setting::query()->value('tva') ?? 0);
            $items = $this->normalizeItems($data['items'], $user->tenant_id, $taxRate);

            $purchaseOrder = PurchaseOrder::create([
                'contact_id' => $contact->id,
                'purchase_number' => $this->documentNumberService->generate('purchase_order', $user->tenant),
                'purchase_date' => $data['purchase_date'],
                'status' => PurchaseOrder::STATUS_DRAFT,
                'total_ht' => collect($items)->sum('subtotal_ht'),
                'total_discount' => collect($items)->sum('discount_amount'),
                'tax_amount' => collect($items)->sum('tax_amount'),
                'total_ttc' => collect($items)->sum('total_ttc'),
                'created_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $item['purchase_order_id'] = $purchaseOrder->id;
                PurchaseOrderItem::create($item);
            }

            return $purchaseOrder->fresh(['items.product', 'contact']);
        });
    }

    public function update(PurchaseOrder $purchaseOrder, array $data, $user): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $data, $user) {
            if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'Seules les commandes en brouillon sont modifiables.',
                ]);
            }

            $taxRate = (float) (Setting::query()->value('tva') ?? 0);
            $items = $this->normalizeItems($data['items'], $user->tenant_id, $taxRate);

            $purchaseOrder->update([
                'contact_id' => $data['contact_id'],
                'purchase_date' => $data['purchase_date'],
                'total_ht' => collect($items)->sum('subtotal_ht'),
                'total_discount' => collect($items)->sum('discount_amount'),
                'tax_amount' => collect($items)->sum('tax_amount'),
                'total_ttc' => collect($items)->sum('total_ttc'),
                'notes' => $data['notes'] ?? null,
            ]);

            $purchaseOrder->items()->delete();

            foreach ($items as $item) {
                $purchaseOrder->items()->create($item);
            }

            return $purchaseOrder->fresh(['items.product', 'contact']);
        });
    }

    public function confirm(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_CONFIRMED]);
        return $purchaseOrder;
    }

    public function cancel(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
        return $purchaseOrder;
    }

    private function resolveContact(string $tenantId, string $contactId): Contact
    {
        return Contact::query()
            ->where('tenant_id', $tenantId)
            ->where('type', 'supplier')
            ->whereKey($contactId)
            ->firstOrFail();
    }

    private function normalizeItems(array $items, string $tenantId, float $taxRate): array
    {
        $rows = [];

        foreach ($items as $item) {
            $product = Product::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($item['product_id'])
                ->firstOrFail();

            $quantity = (int) $item['quantity_ordered'];
            $unitCost = (int) $item['unit_cost_ht'];
            $discount = max(0, (int) ($item['discount_amount'] ?? 0));
            $subtotal = max(($unitCost * $quantity) - $discount, 0);
            $taxAmount = (int) round($subtotal * $taxRate / 100);

            $rows[] = [
                'product_id' => $product->id,
                'warehouse_id' => $item['warehouse_id'] ?? null,
                'quantity_ordered' => $quantity,
                'quantity_received' => 0,
                'quantity_remaining' => $quantity,
                'unit_cost_ht' => $unitCost,
                'discount_amount' => $discount,
                'subtotal_ht' => $subtotal,
                'tax_id' => $item['tax_id'] ?? null,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_ttc' => $subtotal + $taxAmount,
                'expiration_date' => $item['expiration_date'] ?? null,
            ];
        }

        if (empty($rows)) {
            throw ValidationException::withMessages(['items' => 'Ajoute au moins une ligne.']);
        }

        return $rows;
    }
}
