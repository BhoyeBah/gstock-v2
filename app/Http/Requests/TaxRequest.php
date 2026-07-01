<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;
        $taxId = $this->route('tax')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('taxes', 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)->whereNull('deleted_at'))
                    ->ignore($taxId),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la taxe est obligatoire.',
            'name.unique' => 'Une taxe avec ce nom existe déjà.',
            'rate.required' => 'Le taux est obligatoire.',
            'rate.min' => 'Le taux ne peut pas être négatif.',
            'rate.max' => 'Le taux ne peut pas dépasser 100%.',
        ];
    }
}
