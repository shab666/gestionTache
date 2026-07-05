<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des tables</title>
    <style>
        body { margin:0; font-family:Inter, sans-serif; background:#f2f6fb; color:#172a3a; }
        .container { max-width: 1280px; margin:0 auto; padding:24px; }
        .nav { display:flex; justify-content:space-between; align-items:center; background:white; padding:14px 20px; border-radius:16px; margin-bottom:20px; box-shadow:0 8px 24px rgba(15,23,42,0.06); }
        .card { background:white; border:1px solid #dbe4ef; border-radius:18px; padding:20px; margin-bottom:20px; }
        .grid { display:grid; gap:20px; grid-template-columns: 1fr 1fr; }
        form { display:grid; gap:12px; }
        input, select, button { width:100%; font:inherit; padding:12px 14px; border-radius:12px; border:1px solid #cbd5e1; }
        button { cursor:pointer; background:#4338ca; color:white; border:0; }
        table { width:100%; border-collapse:collapse; }
        th, td { border-bottom:1px solid #f1f5f9; padding:12px 10px; text-align:left; }
        .alert { padding: 14px 16px; border-radius: 12px; margin-bottom: 16px; }
        .alert.success { background:#eef7ed; color:#1f5133; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav">
        <strong>Gestion des tables</strong>
        <div style="display:flex;gap:12px;align-items:center;">
            <a href="{{ route('dashboard.index') }}" style="color:#4338ca;text-decoration:none;font-weight:600;">Dashboard</a>
            <a href="{{ route('clients.index') }}" style="color:#4338ca;text-decoration:none;font-weight:600;">Clients</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" style="width:auto;padding:10px 14px;">Déconnexion</button></form>
        </div>
    </div>

    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif

    <div class="grid">
        <div class="card">
            <h2>{{ $editingTable ? 'Modifier une table' : 'Ajouter une table' }}</h2>
            <form method="post" action="{{ $editingTable ? route('tables.update', $editingTable) : route('tables.store') }}">
                @csrf
                @if($editingTable)
                    @method('PUT')
                @endif
                <select name="zone_id" required>
                    <option value="">Choisir une zone</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ old('zone_id', $editingTable?->zone_id ?? '') == $zone->id ? 'selected' : '' }}>{{ $zone->nom }}</option>
                    @endforeach
                </select>
                <input name="numero" placeholder="Numéro de table" required value="{{ old('numero', $editingTable?->numero ?? '') }}">
                <input name="capacite" type="number" min="1" placeholder="Capacité" required value="{{ old('capacite', $editingTable?->capacite ?? '') }}">
                <select name="forme" required>
                    <option value="">Choisir une forme</option>
                    @foreach(App\Enums\TableForme::cases() as $forme)
                        <option value="{{ $forme->value }}" {{ old('forme', $editingTable?->forme?->value ?? '') == $forme->value ? 'selected' : '' }}>{{ $forme->label() }}</option>
                    @endforeach
                </select>
                <input name="position_x" type="number" step="0.01" placeholder="Position X" value="{{ old('position_x', $editingTable?->position_x ?? '') }}">
                <input name="position_y" type="number" step="0.01" placeholder="Position Y" value="{{ old('position_y', $editingTable?->position_y ?? '') }}">
                <select name="statut" required>
                    @foreach(App\Enums\TableStatut::cases() as $statut)
                        <option value="{{ $statut->value }}" {{ old('statut', $editingTable?->statut?->value ?? '') == $statut->value ? 'selected' : '' }}>{{ $statut->label() }}</option>
                    @endforeach
                </select>
                <div class="actions">
                    <button type="submit">{{ $editingTable ? 'Mettre à jour' : 'Enregistrer' }}</button>
                    @if($editingTable)
                        <a href="{{ route('tables.index') }}" style="background:#64748b;color:white;text-decoration:none;padding:10px 12px;border-radius:12px;display:inline-block;">Annuler</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Liste des tables</h2>
            <table>
                <thead><tr><th>Table</th><th>Zone</th><th>Capacité</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($tables as $table)
                        <tr>
                            <td>{{ $table->numero }}</td>
                            <td>{{ $table->zone->nom ?? '—' }}</td>
                            <td>{{ $table->capacite }}</td>
                            <td>{{ $table->statut->label() }}</td>
                            <td class="actions">
                                <form method="post" action="{{ route('tables.destroy', $table) }}">@csrf @method('DELETE')<button type="submit" style="background:#dc2626;width:auto;padding:10px 12px;">Supprimer</button></form>
                                <a href="{{ route('tables.index', ['edit' => $table->id]) }}" style="background:#0f766e;color:white;text-decoration:none;padding:10px 12px;border-radius:12px;display:inline-block;">Éditer</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $tables->links() }}</div>
        </div>
    </div>
</div>
</body>
</html>
