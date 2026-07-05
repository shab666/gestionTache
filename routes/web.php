<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Restaurant\ClientController;
use App\Http\Controllers\Restaurant\CommandeController;
use App\Http\Controllers\Restaurant\TableController;
use App\Http\Controllers\RestaurantDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [RestaurantDashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/reservations', [RestaurantDashboardController::class, 'storeReservation'])->name('dashboard.reservations.store');
    Route::post('/reservations/{reservation}/confirmer', [RestaurantDashboardController::class, 'confirmerReservation'])->name('dashboard.reservations.confirmer');
    Route::post('/reservations/{reservation}/annuler', [RestaurantDashboardController::class, 'annulerReservation'])->name('dashboard.reservations.annuler');
    Route::post('/reservations/{reservation}/terminer', [RestaurantDashboardController::class, 'terminerReservation'])->name('dashboard.reservations.terminer');
    Route::post('/tables/{table}/statut', [RestaurantDashboardController::class, 'updateTableStatut'])->name('dashboard.tables.statut');

    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
    Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
    Route::put('/tables/{table}', [TableController::class, 'update'])->name('tables.update');
    Route::delete('/tables/{table}', [TableController::class, 'destroy'])->name('tables.destroy');

    Route::get('/commandes', [CommandeController::class, 'index'])->name('commandes.index');
    Route::post('/commandes', [CommandeController::class, 'store'])->name('commandes.store');
    Route::get('/commandes/{tableId}/facture', [CommandeController::class, 'facture'])->name('commandes.facture');
});
