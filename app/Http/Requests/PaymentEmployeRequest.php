<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentEmployeRequest extends FormRequest
{
    /**
     * Autorisation
     */
    public function authorize(): bool
    {
        // Mets true ou une policy plus tard
        return true;
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'wallet_id' => [
                Rule::requiredIf(function () {
                    return in_array($this->input('type'), [
                        'salary_payment',
                        'advance',
                        'bonus',
                        'advance_repayment',
                    ]);
                }),
                'nullable',
                'uuid',
                Rule::exists('wallets', 'id')->where('tenant_id', auth()->user()->tenant_id),
            ],

            'amount' => [
                'required',
                'integer',
                'min:1',
            ],

            'type' => [
                'required',
                Rule::in([
                    'salary_payment',
                    'advance',
                    'advance_repayment',
                    'bonus',
                    'deduction',
                ]),
            ],

            'date' => [
                'nullable',
                'date',
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Messages personnalisés (optionnel mais pro)
     */
    public function messages(): array
    {
        return [
            'wallet_id.required' => 'Le wallet est obligatoire.',
            'wallet_id.exists' => 'Le wallet sélectionné est invalide.',

            'amount.required' => 'Le montant est obligatoire.',
            'amount.min' => 'Le montant doit être supérieur à 0.',

            'type.required' => 'Le type de paiement est obligatoire.',
            'type.in' => 'Le type de paiement est invalide.',
        ];
    }
}
