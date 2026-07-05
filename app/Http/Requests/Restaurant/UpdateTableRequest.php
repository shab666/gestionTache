<?php

namespace App\Http\Requests\Restaurant;

use App\Enums\TableForme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $table = $this->route('table'); // Instance de RestaurantTable

        return [
            'zone_id'    => ['sometimes', 'required', 'integer', 'exists:zones,id'],
            'numero'     => [
                'sometimes',
                'required',
                'string',
                'max:10',
                Rule::unique('restaurant_tables')
                    ->where('zone_id', $this->zone_id ?? $table->zone_id)
                    ->ignore($table->id),
            ],
            'capacite'   => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
            'forme'      => ['sometimes', 'required', Rule::enum(TableForme::class)],
            'position_x' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'position_y' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'active'     => ['nullable', 'boolean'],
        ];
    }
}
