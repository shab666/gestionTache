<?php

namespace App\Providers;

use App\Contracts\ReservationServiceInterface;
use App\Contracts\TableTransitionInterface;
use App\Models\RestaurantTable;
use App\Observers\TableObserver;
use App\Services\ReservationService;
use App\Services\TableTransitionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les bindings du conteneur IoC (Inversion of Control).
     *
     * PRINCIPE DIP (Dependency Inversion Principle) :
     * Les classes de haut niveau (contrôleurs, services) dépendent d'ABSTRACTIONS
     * (interfaces), pas d'implémentations concrètes.
     *
     * Ce binding dit au conteneur Laravel :
     * "Quand quelqu'un demande TableTransitionInterface, donne-lui TableTransitionService."
     *
     * Pour les tests : on peut remplacer ces bindings par des mocks :
     * $this->app->bind(TableTransitionInterface::class, fn() => Mockery::mock(TableTransitionInterface::class));
     */
    public function register(): void
    {
        // Binding Interface → Implémentation concrète
        $this->app->bind(
            abstract: TableTransitionInterface::class,
            concrete: TableTransitionService::class,
        );

        $this->app->bind(
            abstract: ReservationServiceInterface::class,
            concrete: ReservationService::class,
        );
    }

    /**
     * Bootstrap : enregistrement des Observers et autres hooks.
     */
    public function boot(): void
    {
        // L'Observer TableObserver sera déclenché automatiquement sur tout
        // événement Eloquent du modèle RestaurantTable (created, updated, deleted...).
        // Notre logique se trouve dans updated() pour capturer les changements de statut.
        RestaurantTable::observe(TableObserver::class);
    }
}
