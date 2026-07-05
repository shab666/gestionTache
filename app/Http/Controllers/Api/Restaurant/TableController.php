<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Contracts\TableTransitionInterface;
use App\Enums\TableStatut;
use App\Exceptions\InvalidStateTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\StoreTableRequest;
use App\Http\Requests\Restaurant\UpdateTableRequest;
use App\Http\Requests\Restaurant\UpdateTableStatutRequest;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;

/**
 * Contrôleur REST pour les tables de restaurant — Skinny Controller.
 *
 * INJECTION DE DÉPENDANCES via le constructeur :
 * Le contrôleur ne sait pas quelle implémentation de TableTransitionInterface
 * il reçoit. C'est le conteneur IoC de Laravel (configuré dans AppServiceProvider)
 * qui résout et injecte la bonne instance.
 *
 * Pour les tests : on peut injecter un mock de TableTransitionInterface
 * sans aucune modification de ce contrôleur.
 */
class TableController extends Controller
{
    /**
     * Injection par constructeur (jamais de "new TableTransitionService()").
     */
    public function __construct(
        private readonly TableTransitionInterface $tableTransition
    ) {}

    /**
     * GET /api/restaurant/tables
     */
    public function index(): JsonResponse
    {
        $tables = RestaurantTable::with('zone')
            ->active()
            ->get();

        return response()->json(['data' => $tables]);
    }

    /**
     * POST /api/restaurant/tables
     */
    public function store(StoreTableRequest $request): JsonResponse
    {
        $table = RestaurantTable::create($request->validated());

        return response()->json([
            'data'    => $table->load('zone'),
            'message' => 'Table créée avec succès.',
        ], 201);
    }

    /**
     * GET /api/restaurant/tables/{table}
     */
    public function show(RestaurantTable $table): JsonResponse
    {
        $table->load(['zone', 'reservations' => fn ($q) => $q->actives()]);

        return response()->json(['data' => $table]);
    }

    /**
     * PUT/PATCH /api/restaurant/tables/{table}
     */
    public function update(UpdateTableRequest $request, RestaurantTable $table): JsonResponse
    {
        $table->update($request->validated());

        return response()->json([
            'data'    => $table->fresh(['zone']),
            'message' => 'Table mise à jour avec succès.',
        ]);
    }

    /**
     * DELETE /api/restaurant/tables/{table}
     */
    public function destroy(RestaurantTable $table): JsonResponse
    {
        if ($table->reservations()->actives()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer une table avec des réservations actives.',
                'error'   => 'TABLE_HAS_ACTIVE_RESERVATIONS',
            ], 409);
        }

        $table->delete();

        return response()->json(['message' => 'Table supprimée avec succès.']);
    }

    /**
     * PATCH /api/restaurant/tables/{table}/statut
     *
     * Endpoint dédié au changement de statut d'une table.
     * Délègue ENTIÈREMENT la validation métier au TableTransitionService.
     */
    public function updateStatut(UpdateTableStatutRequest $request, RestaurantTable $table): JsonResponse
    {
        $nouveauStatut = TableStatut::from($request->validated('statut'));

        try {
            $this->tableTransition->transition(
                table:  $table,
                to:     $nouveauStatut,
                userId: $request->user()->id,
                raison: $request->validated('raison'),
            );
        } catch (InvalidStateTransitionException $e) {
            // On transforme l'exception métier en réponse HTTP 422
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'INVALID_STATE_TRANSITION',
                'from'    => $e->getFromStatut()->label(),
                'to'      => $e->getToStatut()->label(),
            ], 422);
        }

        return response()->json([
            'data'    => $table->fresh(),
            'message' => "Statut mis à jour vers [{$nouveauStatut->label()}].",
        ]);
    }
}
