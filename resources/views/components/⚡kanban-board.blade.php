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

<style>
    /* =====================================================
       KANBAN BOARD – MODERN DARK DESIGN
       ===================================================== */

    .kanban-wrapper {
        padding: 0 30px 60px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Stats row */
    .stats-row {
        display: flex;
        gap: 16px;
        margin-bottom: 36px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .stat-chip {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        border-radius: 50px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.6);
        transition: all 0.3s ease;
    }

    .stat-chip:hover {
        background: rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.9);
        transform: translateY(-1px);
    }

    .stat-chip .stat-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .stat-chip .stat-count {
        font-weight: 700;
        color: white;
        font-size: 14px;
    }

    /* Columns grid */
    .kanban-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    @media (max-width: 900px) {
        .kanban-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Column container */
    .kanban-column {
        display: flex;
        flex-direction: column;
        border-radius: 20px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        transition: box-shadow 0.3s ease;
        min-height: 500px;
    }

    .kanban-column:hover {
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.3);
    }

    /* Column header */
    .column-header {
        padding: 22px 22px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        position: relative;
    }

    .column-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: 20px 20px 0 0;
    }

    /* Todo column accent */
    .col-todo .column-header::before {
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
    }
    .col-todo .col-icon {
        background: rgba(99, 102, 241, 0.15);
        border-color: rgba(99, 102, 241, 0.3);
        color: #818cf8;
    }
    .col-todo .col-badge {
        background: rgba(99, 102, 241, 0.15);
        border-color: rgba(99, 102, 241, 0.25);
        color: #a5b4fc;
    }

    /* In Progress column accent */
    .col-inprogress .column-header::before {
        background: linear-gradient(90deg, #f59e0b, #fb923c);
    }
    .col-inprogress .col-icon {
        background: rgba(245, 158, 11, 0.15);
        border-color: rgba(245, 158, 11, 0.3);
        color: #fbbf24;
    }
    .col-inprogress .col-badge {
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(245, 158, 11, 0.25);
        color: #fcd34d;
    }

    /* Done column accent */
    .col-done .column-header::before {
        background: linear-gradient(90deg, #10b981, #34d399);
    }
    .col-done .col-icon {
        background: rgba(16, 185, 129, 0.15);
        border-color: rgba(16, 185, 129, 0.3);
        color: #34d399;
    }
    .col-done .col-badge {
        background: rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.25);
        color: #6ee7b7;
    }

    .col-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .col-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .col-title {
        font-size: 15px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.9);
        letter-spacing: -0.3px;
    }

    .col-badge {
        font-size: 12px;
        font-weight: 600;
        border: 1px solid;
        border-radius: 20px;
        padding: 3px 10px;
        min-width: 28px;
        text-align: center;
    }

    /* Column body */
    .column-body {
        flex: 1;
        padding: 14px 14px 14px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    /* Kanban list (drop zone) */
    .kanban-list {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-height: 300px;
        padding: 4px 2px;
        transition: background 0.25s ease;
        border-radius: 12px;
    }

    .kanban-list.drag-over {
        background: rgba(255, 255, 255, 0.04);
    }

    /* Task card */
    .task-card {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        padding: 16px;
        cursor: grab;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .task-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, transparent 100%);
        pointer-events: none;
    }

    .task-card:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.18);
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
    }

    .task-card:active {
        cursor: grabbing;
        transform: rotate(2deg) scale(1.02);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    /* Todo card left border */
    .col-todo .task-card {
        border-left: 3px solid rgba(99, 102, 241, 0.6);
    }
    .col-todo .task-card:hover {
        border-left-color: #818cf8;
    }

    /* In Progress card left border */
    .col-inprogress .task-card {
        border-left: 3px solid rgba(245, 158, 11, 0.6);
    }
    .col-inprogress .task-card:hover {
        border-left-color: #fbbf24;
    }

    /* Done card left border */
    .col-done .task-card {
        border-left: 3px solid rgba(16, 185, 129, 0.6);
    }
    .col-done .task-card:hover {
        border-left-color: #34d399;
    }

    .task-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
    }

    .task-title {
        font-size: 14px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.92);
        line-height: 1.4;
        letter-spacing: -0.2px;
    }

    .task-drag-handle {
        flex-shrink: 0;
        color: rgba(255, 255, 255, 0.2);
        font-size: 12px;
        transition: color 0.2s;
    }

    .task-card:hover .task-drag-handle {
        color: rgba(255, 255, 255, 0.5);
    }

    .task-desc {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.38);
        line-height: 1.6;
        font-weight: 400;
    }

    /* Empty state */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 140px;
        gap: 8px;
        opacity: 0.3;
        pointer-events: none;
    }

    .empty-icon {
        font-size: 28px;
        filter: grayscale(1);
    }

    .empty-text {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.5);
        text-align: center;
    }

    /* Sortable ghost */
    .sortable-ghost {
        opacity: 0.3;
        transform: scale(0.97);
    }

    /* Sortable chosen */
    .sortable-chosen {
        opacity: 0.95;
    }

    /* Sortable drag */
    .sortable-drag {
        opacity: 1;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6) !important;
    }

    /* Fade in animation for cards */
    @keyframes cardFadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .task-card {
        animation: cardFadeIn 0.3s ease forwards;
    }
</style>

<div class="kanban-wrapper">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    {{-- Stats row --}}
    <div class="stats-row">
        <div class="stat-chip">
            <span class="stat-dot" style="background: #818cf8;"></span>
            <span>À faire</span>
            <span class="stat-count">{{ $tasks->where('status', 'todo')->count() }}</span>
        </div>
        <div class="stat-chip">
            <span class="stat-dot" style="background: #fbbf24;"></span>
            <span>En cours</span>
            <span class="stat-count">{{ $tasks->where('status', 'in_progress')->count() }}</span>
        </div>
        <div class="stat-chip">
            <span class="stat-dot" style="background: #34d399;"></span>
            <span>Terminé</span>
            <span class="stat-count">{{ $tasks->where('status', 'done')->count() }}</span>
        </div>
        <div class="stat-chip">
            <span style="font-size:14px;">📋</span>
            <span>Total</span>
            <span class="stat-count">{{ $tasks->count() }}</span>
        </div>
    </div>

    {{-- Kanban columns --}}
    <div class="kanban-grid" wire:ignore>

        {{-- À faire --}}
        <div class="kanban-column col-todo">
            <div class="column-header">
                <div class="col-header-left">
                    <div class="col-icon">📌</div>
                    <span class="col-title">À faire</span>
                </div>
                <span class="col-badge">{{ $tasks->where('status', 'todo')->count() }}</span>
            </div>
            <div class="column-body">
                <div id="todo" class="kanban-list">
                    @forelse($tasks->where('status', 'todo') as $task)
                        <div data-id="{{ $task->id }}" class="task-card">
                            <div class="task-card-header">
                                <strong class="task-title">{{ $task->title }}</strong>
                                <span class="task-drag-handle">⠿</span>
                            </div>
                            @if($task->description)
                                <p class="task-desc">{{ $task->description }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">
                            <span class="empty-icon">📭</span>
                            <span class="empty-text">Aucune tâche</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- En cours --}}
        <div class="kanban-column col-inprogress">
            <div class="column-header">
                <div class="col-header-left">
                    <div class="col-icon">⚡</div>
                    <span class="col-title">En cours</span>
                </div>
                <span class="col-badge">{{ $tasks->where('status', 'in_progress')->count() }}</span>
            </div>
            <div class="column-body">
                <div id="in_progress" class="kanban-list">
                    @forelse($tasks->where('status', 'in_progress') as $task)
                        <div data-id="{{ $task->id }}" class="task-card">
                            <div class="task-card-header">
                                <strong class="task-title">{{ $task->title }}</strong>
                                <span class="task-drag-handle">⠿</span>
                            </div>
                            @if($task->description)
                                <p class="task-desc">{{ $task->description }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">
                            <span class="empty-icon">⏳</span>
                            <span class="empty-text">Aucune tâche en cours</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Terminé --}}
        <div class="kanban-column col-done">
            <div class="column-header">
                <div class="col-header-left">
                    <div class="col-icon">✅</div>
                    <span class="col-title">Terminé</span>
                </div>
                <span class="col-badge">{{ $tasks->where('status', 'done')->count() }}</span>
            </div>
            <div class="column-body">
                <div id="done" class="kanban-list">
                    @forelse($tasks->where('status', 'done') as $task)
                        <div data-id="{{ $task->id }}" class="task-card">
                            <div class="task-card-header">
                                <strong class="task-title">{{ $task->title }}</strong>
                                <span class="task-drag-handle">⠿</span>
                            </div>
                            @if($task->description)
                                <p class="task-desc">{{ $task->description }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">
                            <span class="empty-icon">🎉</span>
                            <span class="empty-text">Aucune tâche terminée</span>
                        </div>
                    @endforelse
                </div>
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
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onStart: function(evt) {
                        // highlight drop zones
                        document.querySelectorAll('.kanban-list').forEach(list => {
                            if (list !== evt.from) {
                                list.classList.add('drag-over');
                            }
                        });
                    },
                    onEnd: function (evt) {
                        // remove highlight
                        document.querySelectorAll('.kanban-list').forEach(list => {
                            list.classList.remove('drag-over');
                        });

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