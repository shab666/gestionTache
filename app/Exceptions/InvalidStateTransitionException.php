<?php

namespace App\Exceptions;

use App\Enums\TableStatut;
use RuntimeException;

/**
 * Exception métier déclenchée quand une transition d'état invalide est tentée.
 *
 * En utilisant une exception dédiée (et non une HttpException générique),
 * on respecte le principe SRP : la gestion des erreurs métier est séparée
 * de la couche HTTP. Le contrôleur l'attrape et retourne une réponse 422.
 */
class InvalidStateTransitionException extends RuntimeException
{
    public function __construct(
        private readonly TableStatut $from,
        private readonly TableStatut $to,
    ) {
        parent::__construct(
            "Transition interdite : impossible de passer de l'état [{$from->label()}] à [{$to->label()}]."
        );
    }

    /**
     * Retourne le statut source de la transition échouée.
     */
    public function getFromStatut(): TableStatut
    {
        return $this->from;
    }

    /**
     * Retourne le statut cible de la transition échouée.
     */
    public function getToStatut(): TableStatut
    {
        return $this->to;
    }
}
