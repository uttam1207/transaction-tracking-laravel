@extends('layouts.app')

@section('title', 'Kanban Board')

@push('styles')
<style>
.kanban-board { display: flex; gap: 16px; overflow-x: auto; padding-bottom: 16px; }
.kanban-col { min-width: 280px; max-width: 280px; }
.kanban-col-header { padding: 12px 16px; border-radius: 8px 8px 0 0; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
.kanban-cards { background: var(--bs-light); border-radius: 0 0 8px 8px; min-height: 300px; padding: 8px; }
.kanban-card { background: white; border-radius: 8px; padding: 12px; margin-bottom: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); cursor: pointer; border-left: 3px solid transparent; transition: all .2s; }
.kanban-card:hover { box-shadow: 0 3px 8px rgba(0,0,0,.15); transform: translateY(-1px); }
.kanban-card.priority-urgent, .kanban-card.priority-high { border-left-color: #dc3545; }
.kanban-card.priority-medium { border-left-color: #ffc107; }
.kanban-card.priority-low { border-left-color: #198754; }
[data-bs-theme="dark"] .kanban-cards { background: var(--bs-gray-800); }
[data-bs-theme="dark"] .kanban-card { background: var(--bs-gray-700); }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Kanban Board</h4>
        <p class="text-muted mb-0">Visual task management</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-list me-1"></i>List View
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
            <i class="bi bi-plus-circle me-1"></i>New Task
        </button>
    </div>
</div>

<div class="kanban-board">
    @php
        $columns = [
            'pending' => ['label' => 'Pending', 'color' => 'secondary'],
            'assigned' => ['label' => 'Assigned', 'color' => 'info'],
            'in_progress' => ['label' => 'In Progress', 'color' => 'primary'],
            'review' => ['label' => 'In Review', 'color' => 'warning'],
            'completed' => ['label' => 'Completed', 'color' => 'success'],
        ];
    @endphp

    @foreach($columns as $status => $col)
    <div class="kanban-col">
        <div class="kanban-col-header bg-{{ $col['color'] }} {{ in_array($col['color'], ['warning', 'info']) ? 'text-dark' : 'text-white' }}">
            <span>{{ $col['label'] }}</span>
            <span class="badge bg-white text-dark">
                {{ ($grouped[$status] ?? collect())->count() }}
            </span>
        </div>
        <div class="kanban-cards" id="col-{{ $status }}">
            @forelse($grouped[$status] ?? [] as $task)
            <div class="kanban-card priority-{{ $task->priority }}"
                data-bs-toggle="modal" data-bs-target="#taskDetailModal"
                data-id="{{ $task->id }}" data-title="{{ $task->title }}"
                data-status="{{ $task->status }}" data-priority="{{ $task->priority }}"
                data-progress="{{ $task->progress ?? 0 }}" data-due="{{ $task->due_date ?? '' }}"
                data-employee="{{ $task->assignedEmployee->full_name ?? 'Unassigned' }}">
                <div class="fw-semibold small mb-1">{{ Str::limit($task->title, 40) }}</div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    @php $pColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger']; @endphp
                    <span class="badge bg-{{ $pColors[$task->priority] ?? 'secondary' }} badge-sm">
                        {{ ucfirst($task->priority) }}
                    </span>
                    @if($task->due_date)
                    <small class="{{ \Carbon\Carbon::parse($task->due_date)->isPast() ? 'text-danger' : 'text-muted' }}">
                        <i class="bi bi-calendar3"></i>
                        {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                    </small>
                    @endif
                </div>
                @if($task->progress)
                <div class="progress mb-2" style="height: 4px;">
                    <div class="progress-bar" style="width: {{ $task->progress }}%"></div>
                </div>
                @endif
                @if($task->assignedEmployee)
                <div class="d-flex align-items-center gap-1">
                    <img src="{{ $task->assignedEmployee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($task->assignedEmployee->full_name).'&size=20&background=6366f1&color=fff' }}"
                        class="rounded-circle" width="20" height="20">
                    <small class="text-muted">{{ $task->assignedEmployee->full_name }}</small>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center text-muted py-4 small">
                <i class="bi bi-inbox"></i> No tasks
            </div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

{{-- Task Detail Modal --}}
<div class="modal fade" id="taskDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tdTitle">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="text-muted small">Priority</label>
                        <div id="tdPriority"></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small">Status</label>
                        <div id="tdStatus"></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small">Assigned To</label>
                        <div id="tdEmployee"></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small">Due Date</label>
                        <div id="tdDue"></div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Progress</label>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" id="tdProgress"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="tdApproveBtn" href="#" class="btn btn-success d-none">
                    <i class="bi bi-check2 me-1"></i>Approve
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Create Task Modal --}}
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Select Employee</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('taskDetailModal').addEventListener('show.bs.modal', function(e) {
    const card = e.relatedTarget;
    if (!card) return;
    document.getElementById('tdTitle').textContent = card.dataset.title;
    document.getElementById('tdEmployee').textContent = card.dataset.employee;
    document.getElementById('tdDue').textContent = card.dataset.due || '—';
    document.getElementById('tdPriority').innerHTML = `<span class="badge bg-secondary">${card.dataset.priority}</span>`;
    document.getElementById('tdStatus').innerHTML = `<span class="badge bg-primary">${card.dataset.status.replace('_', ' ')}</span>`;
    const prog = document.getElementById('tdProgress');
    prog.style.width = card.dataset.progress + '%';
    prog.textContent = card.dataset.progress + '%';

    const approveBtn = document.getElementById('tdApproveBtn');
    if (['review', 'in_progress'].includes(card.dataset.status)) {
        approveBtn.href = `/admin/tasks/${card.dataset.id}/approve`;
        approveBtn.classList.remove('d-none');
    } else {
        approveBtn.classList.add('d-none');
    }
});
</script>
@endpush
