<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class ExchangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ici tu peux mettre la logique pour vérifier si l'utilisateur peut transférer
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'from_warehouse' => ['required', Rule::exists('warehouses', 'id')->where('tenant_id', auth()->user()->tenant_id)],
            'to_warehouse'   => ['required', Rule::exists('warehouses', 'id')->where('tenant_id', auth()->user()->tenant_id), 'different:from_warehouse'],
            'batch_id'       => ['required', 'array', 'min:1'],
            'batch_id.*'     => ['required', Rule::exists('batches', 'id')->where('tenant_id', auth()->user()->tenant_id)],
            'quantity'       => ['required', 'array', 'min:1'],
            'quantity.*'     => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Custom messages (optionnel)
     */
    public function messages(): array
    {
        return [
            'from_warehouse.required' => 'L\'entrepôt source est requis.',
            'to_warehouse.required'   => 'L\'entrepôt de destination est requis.',
            'to_warehouse.different'  => 'L\'entrepôt de destination doit être différent de l\'entrepôt source.',
            'batch_id.required'       => 'Vous devez sélectionner au moins un lot.',
            'quantity.required'       => 'La quantité est obligatoire pour chaque lot.',
        ];
    }
}
