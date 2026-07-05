<?php

namespace App\Enums;

enum TableForme: string
{
    case Rond      = 'rond';
    case Carre     = 'carre';
    case Rectangle = 'rectangle';
    case Ovale     = 'ovale';

    /**
     * Retourne le libellé humain de chaque forme.
     */
    public function label(): string
    {
        return match($this) {
            self::Rond      => 'Ronde',
            self::Carre     => 'Carrée',
            self::Rectangle => 'Rectangulaire',
            self::Ovale     => 'Ovale',
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
