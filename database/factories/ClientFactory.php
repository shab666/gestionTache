<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle Client.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom'       => $this->faker->lastName(),
            'prenom'    => $this->faker->firstName(),
            'email'     => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->phoneNumber(),
            'notes'     => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Client sans email (réservation anonyme).
     */
    public function sansEmail(): static
    {
        return $this->state(['email' => null]);
    }

    /**
     * Client VIP avec notes.
     */
    public function vip(): static
    {
        return $this->state([
            'notes' => 'Client VIP — ' . $this->faker->sentence(5),
        ]);
    }
}
