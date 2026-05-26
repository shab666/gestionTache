<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    // 1. Liste des projets de l'utilisateur connecté
    public function index()
    {
        // On récupère les projets liés à l'utilisateur connecté via la table pivot
        $projects = Auth::user()->projects; 
        
        return response()->json($projects, 200);
    }

    // 2. Création d'un nouveau projet
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Création du projet
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // On lie automatiquement le projet à l'utilisateur connecté dans la table pivot
        Auth::user()->projects()->attach($project->id);

        return response()->json([
            'message' => 'Projet créé avec succès !',
            'project' => $project
        ], 201);
    }
}