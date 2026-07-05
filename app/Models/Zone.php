<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle Zone — Skinny Model.
 *
 * Responsabilités EXCLUSIVES de ce modèle :
 * - Définition des champs fillable
 * - Relations Eloquent
 * - Scopes locaux
 *
 * Toute logique métier doit être dans les Services.
 *
 * @property int    $id
 * @property string $nom
 * @property string|null $description
 */
class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    /**
     * Une zone contient plusieurs tables de restaurant.
     */
    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class, 'zone_id');
    }

    // =========================================================================
    // Scopes locaux
    // =========================================================================

    /**
     * Scope : ne retourner que les zones ayant au moins une table active.
     */
    public function scopeAvecTablesActives($query)
    {
        return $query->whereHas('tables', fn ($q) => $q->where('active', true));
    }
}
