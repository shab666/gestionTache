<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Un filtre global : L'Admin a TOUS les droits par défaut, pas besoin de vérifier le reste.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
    }

    /**
     * Qui peut voir une tâche précise ?
     */
    public function view(User $user, Task $task)
    {
        // Un Manager ou un membre qui appartient au projet de la tâche peut la voir
        return $user->hasRole('Manager') || $task->project->users->contains($user->id);
    }

    /**
     * Qui peut modifier (mettre à jour) une tâche ?
     */
    public function update(User $user, Task $task)
    {
        // Un Manager peut tout modifier.
        if ($user->hasRole('Manager')) {
            return true;
        }

        // Un membre peut modifier la tâche UNIQUEMENT si elle lui est assignée
        return $user->hasRole('Membre') && $task->user_id === $user->id;
    }

    /**
     * Qui peut supprimer une tâche ?
     */
    public function delete(User $user, Task $task)
    {
        // Seuls l'Admin (géré dans before) et le Manager peuvent supprimer une tâche
        return $user->hasRole('Manager');
    }
}
