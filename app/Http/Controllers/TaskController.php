<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // 1. Liste toutes les tâches d'un projet donné
    public function index($projectId)
    {
        // On vérifie que le projet existe
        $project = Project::findOrFail($projectId);
        
        return response()->json($project->tasks, 200);
    }

    // 2. Ajoute une tâche dans un projet
    public function store(Request $request, $projectId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed'
        ]);

        Project::findOrFail($projectId);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'pending',
            'project_id' => $projectId
        ]);

        return response()->json([
            'message' => 'Tâche ajoutée avec succès !',
            'task' => $task
        ], 201);
    }
}