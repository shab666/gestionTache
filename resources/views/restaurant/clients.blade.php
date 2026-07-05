<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients</title>
    <style>
        body { margin:0; font-family:Inter, sans-serif; background:#f2f6fb; color:#172a3a; }
        .container { max-width: 1200px; margin:0 auto; padding:24px; }
        .nav { display:flex; justify-content:space-between; align-items:center; background:white; padding:14px 20px; border-radius:16px; margin-bottom:20px; box-shadow:0 8px 24px rgba(15,23,42,0.06); }
        .card { background:white; border:1px solid #dbe4ef; border-radius:18px; padding:20px; margin-bottom:20px; }
        form { display:grid; gap:12px; }
        input, textarea, button { width:100%; font:inherit; padding:12px 14px; border-radius:12px; border:1px solid #cbd5e1; }
        button { cursor:pointer; background:#4338ca; color:white; border:0; }
        .grid { display:grid; gap:20px; grid-template-columns: 1.2fr 0.8fr; }
        table { width:100%; border-collapse:collapse; }
        th, td { border-bottom:1px solid #f1f5f9; padding:12px 10px; text-align:left; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; }
        .alert { padding: 14px 16px; border-radius: 12px; margin-bottom: 16px; }
        .alert.success { background:#eef7ed; color:#1f5133; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav">
        <strong>Clients</strong>
        <div style="display:flex;gap:12px;align-items:center;">
            <a href="{{ route('dashboard.index') }}" style="color:#4338ca;text-decoration:none;font-weight:600;">Dashboard</a>
            <a href="{{ route('tables.index') }}" style="color:#4338ca;text-decoration:none;font-weight:600;">Tables</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" style="width:auto;padding:10px 14px;">Déconnexion</button></form>
        </div>
    </div>

    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif

    <div class="grid">
        <div class="card">
            <h2>Liste des clients</h2>
            <form method="get" action="{{ route('clients.index') }}" style="display:flex; gap:10px; margin-bottom:16px;">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher un client...">
                <button type="submit" style="width:auto;">Rechercher</button>
            </form>
            <table>
                <thead><tr><th>Nom</th><th>Téléphone</th><th>Email</th><th>Notes</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td>{{ $client->nomComplet }}</td>
                            <td>{{ $client->telephone }}</td>
                            <td>{{ $client->email ?? '—' }}</td>
                            <td>{{ $client->notes ?? '—' }}</td>
                            <td class="actions">
                                <form method="post" action="{{ route('clients.destroy', $client) }}">@csrf @method('DELETE')<button type="submit" style="background:#dc2626;width:auto;padding:10px 12px;">Supprimer</button></form>
                                <a href="{{ route('clients.index', ['edit' => $client->id, 'search' => $search]) }}" style="background:#0f766e;color:white;text-decoration:none;padding:10px 12px;border-radius:12px;display:inline-block;">Éditer</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $clients->links() }}</div>
        </div>

        <div class="card">
            <h2>{{ $editingClient ? 'Modifier un client' : 'Ajouter un client' }}</h2>
            <form method="post" action="{{ $editingClient ? route('clients.update', $editingClient) : route('clients.store') }}">
                @csrf
                @if($editingClient)
                    @method('PUT')
                @endif
                <input name="nom" placeholder="Nom" required value="{{ old('nom', $editingClient?->nom ?? '') }}">
                <input name="prenom" placeholder="Prénom" required value="{{ old('prenom', $editingClient?->prenom ?? '') }}">
                <input name="email" type="email" placeholder="Email (optionnel)" value="{{ old('email', $editingClient?->email ?? '') }}">
                <input name="telephone" placeholder="Téléphone" required value="{{ old('telephone', $editingClient?->telephone ?? '') }}">
                <textarea name="notes" placeholder="Notes">{{ old('notes', $editingClient?->notes ?? '') }}</textarea>
                <div class="actions">
                    <button type="submit">{{ $editingClient ? 'Mettre à jour' : 'Enregistrer' }}</button>
                    @if($editingClient)
                        <a href="{{ route('clients.index') }}" style="background:#64748b;color:white;text-decoration:none;padding:10px 12px;border-radius:12px;display:inline-block;">Annuler</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
