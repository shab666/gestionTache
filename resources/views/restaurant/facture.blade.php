<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; }
        .box { border: 1px solid #d1d5db; padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        .total { font-weight: bold; font-size: 16px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Facture Restaurant</h2>
    <p><strong>Table :</strong> {{ $table->numero }}</p>
    <p><strong>Client :</strong> {{ $commande['client'] }}</p>
    <p><strong>Date :</strong> {{ $commande['date'] }}</p>

    <table>
        <thead>
            <tr><th>Article</th></tr>
        </thead>
        <tbody>
            @foreach($commande['items'] as $item)
                <tr><td>{{ $item }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">Montant total : {{ number_format($commande['montant'], 2, ',', ' ') }} €</div>
</div>
</body>
</html>
