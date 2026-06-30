<?php

namespace App\Services;

use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\SaleOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleOrderConversionService
{
    public function __construct(private readonly DocumentNumberService $documentNumberService)
    {
    }

    public function toDelivery(SaleOrder $saleOrder): DeliveryNote
    {
        return DB::transaction(function () use ($saleOrder) {
            $saleOrder->refresh()->loadMissing('items');
            $this->ensureConvertible($saleOrder);

            if (! in_array($saleOrder->status, [SaleOrder::STATUS_DRAFT, SaleOrder::STATUS_CONFIRMED, SaleOrder::STATUS_PARTIALLY_DELIVERED], true)) {
                throw ValidationException::withMessages([
                    'sale_order' => 'Cette commande ne peut pas générer un bon de livraison dans son état actuel.',
                ]);
            }

            $delivery = DeliveryNote::create([
                'sale_order_id' => $saleOrder->id,
                'contact_id' => $saleOrder->contact_id,
                'warehouse_id' => $saleOrder->items->first()?->warehouse_id,
                'delivery_number' => $this->documentNumberService->generate('delivery_note', $saleOrder->tenant),
                'status' => 'draft',
                'delivery_date' => now()->toDateString(),
                'notes' => $saleOrder->notes,
            ]);

            foreach ($saleOrder->items as $item) {
                $delivery->items()->create([
                    'sale_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $item->warehouse_id,
                    'quantity_ordered' => $item->quantity_ordered,
                    'quantity_delivered' => 0,
                    'quantity_remaining' => $item->quantity_remaining,
                ]);
            }

            return $delivery->fresh(['items.product', 'saleOrder.contact']);
        });
    }

    public function toInvoice(SaleOrder $saleOrder): Invoice
    {
        return DB::transaction(function () use ($saleOrder) {
            $saleOrder->refresh()->loadMissing('items');
            $this->ensureConvertible($saleOrder, allowInvoiced: false);

            if ($saleOrder->invoice_id) {
                throw ValidationException::withMessages([
                    'sale_order' => 'Cette commande est déjà facturée.',
                ]);
            }

            $invoice = Invoice::create([
                'contact_id' => $saleOrder->contact_id,
                'sale_order_id' => $saleOrder->id,
                'invoice_number' => $this->documentNumberService->generate('customer_invoice', $saleOrder->tenant),
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'type' => Invoice::TYPE_CLIENT,
                'total_invoice' => $saleOrder->total_ttc,
                'total_ht' => $saleOrder->total_ht,
                'tax_amount' => $saleOrder->tax_amount,
                'discount_amount' => $saleOrder->total_discount,
                'balance' => 0,
                'status' => 'draft',
            ]);

            foreach ($saleOrder->items as $item) {
                $invoice->items()->create([
                    'warehouse_id' => $item->warehouse_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity_ordered,
                    'type' => 'out',
                    'unit_price' => $item->unit_price_ht,
                    'discount' => $item->discount_amount,
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,
                    'total_ht' => $item->subtotal_ht,
                    'total_ttc' => $item->total_ttc,
                    'total_line' => $item->total_ttc,
                    'expiration_date' => null,
                ]);
            }

            $saleOrder->update([
                'status' => SaleOrder::STATUS_INVOICED,
                'invoice_id' => $invoice->id,
            ]);

            return $invoice->fresh(['items.product', 'contact']);
        });
    }

    private function ensureConvertible(SaleOrder $saleOrder, bool $allowInvoiced = true): void
    {
        if ($saleOrder->tenant_id !== auth()->user()?->tenant_id) {
            throw ValidationException::withMessages([
                'sale_order' => 'Cette commande appartient à un autre tenant.',
            ]);
        }

        if (! $allowInvoiced && $saleOrder->status === SaleOrder::STATUS_INVOICED) {
            throw ValidationException::withMessages([
                'sale_order' => 'Cette commande est déjà facturée.',
            ]);
        }

        if ($saleOrder->status === SaleOrder::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'sale_order' => 'Une commande annulée ne peut pas être convertie.',
            ]);
        }
    }
}
