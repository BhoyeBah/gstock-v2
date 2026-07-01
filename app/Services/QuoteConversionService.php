<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quote;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuoteConversionService
{
    public function __construct(private readonly DocumentNumberService $documentNumberService)
    {
    }

    public function toInvoice(Quote $quote): Invoice
    {
        return DB::transaction(function () use ($quote) {
            $quote->refresh()->loadMissing('items');
            $this->ensureConvertible($quote);

            $invoice = Invoice::create([
                'contact_id' => $quote->contact_id,
                'quote_id' => $quote->id,
                'invoice_number' => $this->documentNumberService->generate('customer_invoice', $quote->tenant),
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'type' => Invoice::TYPE_CLIENT,
                'total_invoice' => $quote->total_ttc,
                'total_ht' => $quote->total_ht,
                'tax_amount' => $quote->tax_amount,
                'discount_amount' => $quote->total_discount,
                'balance' => 0,
                'status' => 'draft',
            ]);

            foreach ($quote->items as $item) {
                $invoice->items()->create([
                    'warehouse_id' => $item->warehouse_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
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

            $quote->update([
                'status' => Quote::STATUS_CONVERTED,
                'converted_to_invoice_id' => $invoice->id,
                'converted_invoice_id' => $invoice->id,
                'converted_at' => now(),
            ]);

            return $invoice->fresh(['items.product', 'contact']);
        });
    }

    public function toSaleOrder(Quote $quote): SaleOrder
    {
        return DB::transaction(function () use ($quote) {
            $quote->refresh()->loadMissing('items');
            $this->ensureConvertible($quote);

            $saleOrder = SaleOrder::create([
                'contact_id' => $quote->contact_id,
                'quote_id' => $quote->id,
                'order_number' => $this->documentNumberService->generate('sale_order', $quote->tenant),
                'order_date' => now()->toDateString(),
                'status' => SaleOrder::STATUS_DRAFT,
                'total_ht' => $quote->total_ht,
                'total_discount' => $quote->total_discount,
                'tax_amount' => $quote->tax_amount,
                'total_ttc' => $quote->total_ttc,
                'created_by' => $quote->created_by,
                'notes' => $quote->notes,
            ]);

            foreach ($quote->items as $item) {
                $saleOrder->items()->create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $item->warehouse_id,
                    'quantity_ordered' => $item->quantity,
                    'quantity_delivered' => 0,
                    'quantity_remaining' => $item->quantity,
                    'unit_price_ht' => $item->unit_price_ht,
                    'discount_amount' => $item->discount_amount,
                    'subtotal_ht' => $item->subtotal_ht,
                    'tax_id' => $item->tax_id,
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,
                    'total_ttc' => $item->total_ttc,
                ]);
            }

            $quote->update([
                'status' => Quote::STATUS_CONVERTED,
                'converted_to_sale_order_id' => $saleOrder->id,
                'converted_sale_order_id' => $saleOrder->id,
                'converted_at' => now(),
            ]);

            return $saleOrder->fresh(['items.product', 'contact', 'quote']);
        });
    }

    private function ensureConvertible(Quote $quote): void
    {
        if ($quote->tenant_id !== auth()->user()?->tenant_id) {
            throw ValidationException::withMessages([
                'quote' => 'Ce devis appartient à un autre tenant.',
            ]);
        }

        if ($quote->status === Quote::STATUS_CONVERTED) {
            throw ValidationException::withMessages([
                'quote' => 'Ce devis a déjà été converti.',
            ]);
        }

        if (in_array($quote->status, [Quote::STATUS_REJECTED, Quote::STATUS_CANCELLED], true)) {
            throw ValidationException::withMessages([
                'quote' => 'Un devis rejeté ou annulé ne peut pas être converti.',
            ]);
        }

        if ($quote->converted_to_invoice_id || $quote->converted_to_sale_order_id) {
            throw ValidationException::withMessages([
                'quote' => 'Ce devis a déjà été converti.',
            ]);
        }
    }
}
