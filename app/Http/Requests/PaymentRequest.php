<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'invoice_id' => ['required', 'uuid', 'exists:invoices,id'],
            'amount_paid' => ['required', 'integer', 'min:0'],
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
            'tenant_id.required' => 'Le locataire est obligatoire.',
            'tenant_id.exists' => 'Le locataire sélectionné est invalide.',
            'amount_paid.required' => 'Le montant payé est obligatoire.',
            'payment_date.required' => 'La date du paiement est obligatoire.',
        ];
    }
}
