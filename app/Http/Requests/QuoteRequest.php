<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'contact_id' => [
                'required',
                'uuid',
                Rule::exists('contacts', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('type', 'client')),
            ],
            'quote_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quote_date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'uuid',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'items.*.warehouse_id' => [
                'nullable',
                'uuid',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price_ht' => ['required', 'integer', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'integer', 'min:0'],
            'items.*.tax_id' => [
                'nullable',
                'uuid',
                Rule::exists('taxes', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('is_active', true)->whereNull('deleted_at')),
            ],
        ];
    }
}
