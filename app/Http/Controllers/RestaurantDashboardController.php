<?php

namespace App\Http\Controllers;

use App\Contracts\ReservationServiceInterface;
use App\Contracts\TableTransitionInterface;
use App\Enums\TableStatut;
use App\Exceptions\InvalidStateTransitionException;
use App\Http\Requests\Restaurant\StoreReservationWebRequest;
use App\Http\Requests\Restaurant\UpdateTableStatutWebRequest;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RestaurantDashboardController extends Controller
{
    public function __construct(
        private readonly ReservationServiceInterface $reservationService,
        private readonly TableTransitionInterface $tableTransition,
    ) {}

    public function index(): View
    {
        $zones = Zone::with(['tables' => fn ($query) => $query->active()->with(['reservations' => fn ($query) => $query->actives()])])->get();
        $clients = Client::orderBy('nom')->get();
        $reservations = Reservation::with(['table.zone', 'client'])
                                   ->actives()
                                   ->orderBy('date_debut')
                                   ->get();

        return view('restaurant.dashboard', compact('zones', 'clients', 'reservations'));
    }

    public function storeReservation(StoreReservationWebRequest $request): RedirectResponse
    {
        try {
            $reservation = $this->reservationService->creerReservation(array_merge(
                $request->validated(),
                ['_user_id' => null],
            ));

            return redirect()->route('dashboard.index')
                ->with('success', "Réservation créée avec succès pour la table {$reservation->table->numero}.");
        } catch (InvalidStateTransitionException $exception) {
            return redirect()->route('dashboard.index')
                ->with('error', $exception->getMessage());
        }
    }

    public function updateTableStatut(UpdateTableStatutWebRequest $request, RestaurantTable $table): RedirectResponse
    {
        try {
            $this->tableTransition->transition(
                table:  $table,
                to:     TableStatut::from($request->validated('statut')),
                userId: null,
                raison: $request->validated('raison'),
            );

            return redirect()->route('dashboard.index')
                ->with('success', "Statut de la table {$table->numero} mis à jour.");
        } catch (InvalidStateTransitionException $exception) {
            return redirect()->route('dashboard.index')
                ->with('error', $exception->getMessage());
        }
    }

    public function confirmerReservation(Reservation $reservation): RedirectResponse
    {
        try {
            $this->reservationService->confirmerReservation($reservation);

            return redirect()->route('dashboard.index')
                ->with('success', "Réservation #{$reservation->id} confirmée.");
        } catch (InvalidStateTransitionException $exception) {
            return redirect()->route('dashboard.index')
                ->with('error', $exception->getMessage());
        }
    }

    public function annulerReservation(Reservation $reservation): RedirectResponse
    {
        try {
            $this->reservationService->annulerReservation($reservation);

            return redirect()->route('dashboard.index')
                ->with('success', "Réservation #{$reservation->id} annulée.");
        } catch (InvalidStateTransitionException $exception) {
            return redirect()->route('dashboard.index')
                ->with('error', $exception->getMessage());
        }
    }

    public function terminerReservation(Reservation $reservation): RedirectResponse
    {
        try {
            $this->reservationService->terminerReservation($reservation);

            return redirect()->route('dashboard.index')
                ->with('success', "Réservation #{$reservation->id} terminée.");
        } catch (InvalidStateTransitionException $exception) {
            return redirect()->route('dashboard.index')
                ->with('error', $exception->getMessage());
        }
    }
}
