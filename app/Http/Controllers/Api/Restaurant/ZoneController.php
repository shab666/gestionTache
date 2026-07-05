<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\StoreZoneRequest;
use App\Http\Requests\Restaurant\UpdateZoneRequest;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;

/**
 * Contrôleur REST pour les zones de restaurant — Skinny Controller.
 *
 * Responsabilité : Orchestration HTTP uniquement.
 * - Recevoir la requête validée (via FormRequest)
 * - Appeler le Model/Service approprié
 * - Retourner la réponse JSON
 *
 * Ce contrôleur ne contient AUCUNE logique métier.
 * La validation est dans StoreZoneRequest / UpdateZoneRequest.
 */
class ZoneController extends Controller
{
    /**
     * GET /api/restaurant/zones
     * Liste toutes les zones avec leurs tables.
     */
    public function index(): JsonResponse
    {
        $zones = Zone::with(['tables' => fn ($q) => $q->active()])
                     ->withCount('tables')
                     ->get();

        return response()->json([
            'data'    => $zones,
            'message' => 'Liste des zones récupérée avec succès.',
        ]);
    }

    /**
     * POST /api/restaurant/zones
     */
    public function store(StoreZoneRequest $request): JsonResponse
    {
        $zone = Zone::create($request->validated());

        return response()->json([
            'data'    => $zone,
            'message' => 'Zone créée avec succès.',
        ], 201);
    }

    /**
     * GET /api/restaurant/zones/{zone}
     */
    public function show(Zone $zone): JsonResponse
    {
        $zone->load(['tables.reservations' => fn ($q) => $q->actives()]);

        return response()->json(['data' => $zone]);
    }

    /**
     * PUT/PATCH /api/restaurant/zones/{zone}
     */
    public function update(UpdateZoneRequest $request, Zone $zone): JsonResponse
    {
        $zone->update($request->validated());

        return response()->json([
            'data'    => $zone->fresh(),
            'message' => 'Zone mise à jour avec succès.',
        ]);
    }

    /**
     * DELETE /api/restaurant/zones/{zone}
     */
    public function destroy(Zone $zone): JsonResponse
    {
        // Vérification : on ne peut pas supprimer une zone avec des tables actives
        if ($zone->tables()->where('active', true)->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer une zone contenant des tables actives.',
                'error'   => 'ZONE_HAS_ACTIVE_TABLES',
            ], 409);
        }

        $zone->delete();

        return response()->json(['message' => 'Zone supprimée avec succès.'], 200);
    }
}
