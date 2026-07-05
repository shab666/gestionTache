<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\HistoriqueStatutTable;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;

/**
 * Contrôleur en lecture seule pour l'historique des statuts.
 *
 * Ce contrôleur n'expose que des endpoints GET.
 * L'écriture dans l'historique est exclusivement gérée par TableObserver.
 */
class HistoriqueStatutController extends Controller
{
    /**
     * GET /api/restaurant/tables/{table}/historique
     *
     * Retourne l'historique chronologique complet des changements de statut
     * d'une table donnée, avec les informations de l'utilisateur modificateur.
     */
    public function index(RestaurantTable $table): JsonResponse
    {
        $historique = HistoriqueStatutTable::where('table_id', $table->id)
            ->with('modificateur:id,name,email')
            ->orderByDesc('modifie_a')
            ->paginate(50);

        return response()->json([
            'data'  => $historique,
            'table' => [
                'id'     => $table->id,
                'numero' => $table->numero,
                'statut' => $table->statut->label(),
            ],
        ]);
    }

    /**
     * GET /api/restaurant/historique
     *
     * Vue globale de tous les changements de statuts (pour le tableau de bord admin).
     */
    public function global(): JsonResponse
    {
        $historique = HistoriqueStatutTable::with([
                'table:id,numero,zone_id',
                'table.zone:id,nom',
                'modificateur:id,name',
            ])
            ->orderByDesc('modifie_a')
            ->paginate(50);

        return response()->json(['data' => $historique]);
    }
}
