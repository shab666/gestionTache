<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')->id;

        return [
            'nom'       => ['sometimes', 'required', 'string', 'max:100'],
            'prenom'    => ['sometimes', 'required', 'string', 'max:100'],
            'email'     => ['nullable', 'email', 'max:150', "unique:clients,email,{$clientId}"],
            'telephone' => ['sometimes', 'required', 'string', 'max:20'],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ];
    }
}
