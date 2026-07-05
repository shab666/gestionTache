<?php

namespace Database\Factories;

use App\Enums\ReservationStatut;
use App\Models\Client;
use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle Reservation.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $debut = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $fin   = (clone $debut)->modify('+2 hours');

        $prenom = $this->faker->firstName();
        $nom    = $this->faker->lastName();

        return [
            'table_id'         => RestaurantTable::factory(),
            'client_id'        => null,
            'nom_client'       => "{$prenom} {$nom}",
            'telephone_client' => $this->faker->phoneNumber(),
            'nombre_personnes' => $this->faker->numberBetween(1, 8),
            'date_debut'       => $debut,
            'date_fin'         => $fin,
            'statut'           => ReservationStatut::EnAttente->value,
            'notes'            => $this->faker->optional(0.4)->sentence(),
        ];
    }

    /**
     * Réservation avec un client lié.
     */
    public function avecClient(): static
    {
        return $this->state(['client_id' => Client::factory()]);
    }

    /**
     * Réservation confirmée.
     */
    public function confirmee(): static
    {
        return $this->state(['statut' => ReservationStatut::Confirmee->value]);
    }

    /**
     * Réservation annulée.
     */
    public function annulee(): static
    {
        return $this->state(['statut' => ReservationStatut::Annulee->value]);
    }

    /**
     * Réservation terminée.
     */
    public function terminee(): static
    {
        return $this->state(['statut' => ReservationStatut::Terminee->value]);
    }

    /**
     * Réservation dans le passé.
     */
    public function passee(): static
    {
        $debut = $this->faker->dateTimeBetween('-30 days', '-1 day');
        $fin   = (clone $debut)->modify('+2 hours');

        return $this->state([
            'date_debut' => $debut,
            'date_fin'   => $fin,
        ]);
    }
}
