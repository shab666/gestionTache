<?php

namespace App\Enums;

enum ReservationStatut: string
{
    case EnAttente = 'en_attente';
    case Confirmee = 'confirmee';
    case Terminee  = 'terminee';
    case Annulee   = 'annulee';
    case NonVenue  = 'non_venue';

    /**
     * Retourne le libellé humain de chaque statut.
     */
    public function label(): string
    {
        return match($this) {
            self::EnAttente => 'En attente',
            self::Confirmee => 'Confirmée',
            self::Terminee  => 'Terminée',
            self::Annulee   => 'Annulée',
            self::NonVenue  => 'Non venue',
        };
    }

    /**
     * Retourne la couleur badge associée.
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::EnAttente => 'warning',
            self::Confirmee => 'success',
            self::Terminee  => 'info',
            self::Annulee   => 'danger',
            self::NonVenue  => 'secondary',
        };
    }

    /**
     * Retourne toutes les valeurs sous forme de tableau de chaînes.
     * Utile pour les règles de validation.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
