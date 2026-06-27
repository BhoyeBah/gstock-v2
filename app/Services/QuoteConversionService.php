<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuoteConversionService
{
    public function convert(Quote $quote): Invoice
    {
        return DB::transaction(function () use ($quote) {
            $quote = Quote::where('tenant_id', $quote->tenant_id)
                ->lockForUpdate()
                ->with('items')
                ->whereKey($quote->id)
                ->firstOrFail();

            if ($quote->converted_invoice_id || $quote->status === Quote::STATUS_CONVERTED) {
                throw new \RuntimeException('Ce devis a déjà été converti.');
            }

            if ($quote->status !== Quote::STATUS_ACCEPTED) {
                throw new \RuntimeException('Seul un devis accepté peut être converti.');
            }

            $invoice = Invoice::create([
                'tenant_id' => $quote->tenant_id,
                'contact_id' => $quote->contact_id,
                'invoice_number' => null,
                'invoice_date' => $quote->quote_date ?? now(),
                'due_date' => $quote->expiry_date ?? $quote->quote_date ?? now(),
                'type' => Invoice::TYPE_CLIENT,
                'total_invoice' => $quote->total_ttc,
                'balance' => $quote->total_ttc,
            ]);

            foreach ($quote->items as $item) {
                InvoiceItem::create([
                    'id' => (string) Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'warehouse_id' => $item->warehouse_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'type' => 'out',
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'total_line' => $item->subtotal_ht,
                    'expiration_date' => null,
                ]);
            }

            $quote->update([
                'status' => Quote::STATUS_CONVERTED,
                'converted_invoice_id' => $invoice->id,
            ]);

            return $invoice->load('items.product', 'contact');
        });
    }
}
