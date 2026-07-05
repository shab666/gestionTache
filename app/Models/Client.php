<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle Client — Skinny Model.
 *
 * @property int         $id
 * @property string      $nom
 * @property string      $prenom
 * @property string|null $email
 * @property string      $telephone
 * @property string|null $notes
 */
class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'notes',
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    /**
     * Un client peut avoir plusieurs réservations.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'client_id');
    }

    // =========================================================================
    // Scopes locaux
    // =========================================================================

    /**
     * Scope : recherche par nom ou prénom.
     */
    public function scopeRecherche($query, string $terme)
    {
        return $query->where(function ($q) use ($terme) {
            $q->where('nom', 'like', "%{$terme}%")
              ->orWhere('prenom', 'like', "%{$terme}%")
              ->orWhere('email', 'like', "%{$terme}%")
              ->orWhere('telephone', 'like', "%{$terme}%");
        });
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Accesseur : nom complet du client.
     */
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
