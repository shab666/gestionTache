<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nom'       => ['required', 'string', 'max:100'],
            'prenom'    => ['required', 'string', 'max:100'],
            'email'     => ['nullable', 'email', 'max:150', 'unique:clients,email'],
            'telephone' => ['required', 'string', 'max:20'],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'      => 'Cette adresse email est déjà utilisée par un autre client.',
            'telephone.required'=> 'Le numéro de téléphone est obligatoire.',
        ];
    }
}
