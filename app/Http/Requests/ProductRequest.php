<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette action.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation du formulaire produit.
     */
    public function rules(): array
    {
        // Récupère le tenant_id depuis l'utilisateur connecté (si disponible)
        $tenantId = auth()->user()->tenant_id ?? $this->tenant_id;

        return [
            'category_id' => ['required', 'uuid', Rule::exists('categories', 'id')->where('tenant_id', $tenantId)],
            'unit_id'     => ['required', 'uuid', 'exists:units,id'],
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')
                    ->where(fn($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($this->product?->id), // utile pour l’update
            ],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'integer', 'min:0'],
            'seuil_alert' => ['required', 'integer', 'min:0'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'La catégorie est obligatoire.',
            'unit_id.required' => 'L’unité est obligatoire.',
            'name.required' => 'Le nom du produit est obligatoire.',
            'name.unique' => 'Ce produit existe déjà pour votre entreprise.',
            'price.required' => 'Le prix est obligatoire.',
            'price.integer' => 'Le prix doit être un nombre entier.',
            'seuil_alert.required' => 'Le seuil d’alerte est obligatoire.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'Le format de l’image doit être jpg, jpeg ou png.',
        ];
    }
}
