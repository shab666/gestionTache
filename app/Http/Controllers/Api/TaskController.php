<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Events\TaskMoved; // Importation essentielle pour le Jalon 2

class TaskController extends Controller
{
    // [GET] /api/projects/{project}/tasks -> Liste des tâches d'un projet
    public function index(Project $project)
    {
        $tasks = method_exists(Task::class, 'users')
            ? $project->tasks()->with('users')->get()
            : $project->tasks()->get();

        return response()->json($tasks, 200);
    }

    // [POST] /api/projects/{project}/tasks -> Créer une tâche dans ce projet
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'required|in:todo,in_progress,done',
            'priority'         => 'required|in:low,medium,high',
            'due_date'         => 'required|date',
            'assigned_users'   => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $task = $project->tasks()->create($validated);

        if ($request->has('assigned_users') && method_exists($task, 'users')) {
            $task->users()->sync($request->input('assigned_users'));
        }

        return response()->json(
            method_exists($task, 'users') ? $task->load('users') : $task,
            201
        );
    }

    // [GET] /api/projects/{project}/tasks/{task} -> Afficher le détail d'une tâche
    public function show(Project $project, Task $task)
    {
        return response()->json(
            method_exists($task, 'users') ? $task->load('users') : $task,
            200
        );
    }

    // [PUT/PATCH] /api/projects/{project}/tasks/{task} -> Modifier une tâche
    public function update(Request $request, Project $project, Task $task)
    {
        $validated = $request->validate([
            'title'            => 'sometimes|required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'sometimes|required|in:todo,in_progress,done',
            'priority'         => 'sometimes|required|in:low,medium,high',
            'due_date'         => 'sometimes|required|date',
            'assigned_users'   => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $task->update($validated);

        if ($request->has('assigned_users') && method_exists($task, 'users')) {
            $task->users()->sync($request->input('assigned_users'));
        }

        return response()->json(
            method_exists($task, 'users') ? $task->load('users') : $task,
            200
        );
    }

    // [DELETE] /api/projects/{project}/tasks/{task} -> Supprimer une tâche
    public function destroy(Project $project, Task $task)
    {
        if (method_exists($task, 'users')) {
            $task->users()->detach();
        }
        $task->delete();

        return response()->json(['message' => 'Tâche supprimée avec succès'], 200);
    }

    // ----------------------------------------------------------------
    // MÉTHODE CORRIGÉE JALON 2 : [PUT] /api/tasks/{id}/move
    // ----------------------------------------------------------------
    public function move(Request $request, $id)
    {
        // CORRECTION : On ne demande QUE le status ici ! Plus d'erreur 422 sur le titre.
        $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $task = Task::findOrFail($id);

        $task->update([
            'status' => $request->input('status'),
        ]);

        // Envoi du signal temps réel à Pusher
        broadcast(new TaskMoved($task));

        return response()->json(
            method_exists($task, 'users') ? $task->load('users') : $task,
            200
        );
    }
}