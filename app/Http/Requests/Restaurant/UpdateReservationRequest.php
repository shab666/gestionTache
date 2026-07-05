<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nom_client'       => ['sometimes', 'required', 'string', 'max:150'],
            'telephone_client' => ['sometimes', 'required', 'string', 'max:20'],
            'nombre_personnes' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'date_debut'       => ['sometimes', 'required', 'date'],
            'date_fin'         => ['sometimes', 'required', 'date', 'after:date_debut'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
