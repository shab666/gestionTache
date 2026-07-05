<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; display: grid; place-items: center; min-height: 100vh; }
        .card { background: white; padding: 32px; border-radius: 16px; width: min(100%, 420px); box-shadow: 0 15px 40px rgba(0,0,0,.08); }
        h1 { margin-top: 0; margin-bottom: 8px; }
        p { color: #64748b; }
        form { display: grid; gap: 14px; margin-top: 20px; }
        label { font-weight: 600; }
        input, button { padding: 12px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font: inherit; }
        button { background: #4338ca; color: white; border: 0; cursor: pointer; }
        .error { color: #b91c1c; font-size: .95rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Connexion</h1>
        <p>Accédez au tableau de bord du restaurant.</p>

        @if($errors->any())
            <p class="error">{{ $errors->first() }}</p>
        @endif

        <form method="post" action="{{ route('login.post') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="{{ old('email') }}">

            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" required>

            <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                <input type="checkbox" name="remember" value="1"> Se souvenir de moi
            </label>

            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
