<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderRequest extends FormRequest
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
                Rule::exists('contacts', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('type', 'supplier')),
            ],
            'purchase_date' => ['required', 'date'],
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
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost_ht' => ['required', 'integer', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'integer', 'min:0'],
            'items.*.expiration_date' => ['nullable', 'date'],
            'items.*.tax_id' => [
                'nullable',
                'uuid',
                Rule::exists('taxes', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('is_active', true)->whereNull('deleted_at')),
            ],
        ];
    }
}
