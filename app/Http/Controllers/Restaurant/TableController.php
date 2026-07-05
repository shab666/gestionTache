<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\TableForme;
use App\Enums\TableStatut;
use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(Request $request): View
    {
        $editingTable = $request->filled('edit') ? RestaurantTable::find($request->get('edit')) : null;
        $tables = RestaurantTable::with('zone')->orderBy('numero')->paginate(20);
        $zones = Zone::orderBy('nom')->get();

        return view('restaurant.tables', compact('tables', 'zones', 'editingTable'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'zone_id' => ['required', 'exists:zones,id'],
            'numero' => ['required', 'string', 'max:50'],
            'capacite' => ['required', 'integer', 'min:1'],
            'forme' => ['required', 'in:' . implode(',', TableForme::values())],
            'position_x' => ['nullable', 'numeric'],
            'position_y' => ['nullable', 'numeric'],
            'statut' => ['required', 'in:' . implode(',', TableStatut::values())],
        ]);

        RestaurantTable::create($validated + ['active' => true]);

        return redirect()->route('tables.index')->with('success', 'Table ajoutée avec succès.');
    }

    public function update(Request $request, RestaurantTable $table): RedirectResponse
    {
        $validated = $request->validate([
            'zone_id' => ['required', 'exists:zones,id'],
            'numero' => ['required', 'string', 'max:50'],
            'capacite' => ['required', 'integer', 'min:1'],
            'forme' => ['required', 'in:' . implode(',', TableForme::values())],
            'position_x' => ['nullable', 'numeric'],
            'position_y' => ['nullable', 'numeric'],
            'statut' => ['required', 'in:' . implode(',', TableStatut::values())],
        ]);

        $table->update($validated);

        return redirect()->route('tables.index')->with('success', 'Table mise à jour avec succès.');
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('tables.index')->with('success', 'Table supprimée avec succès.');
    }
}
