<?php

namespace App\Enums;

enum TableStatut: string
{
    case Libre       = 'libre';
    case Reservee    = 'reservee';
    case Occupee     = 'occupee';
    case ANnettoyer  = 'a_nettoyer';
    case HorsService = 'hors_service';

    /**
     * Retourne le libellé humain de chaque statut.
     */
    public function label(): string
    {
        return match($this) {
            self::Libre       => 'Libre',
            self::Reservee    => 'Réservée',
            self::Occupee     => 'Occupée',
            self::ANnettoyer  => 'À nettoyer',
            self::HorsService => 'Hors service',
        };
    }

    /**
     * Retourne la classe CSS associée au statut (utile pour le frontend).
     */
    public function cssClass(): string
    {
        return match($this) {
            self::Libre       => 'status-libre',
            self::Reservee    => 'status-reservee',
            self::Occupee     => 'status-occupee',
            self::ANnettoyer  => 'status-nettoyer',
            self::HorsService => 'status-hors-service',
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
