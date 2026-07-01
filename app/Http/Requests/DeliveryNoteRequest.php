<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'sale_order_id' => [
                'required',
                'uuid',
                Rule::exists('sale_orders', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'warehouse_id' => [
                'required',
                'uuid',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'delivery_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
