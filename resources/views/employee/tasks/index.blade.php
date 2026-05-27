@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">My Tasks</h4>
        <p class="text-muted mb-0">Track your assignments and progress</p>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @php
        $pendingCount = $tasks->whereIn('status', ['pending', 'assigned'])->count();
        $inProgressCount = $tasks->where('status', 'in_progress')->count();
        $completedCount = $tasks->where('status', 'completed')->count();
        $overdueCount = $tasks->filter(fn($t) => $t->due_date && \Carbon\Carbon::parse($t->due_date)->isPast() && !in_array($t->status, ['completed', 'cancelled']))->count();
    @endphp
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-warning border-4">
            <div class="fs-2 fw-bold text-warning">{{ $pendingCount }}</div>
            <div class="text-muted">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-primary border-4">
            <div class="fs-2 fw-bold text-primary">{{ $inProgressCount }}</div>
            <div class="text-muted">In Progress</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-success border-4">
            <div class="fs-2 fw-bold text-success">{{ $completedCount }}</div>
            <div class="text-muted">Completed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-danger border-4">
            <div class="fs-2 fw-bold text-danger">{{ $overdueCount }}</div>
            <div class="text-muted">Overdue</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employee.tasks.index') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                    placeholder="Search tasks..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(['pending', 'assigned', 'in_progress', 'review', 'completed'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>
                            {{ ucwords(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="priority" class="form-select">
                    <option value="">All Priority</option>
                    @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                        <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                    <a href="{{ route('employee.tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Task List --}}
<div class="row g-3">
    @forelse($tasks as $task)
    @php
        $isOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast()
            && !in_array($task->status, ['completed', 'cancelled']);
        $priorityColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'];
        $statusColors = ['pending' => 'secondary', 'assigned' => 'info', 'in_progress' => 'primary', 'review' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'];
    @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 {{ $isOverdue ? 'border border-danger' : '' }}"
            style="border-left: 4px solid var(--bs-{{ $priorityColors[$task->priority] ?? 'secondary' }}) !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-semibold mb-0">{{ Str::limit($task->title, 45) }}</h6>
                    <span class="badge bg-{{ $priorityColors[$task->priority] ?? 'secondary' }} ms-2">
                        {{ ucfirst($task->priority) }}
                    </span>
                </div>

                @if($task->description)
                <p class="text-muted small mb-2">{{ Str::limit($task->description, 80) }}</p>
                @endif

                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Progress</span>
                        <span>{{ $task->progress ?? 0 }}%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-{{ $statusColors[$task->status ?? 'pending'] }}"
                            style="width: {{ $task->progress ?? 0 }}%"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-{{ $statusColors[$task->status ?? 'pending'] }}">
                        {{ ucwords(str_replace('_', ' ', $task->status ?? 'pending')) }}
                    </span>
                    @if($task->due_date)
                    <small class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-muted' }}">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                    </small>
                    @endif
                </div>

                @if($task->estimated_hours)
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Est: {{ $task->estimated_hours }}h
                        @if($task->actual_hours) | Actual: {{ number_format($task->actual_hours, 1) }}h @endif
                    </small>
                </div>
                @endif
            </div>
            <div class="card-footer bg-transparent d-flex gap-2">
                <a href="{{ route('employee.tasks.show', $task) }}" class="btn btn-sm btn-outline-primary flex-fill">
                    <i class="bi bi-eye me-1"></i>View
                </a>
                @if(!in_array($task->status, ['completed', 'cancelled']))
                <button class="btn btn-sm btn-outline-success"
                    onclick="updateTaskStatus({{ $task->id }}, 'in_progress')"
                    title="Mark In Progress"
                    @if($task->status === 'in_progress') disabled @endif>
                    <i class="bi bi-play-fill"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5 text-muted">
            <i class="bi bi-list-task fs-1 d-block mb-2"></i>
            No tasks assigned to you
        </div>
    </div>
    @endforelse
</div>

@if($tasks->hasPages())
<div class="mt-4">{{ $tasks->withQueryString()->links() }}</div>
@endif
@endsection

@push('scripts')
<script>
function updateTaskStatus(id, status) {
    fetch(`/employee/tasks/${id}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({ status })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            APP.toast('Task status updated!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            APP.toast(data.message || 'Error', 'danger');
        }
    });
}
</script>
@endpush
