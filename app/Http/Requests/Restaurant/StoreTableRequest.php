<?php

namespace App\Http\Requests\Restaurant;

use App\Enums\TableForme;
use App\Enums\TableStatut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation stricte pour la création d'une table de restaurant.
 *
 * La validation des Enums PHP 8 utilise Rule::enum() pour garantir
 * que seules les valeurs définies dans l'Enum sont acceptées.
 */
class StoreTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'zone_id'    => ['required', 'integer', 'exists:zones,id'],
            'numero'     => [
                'required',
                'string',
                'max:10',
                // Unicité du numéro dans la zone
                Rule::unique('restaurant_tables')->where('zone_id', $this->zone_id),
            ],
            'capacite'   => ['required', 'integer', 'min:1', 'max:50'],
            'forme'      => ['required', Rule::enum(TableForme::class)],
            'position_x' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'position_y' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'statut'     => ['nullable', Rule::enum(TableStatut::class)],
            'active'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'zone_id.exists'   => 'La zone sélectionnée n\'existe pas.',
            'numero.unique'    => 'Ce numéro de table existe déjà dans cette zone.',
            'capacite.min'     => 'La capacité minimale est de 1 personne.',
            'forme.enum'       => 'La forme doit être : rond, carré, rectangle ou ovale.',
        ];
    }
}
