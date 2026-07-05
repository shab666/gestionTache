<?php

namespace App\Observers;

use App\Models\HistoriqueStatutTable;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\Auth;

/**
 * Observer du modèle RestaurantTable.
 *
 * RESPONSABILITÉ UNIQUE : Créer automatiquement un enregistrement
 * dans historique_statuts_tables à chaque fois que le statut d'une table change.
 *
 * AVANTAGE ARCHITECTURAL :
 * En plaçant cette logique dans un Observer et non dans les contrôleurs/services,
 * on garantit que l'historique est TOUJOURS créé, quelle que soit la source
 * du changement (API, console, job de queue, tests...).
 * C'est une application du principe DRY et de la Separation of Concerns.
 *
 * Enregistrement : AppServiceProvider::boot()
 */
class TableObserver
{
    /**
     * Déclenché après chaque sauvegarde réussie d'une RestaurantTable.
     *
     * On utilise 'updated' (et non 'saving') pour s'assurer que le changement
     * a bien été persisté en base avant de créer l'historique.
     */
    public function updated(RestaurantTable $table): void
    {
        if (! $table->wasChanged('statut')) {
            return;
        }

        $ancienStatut  = $table->getOriginal('statut');
        $nouveauStatut = $table->statut->value;

        // Récupère et réinitialise le contexte de transition stocké sur le modèle
        // Ces propriétés ne sont JAMAIS persistées en base de données.
        $context = $table->popTransitionContext();
        $userId  = $context['user_id'] ?? Auth::id();
        $raison  = $context['raison']  ?? null;

        HistoriqueStatutTable::create([
            'table_id'       => $table->id,
            'ancien_statut'  => $ancienStatut,
            'nouveau_statut' => $nouveauStatut,
            'modifie_par'    => $userId,
            'modifie_a'      => now(),
            'raison'         => $raison,
        ]);
    }

}
