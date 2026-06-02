<?php

// CORRECTION CRITIQUE : Ce fichier contenait par erreur le code du modèle Task.
// Il est remplacé par le vrai ProjectController avec le namespace correct.
// L'ancien fichier est conservé vide pour ne pas casser l'autoload, mais
// toute la logique est désormais dans app/Http/Controllers/Api/ProjectController.php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

// Ce fichier est le Controller de base dont héritent tous les autres controllers.
// NE PAS mettre de logique métier ici.
class ProjectController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}