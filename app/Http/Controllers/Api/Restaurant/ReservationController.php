<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Contracts\ReservationServiceInterface;
use App\Exceptions\InvalidStateTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\StoreReservationRequest;
use App\Http\Requests\Restaurant\UpdateReservationRequest;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur REST pour les réservations — Skinny Controller.
 *
 * INJECTION DE DÉPENDANCES via constructeur :
 * ReservationServiceInterface est injectée automatiquement par le conteneur IoC.
 * Le binding est configuré dans AppServiceProvider::register().
 *
 * Ce contrôleur n'instancie JAMAIS directement un service.
 * Il ne contient AUCUNE logique métier : il orchestre seulement.
 */
class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationServiceInterface $reservationService
    ) {}

    /**
     * GET /api/restaurant/reservations
     * Supporte le filtrage par date et statut.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Reservation::with(['table.zone', 'client']);

        if ($date = $request->query('date')) {
            $query->pourDate($date);
        }

        if ($statut = $request->query('statut')) {
            $query->where('statut', $statut);
        }

        if ($tableId = $request->query('table_id')) {
            $query->where('table_id', $tableId);
        }

        $reservations = $query->orderBy('date_debut')->paginate(20);

        return response()->json($reservations);
    }

    /**
     * POST /api/restaurant/reservations
     *
     * La création est atomique (DB::transaction dans ReservationService).
     * Le statut de la table passe en 'reservee' en même temps.
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['_user_id'] = $request->user()->id;

            $reservation = $this->reservationService->creerReservation($data);

            return response()->json([
                'data'    => $reservation,
                'message' => 'Réservation créée avec succès.',
            ], 201);

        } catch (InvalidStateTransitionException $e) {
            return response()->json([
                'message' => 'La table n\'est pas disponible pour une réservation.',
                'detail'  => $e->getMessage(),
                'error'   => 'TABLE_NOT_AVAILABLE',
            ], 422);
        }
    }

    /**
     * GET /api/restaurant/reservations/{reservation}
     */
    public function show(Reservation $reservation): JsonResponse
    {
        $reservation->load(['table.zone', 'client']);

        return response()->json(['data' => $reservation]);
    }

    /**
     * PUT/PATCH /api/restaurant/reservations/{reservation}
     * Mise à jour des informations de base (pas du statut — voir actions dédiées).
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): JsonResponse
    {
        $reservation->update($request->validated());

        return response()->json([
            'data'    => $reservation->fresh(['table', 'client']),
            'message' => 'Réservation mise à jour avec succès.',
        ]);
    }

    /**
     * DELETE /api/restaurant/reservations/{reservation}
     * Annule la réservation (ne supprime pas l'enregistrement — audit trail).
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        try {
            $this->reservationService->annulerReservation($reservation);
        } catch (InvalidStateTransitionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'INVALID_STATE_TRANSITION',
            ], 422);
        }

        return response()->json(['message' => 'Réservation annulée avec succès.']);
    }

    // =========================================================================
    // Actions sur le cycle de vie des réservations
    // =========================================================================

    /**
     * POST /api/restaurant/reservations/{reservation}/confirmer
     * Le client est arrivé — table passe en 'occupee'.
     */
    public function confirmer(Reservation $reservation): JsonResponse
    {
        try {
            $reservation = $this->reservationService->confirmerReservation($reservation);
        } catch (InvalidStateTransitionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'INVALID_STATE_TRANSITION',
            ], 422);
        }

        return response()->json([
            'data'    => $reservation,
            'message' => 'Réservation confirmée. Table marquée comme occupée.',
        ]);
    }

    /**
     * POST /api/restaurant/reservations/{reservation}/annuler
     * Annule la réservation — table retourne en 'libre'.
     */
    public function annuler(Reservation $reservation): JsonResponse
    {
        try {
            $reservation = $this->reservationService->annulerReservation($reservation);
        } catch (InvalidStateTransitionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'INVALID_STATE_TRANSITION',
            ], 422);
        }

        return response()->json([
            'data'    => $reservation,
            'message' => 'Réservation annulée. Table remise en statut libre.',
        ]);
    }

    /**
     * POST /api/restaurant/reservations/{reservation}/terminer
     * Les clients sont partis — table passe en 'a_nettoyer'.
     */
    public function terminer(Reservation $reservation): JsonResponse
    {
        try {
            $reservation = $this->reservationService->terminerReservation($reservation);
        } catch (InvalidStateTransitionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error'   => 'INVALID_STATE_TRANSITION',
            ], 422);
        }

        return response()->json([
            'data'    => $reservation,
            'message' => 'Réservation terminée. Table marquée à nettoyer.',
        ]);
    }
}
