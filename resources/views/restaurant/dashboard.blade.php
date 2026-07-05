<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Restaurant</title>
    <style>
        body { margin: 0; font-family: Inter, sans-serif; background: #f2f6fb; color: #172a3a; }
        .container { max-width: 1280px; margin: 0 auto; padding: 24px; }
        header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
        header h1 { margin: 0; font-size: 2rem; }
        .grid { display: grid; gap: 20px; }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .nav { display:flex; justify-content:space-between; align-items:center; background:white; padding:14px 20px; border-radius:16px; margin-bottom:20px; box-shadow:0 8px 24px rgba(15,23,42,0.06); }
        .nav a { color:#4338ca; text-decoration:none; font-weight:600; }
        .search-box { display:flex; gap:10px; align-items:center; margin-top:16px; }
        .search-box input { width:280px; }
        .card { background: white; border: 1px solid #dbe4ef; border-radius: 18px; padding: 20px; box-shadow: 0 10px 30px rgba(18, 50, 88, 0.04); }
        .card h2 { margin-top: 0; font-size: 1.15rem; }
        .card p { color: #475569; line-height: 1.6; }
        .alert { padding: 16px 20px; border-radius: 14px; margin-bottom: 20px; }
        .alert.success { background: #eef7ed; border: 1px solid #d1ecd5; color: #1f5133; }
        .alert.error { background: #fbeaea; border: 1px solid #f3c1c1; color: #7c1d1d; }
        form { display: grid; gap: 14px; }
        label { font-weight: 600; color: #334155; }
        input, select, textarea, button { width: 100%; font: inherit; border-radius: 12px; border: 1px solid #cbd5e1; padding: 12px 14px; }
        textarea { resize: vertical; min-height: 100px; }
        button { cursor: pointer; background: #4338ca; color: white; border: 0; transition: transform .18s ease; }
        button:hover { transform: translateY(-1px); }
        .status { font-weight: 700; display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; font-size: 0.95rem; }
        .status.libre { background: #e8f5e9; color: #18794d; }
        .status.reservee { background: #fef6e7; color: #925b00; }
        .status.occupee { background: #eef2ff; color: #3730a3; }
        .status.a_nettoyer { background: #fff7ed; color: #92400e; }
        .status.hors_service { background: #f3f4f6; color: #4b5563; }
        .table-list, .reservation-list { width: 100%; border-collapse: collapse; }
        .table-list th, .table-list td, .reservation-list th, .reservation-list td { border-bottom: 1px solid #f1f5f9; padding: 12px 10px; text-align: left; }
        .table-list th, .reservation-list th { color: #475569; font-size: 0.95rem; text-transform: uppercase; letter-spacing: .05em; }
        .table-list tr:hover, .reservation-list tr:hover { background: #f8fbff; }
        .small-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .small-actions button { width: auto; padding: 10px 16px; font-size: .95rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <strong>Gestion table Restaurant</strong>
            <div style="display:flex;gap:16px;align-items:center;">
                <a href="#reservations">Réservations</a>
                <a href="{{ route('tables.index') }}">Tables</a>
                <a href="{{ route('clients.index') }}">Clients</a>
                <a href="{{ route('commandes.index') }}">Commandes</a>
                <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" style="width:auto;padding:10px 14px;">Déconnexion</button></form>
            </div>
        </div>

        <header>
            <div>
                <h1>Gestion table Restaurant</h1>
                <p style="margin: 8px 0 0;color:#475569;max-width:640px;">Une interface simple et opérationnelle pour suivre les tables, gérer les réservations et faire évoluer les statuts en restaurant.</p>
            </div>
        </header>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        <div class="grid grid-3">
            <div class="card">
                <h2>Zones</h2>
                <p>{{ $zones->count() }} zones affichées.</p>
            </div>
            <div class="card">
                <h2>Tables</h2>
                <p>{{ $zones->flatMap(fn($zone) => $zone->tables)->count() }} tables actives.</p>
            </div>
            <div class="card">
                <h2>Réservations actives</h2>
                <p>{{ $reservations->count() }} réservations actives.</p>
            </div>
        </div>

        <section style="margin-top: 24px; display:grid; gap:20px;">
            <div class="card">
                <h2>Réservations du jour</h2>
                <p>Nombre de réservations aujourd’hui : {{ $reservations->count() }}</p>
                <div class="search-box">
                    <input type="text" placeholder="Rechercher un client..." onkeyup="filterReservations(this.value)">
                </div>
            </div>
            <div class="card">
                <h2>Créer une réservation</h2>
                <form method="post" action="{{ route('dashboard.reservations.store') }}">
                    @csrf
                    <label for="table_id">Table</label>
                    <select id="table_id" name="table_id" required>
                        <option value="">Choisir une table libre</option>
                        @foreach($zones as $zone)
                            @foreach($zone->tables->where('statut', 'libre') as $table)
                                <option value="{{ $table->id }}">Zone {{ $zone->nom }} — Table {{ $table->numero }} ({{ $table->capacite }} pers.)</option>
                            @endforeach
                        @endforeach
                    </select>

                    <label for="nom_client">Nom du client</label>
                    <input id="nom_client" name="nom_client" type="text" required>

                    <label for="telephone_client">Téléphone</label>
                    <input id="telephone_client" name="telephone_client" type="text" required>

                    <label for="nombre_personnes">Nombre de personnes</label>
                    <input id="nombre_personnes" name="nombre_personnes" type="number" min="1" required>

                    <label for="date_debut">Début</label>
                    <input id="date_debut" name="date_debut" type="datetime-local" required>

                    <label for="date_fin">Fin</label>
                    <input id="date_fin" name="date_fin" type="datetime-local" required>

                    <label for="notes">Notes (optionnel)</label>
                    <textarea id="notes" name="notes"></textarea>

                    <button type="submit">Créer la réservation</button>
                </form>
            </div>

            <div class="card" id="tables">
                <h2>Actions sur les tables</h2>
                <table class="table-list">
                    <thead>
                        <tr>
                            <th>Zone</th>
                            <th>Table</th>
                            <th>Capacité</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zones as $zone)
                            @foreach($zone->tables as $table)
                                <tr>
                                    <td>{{ $zone->nom }}</td>
                                    <td>{{ $table->numero }}</td>
                                    <td>{{ $table->capacite }}</td>
                                    <td><span class="status {{ $table->statut->value }}">{{ $table->statut->label() }}</span></td>
                                    <td>
                                        <form method="post" action="{{ route('dashboard.tables.statut', ['table' => $table->id]) }}" style="display:grid;gap:8px;">
                                            @csrf
                                            <select name="statut" required>
                                                @foreach(App\Enums\TableStatut::cases() as $statut)
                                                    <option value="{{ $statut->value }}" {{ $statut === $table->statut ? 'selected' : '' }}>{{ $statut->label() }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="raison" placeholder="Raison (optionnel)">
                                            <button type="submit">Mettre à jour</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>Réservations actives</h2>
                <table class="reservation-list">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Table</th>
                            <th>Début / Fin</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reservationTableBody">
                        @forelse($reservations as $reservation)
                            <tr>
                                <td>{{ $reservation->id }}</td>
                                <td>{{ $reservation->nom_client }}</td>
                                <td>{{ $reservation->table->numero }} (Zone {{ $reservation->table->zone->nom }})</td>
                                <td>{{ $reservation->date_debut->format('d/m H:i') }} — {{ $reservation->date_fin->format('d/m H:i') }}</td>
                                <td><span class="status {{ $reservation->statut->value }}">{{ $reservation->statut->label() }}</span></td>
                                <td class="small-actions">
                                    <form method="post" action="{{ route('dashboard.reservations.confirmer', ['reservation' => $reservation->id]) }}">
                                        @csrf
                                        <button type="submit">Confirmer</button>
                                    </form>
                                    <form method="post" action="{{ route('dashboard.reservations.terminer', ['reservation' => $reservation->id]) }}">
                                        @csrf
                                        <button type="submit">Terminer</button>
                                    </form>
                                    <form method="post" action="{{ route('dashboard.reservations.annuler', ['reservation' => $reservation->id]) }}">
                                        @csrf
                                        <button type="submit">Annuler</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Aucune réservation active pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <script>
        function filterReservations(value) {
            const rows = document.querySelectorAll('#reservationTableBody tr');
            const query = value.toLowerCase();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
