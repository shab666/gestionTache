<?php

namespace App\Models;

use App\Enums\TableForme;
use App\Enums\TableStatut;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle RestaurantTable — Skinny Model.
 *
 * Nom de classe : RestaurantTable (et non "Table" pour éviter le conflit
 * avec la façade Illuminate\Database\Schema\Blueprint\Table).
 * La table SQL correspondante est déclarée explicitement : 'restaurant_tables'.
 *
 * @property int          $id
 * @property int          $zone_id
 * @property string       $numero
 * @property int          $capacite
 * @property TableForme   $forme
 * @property float        $position_x
 * @property float        $position_y
 * @property TableStatut  $statut
 * @property bool         $active
 */
class RestaurantTable extends Model
{
    use HasFactory;

    /**
     * Nom explicite de la table SQL pour éviter le conflit avec le mot réservé SQL.
     */
    protected $table = 'restaurant_tables';

    /**
     * Valeurs par défaut applicables côté application pour garantir un état cohérent
     * même avant la lecture depuis la base de données.
     */
    protected $attributes = [
        'statut' => 'libre',
        'active' => true,
    ];

    /**
     * Contexte de transition d'état (non persisté en base).
     * Utilisé par TableTransitionService pour passer des métadonnées à TableObserver.
     */
    protected array $transitionContext = [
        'user_id' => null,
        'raison'  => null,
    ];
    protected $fillable = [
        'zone_id',
        'numero',
        'capacite',
        'forme',
        'position_x',
        'position_y',
        'statut',
        'active',
    ];

    /**
     * Casts automatiques : Laravel convertit les valeurs en Enum PHP 8 natif.
     * Cela garantit le typage fort partout dans l'application.
     */
    protected function casts(): array
    {
        return [
            'forme'      => TableForme::class,
            'statut'     => TableStatut::class,
            'active'     => 'boolean',
            'position_x' => 'decimal:2',
            'position_y' => 'decimal:2',
        ];
    }

    // =========================================================================
    // Gestion du contexte de transition (non-Eloquent)
    // =========================================================================

    /**
     * Définit le contexte de transition pour que l'Observer puisse l'utiliser.
     * Ces données ne sont jamais persistées en base de données.
     */
    public function setTransitionContext(?int $userId, ?string $raison): void
    {
        $this->transitionContext = [
            'user_id' => $userId,
            'raison'  => $raison,
        ];
    }

    /**
     * Récupère le contexte de transition et le réinitialise.
     */
    public function popTransitionContext(): array
    {
        $context = $this->transitionContext;
        $this->transitionContext = ['user_id' => null, 'raison' => null];
        return $context;
    }

    // =========================================================================
    // Relations
    // =========================================================================

    /**
     * La table appartient à une zone.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    /**
     * Une table peut avoir plusieurs réservations dans le temps.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_id');
    }

    /**
     * Une table a un historique complet de ses changements de statut.
     * Alimenté automatiquement par TableObserver.
     */
    public function historiqueStatuts(): HasMany
    {
        return $this->hasMany(HistoriqueStatutTable::class, 'table_id');
    }

    // =========================================================================
    // Scopes locaux
    // =========================================================================

    /**
     * Scope : uniquement les tables actives.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope : uniquement les tables libres.
     */
    public function scopeLibres($query)
    {
        return $query->where('statut', TableStatut::Libre);
    }

    /**
     * Scope : tables d'une zone spécifique.
     */
    public function scopeDansZone($query, int $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Scope : tables ayant assez de capacité.
     */
    public function scopeCapaciteMin($query, int $personnes)
    {
        return $query->where('capacite', '>=', $personnes);
    }
}
