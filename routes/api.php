<?php

use App\Http\Controllers\Api\Restaurant\ClientController;
use App\Http\Controllers\Api\Restaurant\HistoriqueStatutController;
use App\Http\Controllers\Api\Restaurant\ReservationController;
use App\Http\Controllers\Api\Restaurant\TableController;
use App\Http\Controllers\Api\Restaurant\ZoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    // =========================================================================
    // MODULE RESTAURANT — Gestion du cycle de vie des tables & réservations
    // =========================================================================
    Route::prefix('restaurant')->name('restaurant.')->group(function () {

        // --- Zones ---
        // GET    /api/restaurant/zones
        // POST   /api/restaurant/zones
        // GET    /api/restaurant/zones/{zone}
        // PUT    /api/restaurant/zones/{zone}
        // DELETE /api/restaurant/zones/{zone}
        Route::apiResource('zones', ZoneController::class);

        // --- Tables de restaurant ---
        // GET    /api/restaurant/tables
        // POST   /api/restaurant/tables
        // GET    /api/restaurant/tables/{table}
        // PUT    /api/restaurant/tables/{table}
        // DELETE /api/restaurant/tables/{table}
        Route::apiResource('tables', TableController::class);

        // PATCH  /api/restaurant/tables/{table}/statut  ← State Machine
        Route::patch('tables/{table}/statut', [TableController::class, 'updateStatut'])
             ->name('tables.statut');

        // GET    /api/restaurant/tables/{table}/historique  ← Audit trail par table
        Route::get('tables/{table}/historique', [HistoriqueStatutController::class, 'index'])
             ->name('tables.historique');

        // GET    /api/restaurant/historique  ← Vue globale admin
        Route::get('historique', [HistoriqueStatutController::class, 'global'])
             ->name('historique.global');

        // --- Clients ---
        // GET    /api/restaurant/clients?q=terme
        // POST   /api/restaurant/clients
        // GET    /api/restaurant/clients/{client}
        // PUT    /api/restaurant/clients/{client}
        // DELETE /api/restaurant/clients/{client}
        Route::apiResource('clients', ClientController::class);

        // --- Réservations ---
        // GET    /api/restaurant/reservations?date=&statut=&table_id=
        // POST   /api/restaurant/reservations
        // GET    /api/restaurant/reservations/{reservation}
        // PUT    /api/restaurant/reservations/{reservation}
        // DELETE /api/restaurant/reservations/{reservation}
        Route::apiResource('reservations', ReservationController::class);

        // Actions sur le cycle de vie des réservations
        // POST   /api/restaurant/reservations/{reservation}/confirmer
        Route::post('reservations/{reservation}/confirmer', [ReservationController::class, 'confirmer'])
             ->name('reservations.confirmer');

        // POST   /api/restaurant/reservations/{reservation}/annuler
        Route::post('reservations/{reservation}/annuler', [ReservationController::class, 'annuler'])
             ->name('reservations.annuler');

        // POST   /api/restaurant/reservations/{reservation}/terminer
        Route::post('reservations/{reservation}/terminer', [ReservationController::class, 'terminer'])
             ->name('reservations.terminer');
    });
});
