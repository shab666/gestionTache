<?php

// CORRECTION CRITIQUE : Le fichier ProjectController.php contenait par erreur
// le code du modèle Task. Voici le vrai controller avec le bon namespace.
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // [GET] /api/projects -> Liste tous les projets
    public function index()
    {
        $projects = Project::with(['tasks', 'users'])->get();
        return response()->json($projects, 200);
    }

    // [POST] /api/projects -> Créer un nouveau projet
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::create($validated);

        return response()->json($project, 201);
    }

    // [GET] /api/projects/{project} -> Afficher un projet spécifique
    public function show(Project $project)
    {
        // Charge les relations imbriquées pour une réponse complète
        $project->load(['tasks.users', 'users']);
        return response()->json($project, 200);
    }

    // [PUT/PATCH] /api/projects/{project} -> Modifier un projet
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json($project, 200);
    }

    // [DELETE] /api/projects/{project} -> Supprimer un projet
    public function destroy(Project $project)
    {
        // Suppression en cascade : détacher d'abord les tâches liées
        $project->tasks()->each(function ($task) {
            if (method_exists($task, 'users')) {
                $task->users()->detach();
            }
            $task->delete();
        });

        $project->delete();

        return response()->json(['message' => 'Projet supprimé avec succès'], 200);
    }

    /**
     * [GET] /api/projects/{id}/report -> Générer un rapport PDF
     */
    public function report($id)
    {
        $project = Project::with(['tasks', 'users.roles'])->findOrFail($id);

        $totalTasksCount = $project->tasks->count();
        $completedTasksCount = $project->tasks->where('status', 'done')->count();
        $progress = $totalTasksCount > 0 ? round(($completedTasksCount / $totalTasksCount) * 100) : 0;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.project_report', [
            'project' => $project,
            'totalTasksCount' => $totalTasksCount,
            'completedTasksCount' => $completedTasksCount,
            'progress' => $progress
        ]);

        return $pdf->download("Rapport_Projet_{$project->id}.pdf");
    }
}
