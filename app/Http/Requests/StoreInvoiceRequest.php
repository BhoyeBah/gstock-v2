<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $invoiceId = $this->route('invoice')?->id;

        // Détection du type (client ou fournisseur)
        $type = $this->input('type');

        $rules = [
            'contact_id' => ['required', 'uuid', Rule::exists('contacts', 'id')->where('tenant_id', $tenantId)],
            'invoice_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('invoices')
                    ->ignore($invoiceId)
                    ->where(fn($query) => $query->where('tenant_id', $tenantId)
                        ->where('type', $this->input('type'))),
            ],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'type' => ['required', Rule::in(['client', 'supplier'])],

            // Lignes de facture
            'items' => ['required', 'array', 'min:1'],
            'items.*.warehouse_id' => ['required', 'uuid', Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],
            'items.*.product_id' => ['required', 'uuid', Rule::exists('products', 'id')->where('tenant_id', $tenantId)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.discount' => ['nullable', 'integer', 'min:0'],
        ];

        // ✅ Si la facture est de type fournisseur, on exige expiration_date
        if ($type === 'supplier') {
            $rules['items.*.expiration_date'] = [
                'nullable',
                'date',
                'after_or_equal:today', // la date d’expiration ne peut pas être passée
            ];
        } else {
            // ✅ Si c’est un client, la date d’expiration peut être absente mais doit être valide si présente
            $rules['items.*.expiration_date'] = ['nullable', 'date', 'after_or_equal:today'];
        }

        return $rules;
    }

    /**
     * Messages personnalisés.
     */
    public function messages(): array
    {
        return [
            'invoice_number.unique' => 'Ce numéro de facture existe déjà pour ce tenant.',
            'contact_id.required' => 'Le contact est requis.',
            'invoice_date.required' => 'La date de facture est obligatoire.',
            'due_date.required' => 'La date \'echeance de la facture est obligatoire.',
            'items.required' => 'La facture doit contenir au moins une ligne.',
            'items.*.warehouse_id.required' => 'Chaque ligne doit avoir un entrepôt.',
            'items.*.product_id.required' => 'Chaque ligne doit avoir un produit.',
            'items.*.quantity.required' => 'Chaque ligne doit avoir une quantité.',
            'items.*.unit_price.required' => 'Chaque ligne doit avoir un prix unitaire.',
            'items.*.expiration_date.required' => 'Chaque ligne doit avoir une date d’expiration (pour les fournisseurs).',
            'items.*.expiration_date.after_or_equal' => 'La date d’expiration doit être aujourd’hui ou ultérieure.',
        ];
    }
}
