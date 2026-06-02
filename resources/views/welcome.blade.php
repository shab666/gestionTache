<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau Kanban</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 font-sans antialiased">

    <div class="py-12">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8"> Tableau de Bord Kanban Interactif</h1>
        
        <livewire:kanban-board />
    </div>

    @livewireScripts-
</body>
</html>