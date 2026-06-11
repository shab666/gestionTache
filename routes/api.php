<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SearchController;

// CORRECTION : Import des controllers dans le bon namespace App\Http\Controllers\Api
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;


Route::get('/search', [SearchController::class, 'globalSearch']);

// Routes publiques (pas besoin d'être connecté)
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login',    [App\Http\Controllers\AuthController::class, 'login']);

// Toutes les routes à l'intérieur de ce groupe nécessitent un Token Sanctum valide
Route::middleware('auth:sanctum')->group(function () {

    // Récupérer les infos de l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Déconnexion
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);

    // CRUD complet sur les projets
    Route::apiResource('projects', ProjectController::class);

    // Rapport PDF pour un projet
    Route::get('/projects/{id}/report', [ProjectController::class, 'report']);

    // Routes pour les Tâches imbriquées dans les projets
    // URLs générées :
    // - GET    /api/projects/{project}/tasks
    // - POST   /api/projects/{project}/tasks
    // - GET    /api/projects/{project}/tasks/{task}
    // - PUT    /api/projects/{project}/tasks/{task}
    // - DELETE /api/projects/{project}/tasks/{task}
    Route::apiResource('projects.tasks', TaskController::class);

    // Changement de statut/colonne d'une tâche (Kanban move)
    Route::put('/tasks/{id}', [TaskController::class, 'move']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroyFlat']);
});
