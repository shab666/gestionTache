<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class SearchController extends Controller
{
    /**
     * Recherche globale sur les projets, tâches et membres.
     * Route: /api/search?q=mot-cle
     */
    public function globalSearch(Request $request)
    {
        // 1. Récupérer le mot-clé 'q'
        $query = $request->input('q');

        // Si le mot-clé est vide ou trop court, on renvoie des tableaux vides
        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'projects' => [],
                'tasks'    => [],
                'members'  => []
            ], 200);
        }

        // 2. Rechercher dans les Projets (par nom ou description)
        $projects = Project::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->get();

        // 3. Rechercher dans les Tâches (par titre ou description)
        $tasks = Task::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->get();

        // 4. Rechercher dans les Membres / Utilisateurs (par nom ou email)
        $members = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->get();

        // 5. Retourner la réponse JSON structurée pour Android
        return response()->json([
            'projects' => $projects,
            'tasks'    => $tasks,
            'members'  => $members
        ], 200);
    }
}