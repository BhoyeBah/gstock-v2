<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    /**
     * Autorisation : on autorise l'utilisateur connecté à faire la requête.
     */
    public function authorize(): bool
    {
        // Tu peux ajouter ici une vérification de rôle ou de permission si nécessaire
        return true;
    }

    /**
     * Règles de validation pour créer ou mettre à jour un paiement.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;
        $invoiceType = rtrim((string) $this->route('type'), 's');

        return [
            'invoice_id' => [
                'required',
                'uuid',
                Rule::exists('invoices', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                    ->where('type', $invoiceType)
                ),
            ],
            'wallet_id' => [
                'required',
                'uuid',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)
                ),
            ],
            'amount_paid' => ['required', 'integer', 'min:1'],
            'payment_date' => ['required', 'date'],
        ];
    }

    /**
     * Messages d’erreur personnalisés (optionnel mais recommandé).
     */
    public function messages(): array
    {
        return [
            'invoice_id.required' => 'La facture associée est obligatoire.',
            'invoice_id.exists' => 'La facture sélectionnée est invalide.',
            'wallet_id.required' => 'Le wallet est obligatoire.',
            'wallet_id.exists' => 'Le wallet sélectionné est invalide.',
            'amount_paid.required' => 'Le montant payé est obligatoire.',
            'payment_date.required' => 'La date du paiement est obligatoire.',
        ];
    }
}
