<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $taxRateId = $this->route('tax_rate')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tax_rates')
                    ->ignore($taxRateId)
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
