<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('items', []))->map(function (array $item) {
            if (! isset($item['unit_price_ht']) && isset($item['unit_price'])) {
                $item['unit_price_ht'] = $item['unit_price'];
            }

            if (! isset($item['discount_amount']) && isset($item['discount'])) {
                $item['discount_amount'] = $item['discount'];
            }

            return $item;
        })->all();

        $this->merge([
            'valid_until' => $this->input('valid_until', $this->input('expiry_date')),
            'items' => $items,
        ]);
    }

    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $quoteId = $this->route('quote')?->id;

        return [
            'contact_id' => ['required', 'uuid', Rule::exists('contacts', 'id')->where('tenant_id', $tenantId)],
            'quote_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:quote_date'],
            'status' => ['nullable', Rule::in([
                'draft',
                'sent',
                'accepted',
                'rejected',
                'expired',
                'converted',
            ])],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.warehouse_id' => ['required', 'uuid', Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],
            'items.*.product_id' => ['required', 'uuid', Rule::exists('products', 'id')->where('tenant_id', $tenantId)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price_ht' => ['required', 'integer', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'integer', 'min:0'],
            'items.*.tax_id' => ['nullable', 'uuid', Rule::exists('taxes', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at')],
            'items.*.tax_rate_id' => ['nullable', 'uuid', Rule::exists('tax_rates', 'id')->where('tenant_id', $tenantId)->where('is_active', true)],
        ];
    }
}
