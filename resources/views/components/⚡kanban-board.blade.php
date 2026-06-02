<?php

use Livewire\Component;
use App\Models\Task;

new class extends Component
{
    // On écoute le signal envoyé par le glisser-déposer JavaScript
    protected $listeners = ['taskMoved' => 'updateTaskStatus'];

    public function updateTaskStatus($id, $status)
    {
        $task = Task::find($id);
        
        if ($task) {
            // 1. Mise à jour en Base de données
            $task->update(['status' => $status]);

            // 2. Envoi immédiat à Pusher pour l'application Android
            broadcast(new \App\Events\TaskMoved($task));
        }
    }

    // Cette fonction est le standard absolu de Livewire Volt pour passer des données fraîches à la vue
    public function with(): array
    {
        return [
            'tasks' => Task::all()
        ];
    }
};
?>

<div class="container mx-auto p-6">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" wire:ignore>
        
        <div class="bg-gray-100 p-4 rounded shadow">
            <h3 class="font-bold text-lg mb-4 text-gray-700">📌 À faire</h3>
            <div id="todo" class="kanban-list space-y-2 min-h-[300px]">
                @foreach($tasks->where('status', 'todo') as $task)
                    <div data-id="{{ $task->id }}" class="bg-white p-3 rounded shadow cursor-pointer border-l-4 border-blue-500">
                        <strong>{{ $task->title }}</strong>
                        <p class="text-sm text-gray-500">{{ $task->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-100 p-4 rounded shadow">
            <h3 class="font-bold text-lg mb-4 text-gray-700">⚡ En cours</h3>
            <div id="in_progress" class="kanban-list space-y-2 min-h-[300px]">
                @foreach($tasks->where('status', 'in_progress') as $task)
                    <div data-id="{{ $task->id }}" class="bg-white p-3 rounded shadow cursor-pointer border-l-4 border-yellow-500">
                        <strong>{{ $task->title }}</strong>
                        <p class="text-sm text-gray-500">{{ $task->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-gray-100 p-4 rounded shadow">
            <h3 class="font-bold text-lg mb-4 text-gray-700">✅ Terminé</h3>
            <div id="done" class="kanban-list space-y-2 min-h-[300px]">
                @foreach($tasks->where('status', 'done') as $task)
                    <div data-id="{{ $task->id }}" class="bg-white p-3 rounded shadow cursor-pointer border-l-4 border-green-500">
                        <strong>{{ $task->title }}</strong>
                        <p class="text-sm text-gray-500">{{ $task->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('livewire:navigated', () => {
            initSortable();
        });

        initSortable();

        function initSortable() {
            document.querySelectorAll('.kanban-list').forEach(el => {
                new Sortable(el, {
                    group: 'kanban',
                    animation: 150,
                    onEnd: function (evt) {
                        let taskId = evt.item.getAttribute('data-id');
                        let newStatus = evt.to.id;
                        
                        // Envoi de l'information en tâche de fond au PHP
                        Livewire.dispatch('taskMoved', { id: taskId, status: newStatus });
                    }
                });
            });
        }
    </script>
</div>