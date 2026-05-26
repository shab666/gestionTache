<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. Inscription (Register)
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Génère le token de connexion pour l'application mobile
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur créé avec succès !',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // 2. Connexion (Login)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Vérifie si l'utilisateur existe et si le mot de passe correspond
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects.'
            ], 401);
        }

        // Génère le token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // 3. Déconnexion (Logout)
    public function logout(Request $request)
    {
        // Supprime le token actuel utilisé par l'application Android
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie !'
        ]);
    }
}