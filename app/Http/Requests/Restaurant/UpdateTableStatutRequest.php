<?php

namespace App\Http\Requests\Restaurant;

use App\Enums\TableStatut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation du changement de statut d'une table.
 *
 * Ce FormRequest ne valide QUE le nouveau statut demandé et la raison optionnelle.
 * La validation métier (est-ce que la transition est autorisée ?) est déléguée
 * au TableTransitionService — c'est sa responsabilité, pas celle du FormRequest.
 */
class UpdateTableStatutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', Rule::enum(TableStatut::class)],
            'raison' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'statut.required' => 'Le nouveau statut est obligatoire.',
            'statut.enum'     => 'Statut invalide. Valeurs acceptées : '
                . implode(', ', TableStatut::values()),
        ];
    }
}
