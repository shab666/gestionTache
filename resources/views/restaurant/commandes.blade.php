<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes & Facturation</title>
    <style>
        body { margin:0; font-family:Inter, sans-serif; background:#f2f6fb; color:#172a3a; }
        .container { max-width: 1200px; margin:0 auto; padding:24px; }
        .nav { display:flex; justify-content:space-between; align-items:center; background:white; padding:14px 20px; border-radius:16px; margin-bottom:20px; box-shadow:0 8px 24px rgba(15,23,42,0.06); }
        .card { background:white; border:1px solid #dbe4ef; border-radius:18px; padding:20px; margin-bottom:20px; }
        .grid { display:grid; gap:20px; grid-template-columns: 1fr 1fr; }
        form { display:grid; gap:12px; }
        input, textarea, select, button { width:100%; font:inherit; padding:12px 14px; border-radius:12px; border:1px solid #cbd5e1; }
        button { cursor:pointer; background:#4338ca; color:white; border:0; }
        .alert { padding: 14px 16px; border-radius: 12px; margin-bottom: 16px; }
        .alert.success { background:#eef7ed; color:#1f5133; }
        .alert.error { background:#fbeaea; color:#7c1d1d; }
        table { width:100%; border-collapse:collapse; }
        th, td { border-bottom:1px solid #f1f5f9; padding:12px 10px; text-align:left; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav">
        <strong>Commandes & Facturation</strong>
        <div style="display:flex;gap:12px;align-items:center;">
            <a href="{{ route('dashboard.index') }}" style="color:#4338ca;text-decoration:none;font-weight:600;">Dashboard</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" style="width:auto;padding:10px 14px;">Déconnexion</button></form>
        </div>
    </div>

    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert error">{{ session('error') }}</div>@endif

    <div class="grid">
        <div class="card">
            <h2>Créer une commande</h2>
            <form method="post" action="{{ route('commandes.store') }}">
                @csrf
                <select name="table_id" required>
                    <option value="">Choisir une table</option>
                    @foreach($tables as $table)
                        <option value="{{ $table->id }}">{{ $table->numero }} — {{ $table->zone->nom ?? '—' }}</option>
                    @endforeach
                </select>
                <input name="client" placeholder="Nom du client" required>
                <textarea name="items" placeholder="Saisir un article par ligne&#10;Ex : Café 2.5&#10;Burger 9.0" required></textarea>
                <input name="montant" type="number" step="0.01" placeholder="Montant total" required>
                <button type="submit">Enregistrer la commande</button>
            </form>
        </div>

        <div class="card">
            <h2>Commandes enregistrées</h2>
            <table>
                <thead><tr><th>Table</th><th>Client</th><th>Montant</th><th>Facture</th></tr></thead>
                <tbody>
                    @foreach($tables as $table)
                        @php($commande = session('commande_' . $table->id))
                        @if($commande)
                            <tr>
                                <td>{{ $table->numero }}</td>
                                <td>{{ $commande['client'] }}</td>
                                <td>{{ number_format($commande['montant'], 2, ',', ' ') }} €</td>
                                <td><a href="{{ route('commandes.facture', $table->id) }}" style="color:#4338ca;">Télécharger PDF</a></td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
