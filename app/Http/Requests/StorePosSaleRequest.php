<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePosSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;

        return [
            'warehouse_id' => ['required', 'uuid', Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],

            'contact_id' => ['nullable', 'uuid', Rule::exists('contacts', 'id')
                ->where('tenant_id', $tenantId)
                ->where('type', 'client')],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', Rule::exists('products', 'id')->where('tenant_id', $tenantId)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.discount' => ['nullable', 'integer', 'min:0'],

            'payments' => ['nullable', 'array'],
            'payments.*.wallet_id' => ['required_with:payments.*.amount', 'uuid', Rule::exists('wallets', 'id')->where('tenant_id', $tenantId)],
            'payments.*.amount' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Vous devez choisir un entrepôt.',
            'warehouse_id.exists' => "L'entrepôt sélectionné est invalide.",
            'contact_id.exists' => 'Le client sélectionné est invalide.',
            'items.required' => 'La vente doit contenir au moins un article.',
            'items.min' => 'La vente doit contenir au moins un article.',
            'items.*.product_id.required' => 'Chaque ligne doit référencer un produit.',
            'items.*.product_id.exists' => 'Un produit sélectionné est invalide.',
            'items.*.quantity.min' => 'La quantité doit être au moins 1.',
            'payments.*.wallet_id.exists' => 'Un moyen de paiement sélectionné est invalide.',
        ];
    }
}
