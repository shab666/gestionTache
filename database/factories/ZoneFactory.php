<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle Zone.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zone>
 */
class ZoneFactory extends Factory
{
    /**
     * Noms de zones réalistes pour un restaurant.
     */
    private static array $zones = [
        'Salle Principale',
        'Terrasse',
        'Bar',
        'Salon VIP',
        'Espace Lounge',
        'Mezzanine',
        'Jardin',
        'Salle Privatisable',
    ];

    public function definition(): array
    {
        return [
            'nom'         => $this->faker->unique()->randomElement(self::$zones),
            'description' => $this->faker->optional(0.7)->sentence(10),
        ];
    }

    /**
     * Zone sans description.
     */
    public function sansDescription(): static
    {
        return $this->state(['description' => null]);
    }
}
