<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\TaxRate;

class TaxCalculatorService
{
    public function resolveRate(string $tenantId, ?string $taxRateId = null): float
    {
        if ($taxRateId) {
            $taxRate = TaxRate::where('tenant_id', $tenantId)
                ->whereKey($taxRateId)
                ->where('is_active', true)
                ->firstOrFail();

            return (float) $taxRate->rate;
        }

        $defaultRate = TaxRate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if ($defaultRate) {
            return (float) $defaultRate->rate;
        }

        $setting = Setting::where('tenant_id', $tenantId)->first();

        return (float) ($setting->tva ?? 0);
    }

    public function calculateLine(array $item, string $tenantId): array
    {
        $quantity = (int) ($item['quantity'] ?? 0);
        $unitPrice = (int) ($item['unit_price'] ?? 0);
        $discount = (int) ($item['discount'] ?? 0);
        $subtotalHt = max(0, ($unitPrice * $quantity) - $discount);
        $rate = $this->resolveRate($tenantId, $item['tax_rate_id'] ?? null);
        $taxAmount = (int) round($subtotalHt * ($rate / 100));
        $totalTtc = $subtotalHt + $taxAmount;

        return [
            'subtotal_ht' => $subtotalHt,
            'tax_amount' => $taxAmount,
            'total_ttc' => $totalTtc,
            'tax_rate' => $rate,
        ];
    }

    public function calculateTotals(array $items, string $tenantId): array
    {
        $subtotalHt = 0;
        $taxTotal = 0;
        $totalTtc = 0;

        foreach ($items as $item) {
            $line = $this->calculateLine($item, $tenantId);
            $subtotalHt += $line['subtotal_ht'];
            $taxTotal += $line['tax_amount'];
            $totalTtc += $line['total_ttc'];
        }

        return [
            'subtotal_ht' => $subtotalHt,
            'tax_total' => $taxTotal,
            'total_ttc' => $totalTtc,
        ];
    }
}
