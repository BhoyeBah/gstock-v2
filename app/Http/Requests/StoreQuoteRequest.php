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

    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $quoteId = $this->route('quote')?->id;

        return [
            'contact_id' => ['required', 'uuid', Rule::exists('contacts', 'id')->where('tenant_id', $tenantId)],
            'quote_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:quote_date'],
            'status' => ['required', Rule::in([
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
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.discount' => ['nullable', 'integer', 'min:0'],
            'items.*.tax_rate_id' => ['nullable', 'uuid', Rule::exists('tax_rates', 'id')->where('tenant_id', $tenantId)],
        ];
    }
}
