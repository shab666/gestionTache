<?php

namespace App\Services;

use App\Contracts\TableTransitionInterface;
use App\Enums\TableStatut;
use App\Exceptions\InvalidStateTransitionException;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\Event;

/**
 * Service de gestion des transitions d'état des tables de restaurant.
 *
 * Implémente le pattern State Machine via une matrice de transitions autorisées.
 * Ce service est la SEULE source de vérité pour les règles de transition.
 *
 * Principes respectés :
 * - SRP : Ce service a une seule responsabilité (valider/exécuter les transitions)
 * - OCP : Ajouter un état ne demande que d'étendre la matrice, pas de modifier le code existant
 * - DIP : Les consommateurs dépendent de TableTransitionInterface, pas de cette classe concrète
 */
class TableTransitionService implements TableTransitionInterface
{
    /**
     * Matrice des transitions autorisées.
     *
     * Format : [TableStatut $from => [TableStatut $to, ...]]
     *
     * Règles métier encodées :
     * - libre       → reservee (une réservation est créée), hors_service
     * - reservee    → occupee (le client arrive), libre (annulation), hors_service
     * - occupee     → a_nettoyer (les clients partent), hors_service
     * - a_nettoyer  → libre (nettoyage terminé), hors_service
     * - hors_service→ libre (remise en service)
     *
     * Transition INTERDITE exemplaire :
     * - libre → a_nettoyer (une table ne peut pas être sale sans avoir été occupée)
     */
    private const TRANSITIONS_AUTORISEES = [
        TableStatut::Libre->value => [
            TableStatut::Reservee->value,
            TableStatut::HorsService->value,
        ],
        TableStatut::Reservee->value => [
            TableStatut::Occupee->value,
            TableStatut::Libre->value,
            TableStatut::HorsService->value,
        ],
        TableStatut::Occupee->value => [
            TableStatut::ANnettoyer->value,
            TableStatut::HorsService->value,
        ],
        TableStatut::ANnettoyer->value => [
            TableStatut::Libre->value,
            TableStatut::HorsService->value,
        ],
        TableStatut::HorsService->value => [
            TableStatut::Libre->value,
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function canTransition(TableStatut $from, TableStatut $to): bool
    {
        $allowed = self::TRANSITIONS_AUTORISEES[$from->value] ?? [];

        return in_array($to->value, $allowed, strict: true);
    }

    /**
     * {@inheritDoc}
     *
     * Note : Ce service NE crée PAS l'historique. C'est le rôle exclusif de
     * TableObserver::updated(). Cette séparation évite la duplication et respecte DRY.
     */
    public function transition(
        RestaurantTable $table,
        TableStatut     $to,
        ?int            $userId = null,
        ?string         $raison = null
    ): void {
        $from = $table->statut;

        if (! $this->canTransition($from, $to)) {
            throw new InvalidStateTransitionException($from, $to);
        }

        // On stocke le contexte dans des propriétés NON-Eloquent du modèle
        // pour que l'Observer puisse y accéder sans persister ces valeurs en DB.
        // Ces propriétés sont déclarées dans RestaurantTable::$transitionContext.
        $table->setTransitionContext($userId, $raison);

        $table->statut = $to;
        $table->save();
        // → Déclenche RestaurantTable::updated() → TableObserver::updated() automatiquement
    }
}
