<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleOrderService
{
    public function __construct(private readonly DocumentNumberService $documentNumberService)
    {
    }

    public function create(array $data, $user): SaleOrder
    {
        return DB::transaction(function () use ($data, $user) {
            $contact = $this->resolveContact($user->tenant_id, $data['contact_id']);
            $taxRate = (float) (Setting::query()->where('tenant_id', $user->tenant_id)->value('tva') ?? 0);
            $items = $this->normalizeItems($data['items'], $user->tenant_id, $taxRate);

            $saleOrder = SaleOrder::create([
                'contact_id' => $contact->id,
                'quote_id' => $data['quote_id'] ?? null,
                'order_number' => $this->documentNumberService->generate('sale_order', $user->tenant),
                'order_date' => $data['order_date'],
                'status' => SaleOrder::STATUS_DRAFT,
                'total_ht' => collect($items)->sum('subtotal_ht'),
                'total_discount' => collect($items)->sum('discount_amount'),
                'tax_amount' => collect($items)->sum('tax_amount'),
                'total_ttc' => collect($items)->sum('total_ttc'),
                'created_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $item['sale_order_id'] = $saleOrder->id;
                SaleOrderItem::create($item);
            }

            return $saleOrder->fresh(['items.product', 'contact', 'quote']);
        });
    }

    public function update(SaleOrder $saleOrder, array $data, $user): SaleOrder
    {
        return DB::transaction(function () use ($saleOrder, $data, $user) {
            if ($saleOrder->status !== SaleOrder::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'sale_order' => 'Seules les commandes en brouillon sont modifiables.',
                ]);
            }

            $taxRate = (float) (Setting::query()->where('tenant_id', $user->tenant_id)->value('tva') ?? 0);
            $items = $this->normalizeItems($data['items'], $user->tenant_id, $taxRate);

            $saleOrder->update([
                'contact_id' => $data['contact_id'],
                'order_date' => $data['order_date'],
                'total_ht' => collect($items)->sum('subtotal_ht'),
                'total_discount' => collect($items)->sum('discount_amount'),
                'tax_amount' => collect($items)->sum('tax_amount'),
                'total_ttc' => collect($items)->sum('total_ttc'),
                'notes' => $data['notes'] ?? null,
            ]);

            $saleOrder->items()->delete();

            foreach ($items as $item) {
                $saleOrder->items()->create($item);
            }

            return $saleOrder->fresh(['items.product', 'contact']);
        });
    }

    public function confirm(SaleOrder $saleOrder): SaleOrder
    {
        $saleOrder->update(['status' => SaleOrder::STATUS_CONFIRMED]);
        return $saleOrder;
    }

    public function cancel(SaleOrder $saleOrder): SaleOrder
    {
        $saleOrder->update(['status' => SaleOrder::STATUS_CANCELLED]);
        return $saleOrder;
    }

    private function resolveContact(string $tenantId, string $contactId): Contact
    {
        return Contact::query()
            ->where('tenant_id', $tenantId)
            ->where('type', 'client')
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
            $unitPrice = (int) $item['unit_price_ht'];
            $discount = max(0, (int) ($item['discount_amount'] ?? 0));
            $subtotal = max(($unitPrice * $quantity) - $discount, 0);
            $taxAmount = (int) round($subtotal * $taxRate / 100);

            $rows[] = [
                'product_id' => $product->id,
                'warehouse_id' => $item['warehouse_id'] ?? null,
                'quantity_ordered' => $quantity,
                'quantity_delivered' => 0,
                'quantity_remaining' => $quantity,
                'unit_price_ht' => $unitPrice,
                'discount_amount' => $discount,
                'subtotal_ht' => $subtotal,
                'tax_id' => $item['tax_id'] ?? null,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_ttc' => $subtotal + $taxAmount,
            ];
        }

        if (empty($rows)) {
            throw ValidationException::withMessages(['items' => 'Ajoute au moins une ligne.']);
        }

        return $rows;
    }
}
