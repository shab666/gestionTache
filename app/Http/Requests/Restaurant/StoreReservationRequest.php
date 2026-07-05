<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation stricte pour la création d'une réservation.
 *
 * Ce FormRequest valide :
 * 1. Le format et la présence des données (sa responsabilité)
 * 2. L'existence des entités référencées (table, client)
 * 3. La cohérence temporelle (date_fin > date_debut)
 *
 * Ce qu'il ne valide PAS (responsabilité du service) :
 * - La disponibilité réelle de la table à ce créneau
 * - La transition de statut valide de la table
 */
class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'table_id'         => ['required', 'integer', 'exists:restaurant_tables,id'],
            'client_id'        => ['nullable', 'integer', 'exists:clients,id'],
            'nom_client'       => ['required', 'string', 'max:150'],
            'telephone_client' => ['required', 'string', 'max:20'],
            'nombre_personnes' => ['required', 'integer', 'min:1', 'max:100'],
            'date_debut'       => ['required', 'date', 'after:now'],
            'date_fin'         => ['required', 'date', 'after:date_debut'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'table_id.exists'         => 'La table sélectionnée n\'existe pas.',
            'client_id.exists'        => 'Le client sélectionné n\'existe pas.',
            'date_debut.after'        => 'La date de début doit être dans le futur.',
            'date_fin.after'          => 'La date de fin doit être postérieure à la date de début.',
            'nombre_personnes.min'    => 'Le nombre de personnes doit être d\'au moins 1.',
            'telephone_client.required' => 'Le téléphone de contact est obligatoire.',
        ];
    }
}
