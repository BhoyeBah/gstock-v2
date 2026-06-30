<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'purchase_order_id' => [
                'required',
                'uuid',
                Rule::exists('purchase_orders', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'warehouse_id' => [
                'required',
                'uuid',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
