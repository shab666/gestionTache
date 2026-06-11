<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport du Projet - {{ $project->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; }
        .project-title { color: #4F46E5; font-size: 24px; margin-bottom: 5px; }
        .section-title { background: #F1F5F9; padding: 8px; margin-top: 20px; font-weight: bold; border-left: 4px solid #4F46E5; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #E2E8F0; padding: 10px; text-align: left; font-size: 12px; }
        th { background-color: #F8FAFC; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .badge-todo { background: #F1F5F9; color: #64748B; }
        .badge-progress { background: #EFF6FF; color: #3B82F6; }
        .badge-done { background: #ECFDF5; color: #10B981; }
        .progress-container { width: 100%; background: #E2E8F0; border-radius: 10px; height: 20px; margin-top: 10px; }
        .progress-bar { height: 100%; background: #4F46E5; border-radius: 10px; text-align: center; color: white; font-size: 12px; line-height: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #94A3B8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="project-title">{{ $project->name }}</div>
        <div>Généré le {{ date('d/m/Y H:i') }}</div>
    </div>

    <div class="section-title">Progression Globale</div>
    <div class="progress-container">
        <div class="progress-bar" style="width: {{ $progress }}%;">{{ $progress }}%</div>
    </div>
    <p style="font-size: 12px;">{{ $completedTasksCount }} tâches terminées sur {{ $totalTasksCount }} au total.</p>

    <div class="section-title">Membres de l'équipe</div>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
            </tr>
        </thead>
        <tbody>
            @foreach($project->users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->roles->pluck('name')->implode(', ') ?: 'Membre' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Liste des Tâches</div>
    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Statut</th>
                <th>Priorité</th>
                <th>Échéance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($project->tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>
                    <span class="badge badge-{{ str_replace('_', '-', $task->status) }}">
                        {{ str_replace('_', ' ', $task->status) }}
                    </span>
                </td>
                <td>{{ strtoupper($task->priority) }}</td>
                <td>{{ $task->due_date ? date('d/m/Y', strtotime($task->due_date)) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        GestionTacheEquipe - Rapport Automatique
    </div>
</body>
</html>
