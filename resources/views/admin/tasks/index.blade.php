@extends('layouts.app')

@section('title', 'Task Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Task Management</h4>
        <p class="text-muted mb-0">Manage and assign tasks to employees</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.tasks.kanban') }}" class="btn btn-outline-secondary">
            <i class="bi bi-kanban me-1"></i>Kanban View
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
            <i class="bi bi-plus-circle me-1"></i>New Task
        </button>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @php
        $taskStats = [
            ['label' => 'Total', 'count' => $tasks->total(), 'color' => 'primary'],
            ['label' => 'In Progress', 'count' => $tasks->where('status', 'in_progress')->count(), 'color' => 'info'],
            ['label' => 'Completed', 'count' => $tasks->where('status', 'completed')->count(), 'color' => 'success'],
            ['label' => 'Overdue', 'count' => $tasks->filter(fn($t) => $t->due_date && \Carbon\Carbon::parse($t->due_date)->isPast() && !in_array($t->status, ['completed', 'cancelled']))->count(), 'color' => 'danger'],
        ];
    @endphp
    @foreach($taskStats as $stat)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-{{ $stat['color'] }} border-4">
            <div class="fs-2 fw-bold text-{{ $stat['color'] }}">{{ $stat['count'] }}</div>
            <div class="text-muted">{{ $stat['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.tasks.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search tasks..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(['pending', 'assigned', 'in_progress', 'review', 'completed', 'cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>
                            {{ ucwords(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priority</option>
                    @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                        <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="employee" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees ?? [] as $emp)
                        <option value="{{ $emp->id }}" @selected(request('employee') == $emp->id)>
                            {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Task Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Task</th>
                        <th>Assigned To</th>
                        <th>Priority</th>
                        <th>Progress</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    @php
                        $isOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast()
                            && !in_array($task->status, ['completed', 'cancelled']);
                    @endphp
                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                        <td>
                            <div class="fw-semibold">{{ Str::limit($task->title, 40) }}</div>
                            <small class="text-muted"><code>{{ $task->task_id }}</code></small>
                        </td>
                        <td>
                            @if($task->assignedEmployee)
                            <div class="d-flex align-items-center gap-1">
                                <img src="{{ $task->assignedEmployee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($task->assignedEmployee->full_name).'&size=24&background=6366f1&color=fff' }}"
                                    class="rounded-circle" width="24" height="24">
                                <small>{{ $task->assignedEmployee->full_name }}</small>
                            </div>
                            @else <span class="text-muted">Unassigned</span> @endif
                        </td>
                        <td>
                            @php
                                $pColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $pColors[$task->priority] ?? 'secondary' }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress flex-fill" style="height: 6px; min-width: 60px;">
                                    <div class="progress-bar" style="width: {{ $task->progress ?? 0 }}%"></div>
                                </div>
                                <small>{{ $task->progress ?? 0 }}%</small>
                            </div>
                        </td>
                        <td>
                            @if($task->due_date)
                                <span class="{{ $isOverdue ? 'text-danger fw-semibold' : '' }}">
                                    {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                </span>
                            @else — @endif
                        </td>
                        <td>
                            @php
                                $sColors = ['pending' => 'secondary', 'assigned' => 'info', 'in_progress' => 'primary', 'review' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'];
                                $ts = $task->status ?? 'pending';
                            @endphp
                            <span class="badge bg-{{ $sColors[$ts] ?? 'secondary' }}">
                                {{ ucwords(str_replace('_', ' ', $ts)) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                @if(in_array($task->status, ['review', 'in_progress']))
                                <form action="{{ route('admin.tasks.approve', $task) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                                @endif
                                <button class="btn btn-outline-primary btn-sm"
                                    onclick="editTask({{ $task->id }})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm"
                                    onclick="deleteTask({{ $task->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-list-task fs-1 d-block mb-2"></i>
                            No tasks found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tasks->hasPages())
    <div class="card-footer bg-transparent">
        {{ $tasks->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Create Task Modal --}}
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Task Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Select Employee</option>
                                @foreach($employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimated Hours</label>
                            <input type="number" name="estimated_hours" class="form-control" step="0.5" min="0">
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
function deleteTask(id) {
    APP.confirm('Delete this task?', function() {
        fetch(`/admin/tasks/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                APP.toast('Task deleted!', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        });
    });
}
</script>
@endpush
