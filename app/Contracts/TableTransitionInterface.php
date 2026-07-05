<?php

namespace App\Contracts;

use App\Enums\TableStatut;
use App\Models\RestaurantTable;

/**
 * Interface définissant le contrat pour la gestion des transitions d'état des tables.
 *
 * En utilisant une Interface, on découple complètement les contrôleurs et services
 * de l'implémentation concrète. Cela permet :
 * - De mocker cette dépendance dans les tests unitaires
 * - De changer l'implémentation (ex: depuis DB, depuis config) sans toucher aux consommateurs
 */
interface TableTransitionInterface
{
    /**
     * Vérifie si une transition entre deux statuts est autorisée.
     *
     * @param  TableStatut  $from  Le statut actuel de la table
     * @param  TableStatut  $to    Le statut cible
     * @return bool                True si la transition est valide
     */
    public function canTransition(TableStatut $from, TableStatut $to): bool;

    /**
     * Exécute la transition d'état d'une table.
     *
     * Applique le nouveau statut sur le modèle et le persiste.
     * L'Observer TableObserver se chargera automatiquement de logger l'historique.
     *
     * @param  RestaurantTable  $table   La table à modifier
     * @param  TableStatut      $to      Le nouveau statut cible
     * @param  int|null         $userId  L'ID de l'utilisateur effectuant la modification
     * @param  string|null      $raison  La raison du changement (optionnel)
     * @return void
     *
     * @throws \App\Exceptions\InvalidStateTransitionException si la transition est interdite
     */
    public function transition(
        RestaurantTable $table,
        TableStatut     $to,
        ?int            $userId = null,
        ?string         $raison = null
    ): void;
}
