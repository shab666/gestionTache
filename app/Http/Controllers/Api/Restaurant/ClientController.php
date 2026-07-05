<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\StoreClientRequest;
use App\Http\Requests\Restaurant\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur REST pour les clients — Skinny Controller.
 */
class ClientController extends Controller
{
    /**
     * GET /api/restaurant/clients
     * Supporte la recherche via ?q=terme
     */
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

        if ($search = $request->query('q')) {
            $query->recherche($search);
        }

        $clients = $query->withCount('reservations')
                         ->orderBy('nom')
                         ->paginate(20);

        return response()->json($clients);
    }

    /**
     * POST /api/restaurant/clients
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return response()->json([
            'data'    => $client,
            'message' => 'Client créé avec succès.',
        ], 201);
    }

    /**
     * GET /api/restaurant/clients/{client}
     */
    public function show(Client $client): JsonResponse
    {
        $client->load(['reservations.table.zone']);

        return response()->json(['data' => $client]);
    }

    /**
     * PUT/PATCH /api/restaurant/clients/{client}
     */
    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());

        return response()->json([
            'data'    => $client->fresh(),
            'message' => 'Client mis à jour avec succès.',
        ]);
    }

    /**
     * DELETE /api/restaurant/clients/{client}
     */
    public function destroy(Client $client): JsonResponse
    {
        // Anonymisation plutôt que suppression si des réservations existent
        if ($client->reservations()->exists()) {
            return response()->json([
                'message' => 'Ce client a des réservations. Utilisez la désactivation plutôt que la suppression.',
                'error'   => 'CLIENT_HAS_RESERVATIONS',
            ], 409);
        }

        $client->delete();

        return response()->json(['message' => 'Client supprimé avec succès.']);
    }
}
