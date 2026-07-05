<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommandeController extends Controller
{
    public function index(): View
    {
        $tables = RestaurantTable::with('zone')->orderBy('numero')->get();
        $reservations = Reservation::with('table.zone')->actives()->orderBy('date_debut')->get();

        return view('restaurant.commandes', compact('tables', 'reservations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'table_id' => ['required', 'exists:restaurant_tables,id'],
            'client' => ['required', 'string', 'max:255'],
            'items' => ['required', 'string'],
            'montant' => ['required', 'numeric', 'min:0'],
        ]);

        $items = array_filter(array_map('trim', explode("\n", $validated['items'])));
        $commande = [
            'table_id' => $validated['table_id'],
            'client' => $validated['client'],
            'items' => $items,
            'montant' => (float) $validated['montant'],
            'date' => now()->format('d/m/Y H:i'),
        ];

        session()->put('commande_' . $validated['table_id'], $commande);

        return redirect()->route('commandes.index')->with('success', 'Commande enregistrée avec succès.');
    }

    public function facture(int $tableId)
    {
        $commande = session()->get('commande_' . $tableId);

        if (! $commande) {
            return redirect()->route('commandes.index')->with('error', 'Aucune commande trouvée pour cette table.');
        }

        $table = RestaurantTable::findOrFail($tableId);

        return response()->view('restaurant.facture', compact('commande', 'table'))
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="facture-table-' . $table->numero . '.html"');
    }
}
