<?php

namespace App\Services;

use App\Contracts\ReservationServiceInterface;
use App\Contracts\TableTransitionInterface;
use App\Enums\ReservationStatut;
use App\Enums\TableStatut;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion des réservations de restaurant.
 *
 * Principes SOLID respectés :
 * - SRP   : Ce service gère UNIQUEMENT le cycle de vie des réservations
 * - OCP   : Extensible sans modification (ex: ajout de notifications via Observer)
 * - LSP   : Implémente fidèlement ReservationServiceInterface
 * - ISP   : L'interface est minimale et cohérente
 * - DIP   : Dépend de TableTransitionInterface (abstraction), pas du service concret
 *
 * INJECTION DE DÉPENDANCES :
 * TableTransitionInterface est injectée via le constructeur.
 * Jamais de "new TableTransitionService()" ici.
 * Cela permet de mocker facilement cette dépendance dans les tests unitaires.
 */
class ReservationService implements ReservationServiceInterface
{
    public function __construct(
        private readonly TableTransitionInterface $tableTransition
    ) {}

    /**
     * {@inheritDoc}
     *
     * Utilise DB::transaction() pour garantir l'atomicité :
     * La réservation ET le changement de statut de la table se font ensemble.
     * Si l'un échoue, tout est annulé (rollback automatique).
     */
    public function creerReservation(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            // Étape 1 : Créer l'enregistrement de réservation
            $reservation = Reservation::create([
                'table_id'         => $data['table_id'],
                'client_id'        => $data['client_id'] ?? null,
                'nom_client'       => $data['nom_client'],
                'telephone_client' => $data['telephone_client'],
                'nombre_personnes' => $data['nombre_personnes'],
                'date_debut'       => $data['date_debut'],
                'date_fin'         => $data['date_fin'],
                'statut'           => ReservationStatut::EnAttente,
                'notes'            => $data['notes'] ?? null,
            ]);

            // Étape 2 : Changer le statut de la table en 'reservee'
            // L'Observer créera automatiquement l'historique.
            // Si la transition échoue (table hors service par ex.), la transaction rollback.
            $this->tableTransition->transition(
                table:  $reservation->table,
                to:     TableStatut::Reservee,
                userId: $data['_user_id'] ?? null,
                raison: "Réservation #{$reservation->id} créée pour {$data['nom_client']}"
            );

            return $reservation->fresh(['table', 'client']);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function confirmerReservation(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            // Changer le statut de la réservation
            $reservation->statut = ReservationStatut::Confirmee;
            $reservation->save();

            // Passer la table en 'occupee' (le client est arrivé)
            $this->tableTransition->transition(
                table:  $reservation->table,
                to:     TableStatut::Occupee,
                raison: "Réservation #{$reservation->id} confirmée — client arrivé"
            );

            return $reservation->fresh();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function annulerReservation(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            $reservation->statut = ReservationStatut::Annulee;
            $reservation->save();

            // Remettre la table libre si elle était encore en statut 'reservee'
            if ($reservation->table->statut === TableStatut::Reservee) {
                $this->tableTransition->transition(
                    table:  $reservation->table,
                    to:     TableStatut::Libre,
                    raison: "Réservation #{$reservation->id} annulée"
                );
            }

            return $reservation->fresh();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function terminerReservation(Reservation $reservation): Reservation
    {
        return DB::transaction(function () use ($reservation) {
            $reservation->statut = ReservationStatut::Terminee;
            $reservation->save();

            // Passer la table en 'a_nettoyer' (les clients sont partis)
            $this->tableTransition->transition(
                table:  $reservation->table,
                to:     TableStatut::ANnettoyer,
                raison: "Réservation #{$reservation->id} terminée — table à nettoyer"
            );

            return $reservation->fresh();
        });
    }
}
