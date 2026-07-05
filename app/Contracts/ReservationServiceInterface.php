<?php

namespace App\Contracts;

use App\Models\Reservation;

/**
 * Interface définissant le contrat pour la gestion des réservations.
 *
 * Ce découplage par interface permet :
 * - L'injection de dépendances dans les contrôleurs (pas de 'new ReservationService()')
 * - Le mocking complet du service dans les tests unitaires
 * - Le remplacement futur de l'implémentation sans toucher aux contrôleurs
 */
interface ReservationServiceInterface
{
    /**
     * Crée une réservation de façon atomique via DB::transaction.
     *
     * Garantit que la création de la réservation ET le changement de statut
     * de la table se font ensemble, ou pas du tout.
     *
     * @param  array  $data  Données validées de la réservation
     * @return Reservation   La réservation nouvellement créée
     *
     * @throws \App\Exceptions\InvalidStateTransitionException si la table n'est pas libre
     * @throws \Throwable en cas d'erreur de transaction
     */
    public function creerReservation(array $data): Reservation;

    /**
     * Confirme une réservation (passe son statut à 'confirmee').
     * Met également la table en statut 'occupee'.
     *
     * @param  Reservation  $reservation
     * @return Reservation
     */
    public function confirmerReservation(Reservation $reservation): Reservation;

    /**
     * Annule une réservation et remet la table en statut 'libre'.
     *
     * @param  Reservation  $reservation
     * @return Reservation
     */
    public function annulerReservation(Reservation $reservation): Reservation;

    /**
     * Termine une réservation et passe la table en statut 'a_nettoyer'.
     *
     * @param  Reservation  $reservation
     * @return Reservation
     */
    public function terminerReservation(Reservation $reservation): Reservation;
}
