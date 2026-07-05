<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des tables de restaurant</title>
    <meta name="description" content="Application de gestion de tables de restaurant avec cycle de vie strict.">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #f5f7fb;
            margin: 0;
            min-height: 100vh;
            color: #172a3a;
        }

        .page-content {
            max-width: 1040px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .hero {
            background: white;
            border: 1px solid #d8e2ef;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        .hero h1 {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            margin: 0 0 16px;
            line-height: 1.05;
        }

        .hero p {
            font-size: 1.05rem;
            line-height: 1.75;
            color: #475569;
            margin: 0 0 28px;
        }

        .hero .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .hero a {
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hero a.primary {
            background: #4338ca;
            color: white;
            box-shadow: 0 14px 24px rgba(67, 56, 202, 0.18);
        }

        .hero a.secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        .hero a:hover {
            transform: translateY(-2px);
        }

        .feature-list {
            margin-top: 40px;
            display: grid;
            gap: 20px;
        }

        .feature {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 24px;
        }

        .feature h2 {
            margin: 0 0 10px;
            font-size: 1.15rem;
        }

        .feature p {
            margin: 0;
            color: #475569;
            line-height: 1.75;
        }
    </style>
</head>
<body>
    <div class="page-content">
        <section class="hero">
            <h1>GESTION DES TABLES DE RESTAURANT</h1>
            <p>Application de gestion simple et fonctionnelle pour les tables de restaurant. Suivi du cycle de vie des tables : libre, réservée, occupée, à nettoyer et hors service.</p>

            <div class="buttons">
                <a href="/api/restaurant/zones" class="primary">Voir API Restaurant</a>
                <a href="/api/restaurant/tables" class="secondary">Voir les tables</a>
            </div>

            <div class="feature-list">
                <div class="feature">
                    <h2>Cycle de vie des tables</h2>
                    <p>La table n'est pas seulement libre ou prise. Elle suit un chemin métier strict et chaque transition est validée.</p>
                </div>
                <div class="feature">
                    <h2>Historique automatique</h2>
                    <p>Les changements de statut sont enregistrés automatiquement via un observer Eloquent, sans logique dispersée.</p>
                </div>
                <div class="feature">
                    <h2>Architecture propre</h2>
                    <p>Dépendances injectées, services écrits contre des interfaces et mutations atomiques via transactions DB.</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>