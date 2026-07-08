@extends('layouts.app')
@section('title', 'My Tasks')

@section('content')

@php
    $pendingCount    = $stats['pending'];
    $inProgressCount = $stats['in_progress'];
    $completedCount  = $stats['completed'];
    $overdueCount    = $stats['overdue'];
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>My Tasks</h4>
            <p>Track your assignments and progress</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat"><div class="v" style="color:#fde047;">{{ $pendingCount }}</div><div class="l">Pending</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#93c5fd;">{{ $inProgressCount }}</div><div class="l">In Progress</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#86efac;">{{ $completedCount }}</div><div class="l">Completed</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#fca5a5;">{{ $overdueCount }}</div><div class="l">Overdue</div></div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('employee.tasks.index') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="flabel">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Search tasks…"
                value="{{ request('search') }}"
                style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
        </div>
        <div class="col-md-3">
            <label class="flabel">Status</label>
            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Status</option>
                @foreach(['pending','assigned','in_progress','review','completed'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Priority</label>
            <select name="priority" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Priority</option>
                @foreach(['low','medium','high','urgent'] as $p)
                    <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        </div>
        <div class="col-md-auto">
            <a href="{{ route('employee.tasks.index') }}" class="btn btn-sm btn-outline-secondary px-3">Reset</a>
        </div>
    </form>
</div>

{{-- Task Cards Grid --}}
<div class="row g-3">
    @forelse($tasks as $task)
    @php
        $isOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast()
            && !in_array($task->status, ['completed', 'cancelled']);
        $pMap = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
        $sMap = ['pending'=>'secondary','assigned'=>'info','in_progress'=>'processing','review'=>'warning','completed'=>'success','cancelled'=>'cancelled'];
        $pColor = $pMap[$task->priority] ?? 'secondary';
        $sColor = $sMap[$task->status ?? 'pending'] ?? 'secondary';
        $borderColor = match($task->priority) { 'urgent','high' => '#dc2626', 'medium' => '#f59e0b', default => '#16a34a' };
    @endphp
    <div class="col-md-6 col-lg-4">
        <div style="background:#fff;border-radius:14px;border:1.5px solid {{ $isOverdue ? '#fecaca' : '#f0f0f5' }};border-left:4px solid {{ $borderColor }};padding:18px;height:100%;display:flex;flex-direction:column;{{ $isOverdue ? 'background:#fff5f5;' : '' }}">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                <div style="font-weight:700;font-size:.9rem;color:#111827;line-height:1.3;">{{ Str::limit($task->title, 45) }}</div>
                <span class="spill spill-{{ $pColor }}" style="font-size:.7rem;flex-shrink:0;">{{ ucfirst($task->priority) }}</span>
            </div>

            @if($task->description)
            <p style="font-size:.8rem;color:#6b7280;margin-bottom:10px;flex:1;">{{ Str::limit($task->description, 80) }}</p>
            @endif

            {{-- Progress --}}
            <div style="margin-bottom:10px;">
                <div class="d-flex justify-content-between" style="font-size:.75rem;color:#9ca3af;margin-bottom:4px;">
                    <span>Progress</span>
                    <span style="font-weight:700;color:#6366f1;">{{ $task->progress ?? 0 }}%</span>
                </div>
                <div style="height:5px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                    <div style="width:{{ $task->progress ?? 0 }}%;height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:3px;"></div>
                </div>
            </div>

            {{-- Status + Due --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="spill spill-{{ $sColor }}" style="font-size:.7rem;">{{ ucwords(str_replace('_',' ',$task->status ?? 'pending')) }}</span>
                @if($task->due_date)
                <span style="font-size:.75rem;{{ $isOverdue ? 'color:#dc2626;font-weight:700;' : 'color:#9ca3af;' }}">
                    <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                    @if($isOverdue) <span class="spill spill-danger" style="font-size:.65rem;padding:1px 5px;margin-left:2px;">Overdue</span> @endif
                </span>
                @endif
            </div>

            @if($task->estimated_hours)
            <div style="font-size:.75rem;color:#9ca3af;margin-bottom:10px;">
                <i class="bi bi-clock me-1"></i>Est: {{ $task->estimated_hours }}h
                @if($task->actual_hours) &bull; Actual: {{ number_format($task->actual_hours,1) }}h @endif
            </div>
            @endif

            {{-- Actions --}}
            <div class="d-flex gap-2" style="margin-top:auto;">
                <a href="{{ route('employee.tasks.show', $task) }}" class="act-btn act-view flex-fill" style="border-radius:8px;justify-content:center;gap:6px;">
                    <i class="bi bi-eye"></i> View
                </a>
                @if(!in_array($task->status, ['completed','cancelled']))
                <button class="act-btn act-green" title="Mark In Progress"
                    onclick="updateTaskStatus({{ $task->id }}, 'in_progress')"
                    style="border-radius:8px;"
                    {{ $task->status === 'in_progress' ? 'disabled' : '' }}>
                    <i class="bi bi-play-fill"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="empty-state"><i class="bi bi-list-task"></i><p>No tasks assigned to you</p></div>
    </div>
    @endforelse
</div>

@if($tasks->hasPages())
<div class="pagination-wrap mt-3">{{ $tasks->withQueryString()->links('pagination::bootstrap-5') }}</div>
@endif
@endsection

@push('scripts')
<script>
function updateTaskStatus(id, status) {
    fetch(`/employee/tasks/${id}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ status })
    }).then(r => r.json()).then(data => {
        if (data.success) { APP.toast('Task status updated!', 'success'); setTimeout(() => location.reload(), 1000); }
        else APP.toast(data.message || 'Error', 'error');
    });
}
</script>
@endpush
