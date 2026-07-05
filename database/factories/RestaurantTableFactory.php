<?php

namespace Database\Factories;

use App\Enums\TableForme;
use App\Enums\TableStatut;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle RestaurantTable.
 *
 * Utilisée dans les tests et les seeders pour générer
 * des données cohérentes et réalistes.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RestaurantTable>
 */
class RestaurantTableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'zone_id'    => Zone::factory(),
            'numero'     => strtoupper($this->faker->unique()->bothify('T##')),
            'capacite'   => $this->faker->randomElement([2, 4, 4, 6, 8]),
            'forme'      => $this->faker->randomElement(TableForme::cases())->value,
            'position_x' => $this->faker->randomFloat(2, 5, 90),
            'position_y' => $this->faker->randomFloat(2, 5, 90),
            'statut'     => TableStatut::Libre->value,
            'active'     => true,
        ];
    }

    /**
     * État : table libre.
     */
    public function libre(): static
    {
        return $this->state(['statut' => TableStatut::Libre->value]);
    }

    /**
     * État : table réservée.
     */
    public function reservee(): static
    {
        return $this->state(['statut' => TableStatut::Reservee->value]);
    }

    /**
     * État : table occupée.
     */
    public function occupee(): static
    {
        return $this->state(['statut' => TableStatut::Occupee->value]);
    }

    /**
     * État : table hors service.
     */
    public function horsService(): static
    {
        return $this->state(['statut' => TableStatut::HorsService->value]);
    }

    /**
     * État : table inactive.
     */
    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
