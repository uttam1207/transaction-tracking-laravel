@extends('layouts.app')
@section('title', 'My Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Welcome Banner -->
<div class="card mb-4 border-0" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
    <div class="card-body py-4 px-4 text-white">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle border border-white border-2" width="56" height="56" alt="">
            <div>
                <h5 class="mb-0 fw-bold">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->name }}! 👋</h5>
                <div class="opacity-75 small">{{ $employee->designation }} &bull; {{ $employee->department?->name }}</div>
            </div>
            <div class="ms-auto text-end d-none d-md-block">
                <div class="small opacity-75">{{ now()->format('l, F j, Y') }}</div>
                <div class="h5 mb-0 fw-bold" id="clock">{{ now()->format('H:i:s') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Check-In/Out Card -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center py-4">
                @if(!$data['is_checked_in'] && !$data['check_in_time'])
                    <div class="text-muted mb-3">
                        <i class="bi bi-clock fs-1 opacity-25"></i>
                        <div class="small mt-2">Not checked in yet</div>
                    </div>
                    <button class="btn btn-success px-4 fw-semibold" onclick="checkIn()">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Check In
                    </button>
                @elseif($data['is_checked_in'])
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle-fill fs-1"></i>
                    </div>
                    <div class="small text-muted mb-1">Checked in at</div>
                    <div class="h4 fw-bold text-success">{{ $data['check_in_time']->format('H:i') }}</div>
                    <div id="workTimer" class="small text-muted mb-3">Working...</div>
                    <button class="btn btn-outline-danger px-4 fw-semibold" onclick="checkOut()">
                        <i class="bi bi-box-arrow-right me-2"></i>Check Out
                    </button>
                @else
                    <div class="text-info mb-2">
                        <i class="bi bi-check-all fs-1"></i>
                    </div>
                    <div class="small text-muted">Completed today</div>
                    <div class="h5 fw-bold">{{ $data['work_hours_today'] }}h worked</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-8">
        <div class="row g-3 h-100">
            <div class="col-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="small text-muted">Pending Tasks</div>
                                <h4 class="fw-bold mb-0 mt-1">{{ $data['pending_tasks'] }}</h4>
                            </div>
                            <div class="stat-icon bg-warning-subtle text-warning" style="width:40px;height:40px;font-size:1rem;">
                                <i class="bi bi-list-task"></i>
                            </div>
                        </div>
                        <a href="{{ route('employee.tasks.index') }}" class="small text-primary text-decoration-none">View tasks →</a>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="small text-muted">Completed This Month</div>
                                <h4 class="fw-bold mb-0 mt-1">{{ $data['completed_tasks_month'] }}</h4>
                            </div>
                            <div class="stat-icon bg-success-subtle text-success" style="width:40px;height:40px;font-size:1rem;">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1">Performance Score</div>
                        <h4 class="fw-bold mb-1">{{ $data['performance_score'] }}%</h4>
                        <div class="progress" style="height:4px;">
                            <div class="progress-bar bg-success" style="width: {{ $data['performance_score'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="small text-muted mb-1">Leave Balance</div>
                        <div class="d-flex gap-3">
                            <div>
                                <div class="h5 fw-bold mb-0 text-success">{{ $data['leave_balance']['annual'] }}</div>
                                <div class="smaller text-muted">Annual</div>
                            </div>
                            <div>
                                <div class="h5 fw-bold mb-0 text-info">{{ $data['leave_balance']['sick'] }}</div>
                                <div class="smaller text-muted">Sick</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tasks & Attendance -->
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">My Recent Tasks</h6>
                <a href="{{ route('employee.tasks.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            @forelse($recentTasks as $task)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('employee.tasks.show', $task) }}" class="text-decoration-none fw-semibold small">
                                        {{ Str::limit($task->title, 40) }}
                                    </a>
                                    <div class="small text-muted">{{ $task->project?->name ?? 'No Project' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->priority_badge }}-subtle text-{{ $task->priority_badge }}">{{ $task->priority }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->status_badge }}-subtle text-{{ $task->status_badge }}">{{ str_replace('_', ' ', $task->status) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="progress flex-grow-1" style="height:4px;min-width:50px;">
                                            <div class="progress-bar" style="width:{{ $task->progress }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $task->progress }}%</small>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No tasks assigned</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">Recent Attendance</h6>
                <a href="{{ route('employee.attendance.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <tbody>
                            @forelse($recentAttendance as $att)
                            <tr>
                                <td class="ps-3">
                                    <div class="small fw-semibold">{{ $att->date->format('M d') }}</div>
                                    <div class="smaller text-muted">{{ $att->date->format('l') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $att->status === 'present' ? 'success' : ($att->status === 'late' ? 'warning' : ($att->status === 'on_leave' ? 'info' : 'danger')) }}-subtle text-{{ $att->status === 'present' ? 'success' : ($att->status === 'late' ? 'warning' : ($att->status === 'on_leave' ? 'info' : 'danger')) }}">
                                        {{ ucfirst($att->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small">{{ $att->check_in?->format('H:i') ?? '--' }} - {{ $att->check_out?->format('H:i') ?? '--' }}</div>
                                    <div class="smaller text-muted">{{ $att->work_hours }}h</div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No attendance records</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Live clock
setInterval(() => {
    document.getElementById('clock').textContent = new Date().toLocaleTimeString('en-US', { hour12: false });
}, 1000);

@if($data['is_checked_in'] && $data['check_in_time'])
// Work hours timer
const checkInTime = new Date('{{ $data['check_in_time']->toISOString() }}');
setInterval(() => {
    const now = new Date();
    const diff = now - checkInTime;
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    document.getElementById('workTimer').textContent = `${h}h ${m}m ${s}s`;
}, 1000);
@endif

function checkIn() {
    APP.ajax('/employee/attendance/check-in', 'POST')
        .done(res => {
            if (res.success) { APP.toast('Checked in at ' + res.check_in + '!'); setTimeout(() => location.reload(), 1000); }
        })
        .fail(err => APP.toast(err.responseJSON?.message || 'Failed', 'error'));
}

function checkOut() {
    APP.confirm('Check Out', 'Are you sure you want to check out?', () => {
        APP.ajax('/employee/attendance/check-out', 'POST')
            .done(res => {
                if (res.success) { APP.toast(`Checked out! Worked ${res.work_hours}h`); setTimeout(() => location.reload(), 1000); }
            })
            .fail(err => APP.toast(err.responseJSON?.message || 'Failed', 'error'));
    });
}
</script>
@endpush
