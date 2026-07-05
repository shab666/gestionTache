<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle HistoriqueStatutTable — Skinny Model (lecture seule).
 *
 * Ce modèle est EXCLUSIVEMENT alimenté par TableObserver.
 * Il ne doit JAMAIS être créé directement depuis un contrôleur ou un service.
 * C'est le tableau de bord de l'audit trail complet d'une table.
 *
 * @property int         $id
 * @property int         $table_id
 * @property string      $ancien_statut
 * @property string      $nouveau_statut
 * @property int|null    $modifie_par
 * @property \Carbon\Carbon $modifie_a
 * @property string|null $raison
 */
class HistoriqueStatutTable extends Model
{
    /**
     * Pas de timestamps automatiques : on utilise modifie_a à la place.
     */
    public $timestamps = false;

    protected $table = 'historique_statuts_tables';

    protected $fillable = [
        'table_id',
        'ancien_statut',
        'nouveau_statut',
        'modifie_par',
        'modifie_a',
        'raison',
    ];

    protected function casts(): array
    {
        return [
            'modifie_a' => 'datetime',
        ];
    }

    // =========================================================================
    // Relations
    // =========================================================================

    /**
     * L'entrée d'historique appartient à une table de restaurant.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    /**
     * L'entrée d'historique référence l'utilisateur qui a fait le changement.
     */
    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifie_par');
    }
}
