<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // tous les utilisateurs autorisés
    }

    public function rules(): array
    {
        return [
            'batch_id' => ['required', 'exists:batches,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'batch_id.required' => 'Le lot est requis.',
            'batch_id.exists'   => 'Le lot sélectionné est invalide.',
            'quantity.required' => 'La quantité est requise.',
            'quantity.integer'  => 'La quantité doit être un nombre entier.',
            'quantity.min'      => 'La quantité doit être au moins 1.',
        ];
    }
}
