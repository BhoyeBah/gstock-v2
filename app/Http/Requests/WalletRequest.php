<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id ?? $this->tenant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('wallets')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($this->wallet?->id),
            ],

            'identifier' => [
                'required',
                'string',
                'max:50',
            ],

            'initial_balance' => [
                'nullable',
                'integer',
                'min:0', // ✅ accepte 0
            ],

            'type' => [
                'required',
                Rule::in(['wave', 'orange', 'bank', 'other']),
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du wallet est obligatoire.',

            'code.required' => 'Le code du wallet est obligatoire.',
            'code.unique' => 'Ce code est déjà utilisé pour votre entreprise.',

            'identifier.required' => 'L’identifiant du wallet est obligatoire.',
            'identifier.string' => 'L’identifiant doit être une chaîne valide.',

            'initial_balance.integer' => 'Le solde initial doit être un nombre.',
            'initial_balance.min' => 'Le solde initial ne peut pas être négatif.',

            'type.required' => 'Le type de wallet est obligatoire.',
            'type.in' => 'Type de wallet invalide.',

            'is_active.boolean' => 'Le statut doit être vrai ou faux.',
        ];
    }
}
