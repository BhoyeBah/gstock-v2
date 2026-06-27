<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette action.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation du formulaire entrepôt.
     */
    public function rules(): array
    {
        // Récupère le tenant_id depuis l'utilisateur connecté (ou du corps de la requête)
        $tenantId = auth()->user()->tenant_id ?? $this->tenant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('warehouses')
                    ->where(fn($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($this->warehouse?->id), // utile lors d'une mise à jour
            ],
            'address' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'uuid', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l’entrepôt est obligatoire.',
            'name.unique' => 'Cet entrepôt existe déjà pour votre entreprise.',
            'name.max' => 'Le nom de l’entrepôt ne doit pas dépasser 100 caractères.',
            'manager_id.uuid' => 'Le responsable doit être un identifiant valide.',
            'manager_id.exists' => 'Le responsable sélectionné est invalide.',
        ];
    }
}
