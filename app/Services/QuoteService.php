<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function __construct(
        private readonly TaxCalculatorService $taxCalculatorService
    ) {
    }

    public function createQuote(array $data): Quote
    {
        return DB::transaction(function () use ($data) {
            $quote = Quote::create([
                'contact_id' => $data['contact_id'],
                'quote_number' => $data['quote_number'] ?? null,
                'quote_date' => $data['quote_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'status' => $data['status'] ?? Quote::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
                'subtotal_ht' => 0,
                'tax_total' => 0,
                'total_ttc' => 0,
            ]);

            $quote->generateQuoteNumber();
            $quote->save();

            $this->syncItems($quote, $data['items']);

            return $quote->load(['items.product', 'items.taxRate', 'contact', 'creator']);
        });
    }

    public function updateQuote(Quote $quote, array $data): Quote
    {
        return DB::transaction(function () use ($quote, $data) {
            if ($quote->status === Quote::STATUS_CONVERTED || $quote->converted_invoice_id) {
                throw new \RuntimeException('Le devis a déjà été converti.');
            }

            $quote->update([
                'contact_id' => $data['contact_id'],
                'quote_date' => $data['quote_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'status' => $data['status'] ?? $quote->status,
                'notes' => $data['notes'] ?? null,
            ]);

            $quote->items()->delete();
            $this->syncItems($quote, $data['items']);

            return $quote->load(['items.product', 'items.taxRate', 'contact', 'creator']);
        });
    }

    public function syncItems(Quote $quote, array $items): array
    {
        $totals = [
            'subtotal_ht' => 0,
            'tax_total' => 0,
            'total_ttc' => 0,
        ];

        foreach ($items as $item) {
            $calculated = $this->taxCalculatorService->calculateLine($item, $quote->tenant_id);

            QuoteItem::create([
                'tenant_id' => $quote->tenant_id,
                'quote_id' => $quote->id,
                'warehouse_id' => $item['warehouse_id'],
                'product_id' => $item['product_id'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (int) $item['unit_price'],
                'discount' => (int) ($item['discount'] ?? 0),
                'tax_rate_id' => $item['tax_rate_id'] ?? null,
                'subtotal_ht' => $calculated['subtotal_ht'],
                'tax_amount' => $calculated['tax_amount'],
                'total_ttc' => $calculated['total_ttc'],
            ]);

            $totals['subtotal_ht'] += $calculated['subtotal_ht'];
            $totals['tax_total'] += $calculated['tax_amount'];
            $totals['total_ttc'] += $calculated['total_ttc'];
        }

        $quote->update($totals);

        return $totals;
    }
}
