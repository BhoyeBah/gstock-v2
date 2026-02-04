<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s\-\(\)]{7,30}$/'],
            'position'  => ['required', 'string', 'max:255'],
            'salary'    => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Le nom complet est obligatoire.',
            'salary.required' => 'Le salaire est obligatoire.',
            'full_name.string'   => 'Le nom complet doit être une chaîne de caractères.',
            'full_name.max'      => 'Le nom complet ne doit pas dépasser 255 caractères.',

            'phone.string'       => 'Le numéro de téléphone est invalide.',
            'phone.max'          => 'Le numéro de téléphone ne doit pas dépasser 30 caractères.',
            'phone.regex'        => 'Le format du numéro de téléphone est invalide (ex: +221 77 000 00 00).',

            'position.required'  => 'Le poste est obligatoire.',
            'position.string'    => 'Le poste doit être une chaîne de caractères.',
            'position.max'       => 'Le poste ne doit pas dépasser 255 caractères.',

            'salary.integer'     => 'Le salaire doit être un nombre entier.',
            'salary.min'         => 'Le salaire ne peut pas être négatif.',
        ];
    }
}
