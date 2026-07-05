<?php

namespace App\Models;

use App\Enums\ReservationStatut;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle Reservation — Skinny Model.
 *
 * @property int                 $id
 * @property int                 $table_id
 * @property int|null            $client_id
 * @property string              $nom_client
 * @property string              $telephone_client
 * @property int                 $nombre_personnes
 * @property \Carbon\Carbon      $date_debut
 * @property \Carbon\Carbon      $date_fin
 * @property ReservationStatut   $statut
 * @property string|null         $notes
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'client_id',
        'nom_client',
        'telephone_client',
        'nombre_personnes',
        'date_debut',
        'date_fin',
        'statut',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'statut'           => ReservationStatut::class,
            'date_debut'       => 'datetime',
            'date_fin'         => 'datetime',
            'nombre_personnes' => 'integer',
        ];
    }

    // =========================================================================
    // Relations
    // =========================================================================

    /**
     * La réservation concerne une table de restaurant.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    /**
     * La réservation peut être liée à un compte client (optionnel).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // =========================================================================
    // Scopes locaux
    // =========================================================================

    /**
     * Scope : réservations actives (non annulées, non terminées).
     */
    public function scopeActives($query)
    {
        return $query->whereNotIn('statut', [
            ReservationStatut::Annulee->value,
            ReservationStatut::Terminee->value,
            ReservationStatut::NonVenue->value,
        ]);
    }

    /**
     * Scope : réservations pour une date donnée.
     */
    public function scopePourDate($query, string $date)
    {
        return $query->whereDate('date_debut', $date);
    }

    /**
     * Scope : réservations qui se chevauchent avec un créneau donné.
     * Utile pour vérifier la disponibilité d'une table.
     */
    public function scopeChevauchant($query, string $debut, string $fin)
    {
        return $query->where('date_debut', '<', $fin)
                     ->where('date_fin', '>', $debut);
    }
}
