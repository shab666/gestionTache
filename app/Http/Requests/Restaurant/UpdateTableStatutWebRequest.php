<?php

namespace App\Http\Requests\Restaurant;

use App\Enums\TableStatut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableStatutWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'statut.enum'     => 'Statut invalide. Valeurs acceptées : ' . implode(', ', TableStatut::values()),
        ];
    }
}
