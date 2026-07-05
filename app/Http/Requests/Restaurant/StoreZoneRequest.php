<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation de la création d'une zone.
 * L'autorisation et la validation sont séparées du contrôleur (SoC).
 */
class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seuls les utilisateurs authentifiés peuvent créer des zones
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nom'         => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la zone est obligatoire.',
            'nom.max'      => 'Le nom de la zone ne peut pas dépasser 100 caractères.',
        ];
    }
}
