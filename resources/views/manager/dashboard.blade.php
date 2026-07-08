@extends('layouts.app')
@section('title', 'Manager Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Manager Dashboard</li>
@endsection

@section('content')

{{-- Welcome Hero --}}
<div style="background:linear-gradient(135deg,#0f766e,#0891b2);border-radius:16px;padding:28px 32px;color:#fff;margin-bottom:24px;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;background:rgba(255,255,255,.06);border-radius:50%;"></div>
    <div style="position:absolute;bottom:-60px;right:60px;width:140px;height:140px;background:rgba(255,255,255,.04);border-radius:50%;"></div>
    <div class="d-flex align-items-center gap-3 flex-wrap" style="position:relative;z-index:1;">
        <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle"
            style="width:56px;height:56px;border:2.5px solid rgba(255,255,255,.5);object-fit:cover;" alt="">
        <div>
            <h4 style="margin:0;font-weight:800;font-size:1.3rem;">
                Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }},
                {{ explode(' ', auth()->user()->name)[0] }}!
            </h4>
            <div style="opacity:.75;font-size:.85rem;margin-top:2px;">
                {{ $employee->designation }} &bull; {{ $employee->department?->name }}
                <span style="opacity:.6;">&bull; Manager</span>
            </div>
        </div>
        <div class="ms-auto text-end d-none d-md-block">
            <div style="opacity:.7;font-size:.8rem;">{{ now()->format('l, F j, Y') }}</div>
            <div style="font-size:1.4rem;font-weight:800;font-family:monospace;" id="clock">{{ now()->format('H:i:s') }}</div>
        </div>
    </div>
</div>

{{-- Top Row: My Check-In + Personal Stats --}}
<div class="row g-3 mb-4">

    {{-- Check-In/Out --}}
    <div class="col-md-3">
        <div class="info-card h-100 text-center" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:28px 16px;">
            @if(!$isCheckedIn && !$isDayComplete)
                <div style="margin-bottom:16px;">
                    <i class="bi bi-clock" style="font-size:2.2rem;color:#d1d5db;"></i>
                    <div style="font-size:.8rem;color:#9ca3af;margin-top:8px;">Not checked in yet</div>
                </div>
                <button class="btn btn-sm" onclick="checkIn()"
                    style="background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border:none;border-radius:9px;font-weight:600;padding:9px 20px;font-size:.83rem;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Check In
                </button>
            @elseif($isCheckedIn)
                <div style="margin-bottom:10px;">
                    <i class="bi bi-check-circle-fill" style="font-size:2.2rem;color:#16a34a;"></i>
                </div>
                <div style="font-size:.75rem;color:#9ca3af;margin-bottom:2px;">Checked in at</div>
                <div style="font-size:1.5rem;font-weight:800;color:#16a34a;font-family:monospace;">
                    {{ $todayAttendance->check_in->format('H:i') }}
                </div>
                <div id="workTimer" style="font-size:.75rem;color:#6b7280;margin:6px 0 14px;"></div>
                <button class="btn btn-sm" onclick="checkOut()"
                    style="background:transparent;color:#dc2626;border:1.5px solid #dc2626;border-radius:9px;font-weight:600;padding:7px 18px;font-size:.82rem;">
                    <i class="bi bi-box-arrow-right me-2"></i>Check Out
                </button>
            @else
                <div style="margin-bottom:10px;">
                    <i class="bi bi-check-all" style="font-size:2.2rem;color:#6366f1;"></i>
                </div>
                <div style="font-size:.8rem;color:#9ca3af;">Day Complete</div>
                <div style="font-size:1.2rem;font-weight:800;color:#111827;margin-top:4px;">
                    {{ $todayAttendance->work_hours }}h worked
                </div>
            @endif
        </div>
    </div>

    {{-- My Tasks --}}
    <div class="col-md-3">
        <div class="info-card h-100" style="border-left:4px solid #f59e0b;">
            <div class="info-card-body d-flex flex-column justify-content-between h-100" style="padding:20px;">
                <div>
                    <div style="font-size:.75rem;color:#9ca3af;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">My Tasks</div>
                    <div style="font-size:2rem;font-weight:800;color:#111827;line-height:1;">{{ $myPendingTasks }}</div>
                    <div style="font-size:.78rem;color:#9ca3af;margin-top:4px;">active tasks</div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3" style="border-top:1px solid #f3f4f6;padding-top:10px;">
                    <span style="font-size:.75rem;color:#6b7280;">Done this month: <strong style="color:#16a34a;">{{ $myCompletedMonth }}</strong></span>
                    <a href="{{ route('employee.tasks.index') }}" style="font-size:.75rem;color:#6366f1;text-decoration:none;">View →</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Leave Balance --}}
    <div class="col-md-3">
        <div class="info-card h-100" style="border-left:4px solid #0ea5e9;">
            <div class="info-card-body" style="padding:20px;">
                <div style="font-size:.75rem;color:#9ca3af;margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">My Leave Balance</div>
                <div class="d-flex gap-4">
                    <div>
                        <div style="font-size:1.8rem;font-weight:800;color:#16a34a;line-height:1;">{{ $employee->annual_leave_balance ?? 0 }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;margin-top:2px;">Annual</div>
                    </div>
                    <div>
                        <div style="font-size:1.8rem;font-weight:800;color:#0ea5e9;line-height:1;">{{ $employee->sick_leave_balance ?? 0 }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;margin-top:2px;">Sick</div>
                    </div>
                </div>
                <a href="{{ route('employee.attendance.leaves') }}" style="font-size:.75rem;color:#6366f1;text-decoration:none;margin-top:12px;display:inline-block;">My leaves →</a>
            </div>
        </div>
    </div>

    {{-- Performance --}}
    <div class="col-md-3">
        <div class="info-card h-100" style="border-left:4px solid #6366f1;">
            <div class="info-card-body" style="padding:20px;">
                <div style="font-size:.75rem;color:#9ca3af;margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;">My Performance</div>
                <div style="font-size:2rem;font-weight:800;color:#6366f1;line-height:1;">{{ $employee->performance_score ?? 0 }}%</div>
                <div style="height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden;margin-top:10px;">
                    <div style="width:{{ min($employee->performance_score ?? 0, 100) }}%;height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:3px;transition:width .6s;"></div>
                </div>
                <div style="font-size:.73rem;color:#9ca3af;margin-top:6px;">score out of 100</div>
            </div>
        </div>
    </div>
</div>

{{-- Team Overview Stats --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;font-weight:700;margin-bottom:8px;">Team Overview</div>
    </div>

    {{-- Total Team Members --}}
    <div class="col-6 col-md-3">
        <div class="info-card text-center" style="padding:20px 12px;">
            <div style="font-size:2rem;font-weight:800;color:#374151;line-height:1;">{{ $teamEmployees->count() }}</div>
            <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">Team Members</div>
            <i class="bi bi-people-fill" style="font-size:1.4rem;color:#e5e7eb;margin-top:8px;display:block;"></i>
        </div>
    </div>

    {{-- Present Today --}}
    <div class="col-6 col-md-3">
        <div class="info-card text-center" style="padding:20px 12px;border-bottom:3px solid #16a34a;">
            <div style="font-size:2rem;font-weight:800;color:#16a34a;line-height:1;">{{ $teamPresentToday }}</div>
            <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">Present Today</div>
            <i class="bi bi-person-check-fill" style="font-size:1.4rem;color:#dcfce7;margin-top:8px;display:block;"></i>
        </div>
    </div>

    {{-- On Leave Today --}}
    <div class="col-6 col-md-3">
        <div class="info-card text-center" style="padding:20px 12px;border-bottom:3px solid #f59e0b;">
            <div style="font-size:2rem;font-weight:800;color:#d97706;line-height:1;">{{ $teamOnLeaveToday }}</div>
            <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">On Leave Today</div>
            <i class="bi bi-calendar-x-fill" style="font-size:1.4rem;color:#fef3c7;margin-top:8px;display:block;"></i>
        </div>
    </div>

    {{-- Pending Leave Approvals --}}
    <div class="col-6 col-md-3">
        <div class="info-card text-center" style="padding:20px 12px;border-bottom:3px solid {{ $pendingLeaves->count() > 0 ? '#dc2626' : '#e5e7eb' }};">
            <div style="font-size:2rem;font-weight:800;color:{{ $pendingLeaves->count() > 0 ? '#dc2626' : '#374151' }};line-height:1;">{{ $pendingLeaves->count() }}</div>
            <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">Pending Leave Requests</div>
            <i class="bi bi-hourglass-split" style="font-size:1.4rem;color:{{ $pendingLeaves->count() > 0 ? '#fee2e2' : '#f3f4f6' }};margin-top:8px;display:block;"></i>
        </div>
    </div>
</div>

{{-- Team Task Stats --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="info-card" style="padding:20px 24px;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="font-size:.88rem;font-weight:700;color:#111827;">Team Task Progress</div>
                <a href="{{ route('admin.tasks.kanban') }}" style="font-size:.78rem;color:#6366f1;text-decoration:none;">Kanban Board →</a>
            </div>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem;font-weight:800;color:#374151;line-height:1;">{{ $teamTaskStats['total'] }}</div>
                        <div style="font-size:.73rem;color:#9ca3af;margin-top:3px;">Total Tasks</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem;font-weight:800;color:#16a34a;line-height:1;">{{ $teamTaskStats['completed'] }}</div>
                        <div style="font-size:.73rem;color:#9ca3af;margin-top:3px;">Completed</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem;font-weight:800;color:#0ea5e9;line-height:1;">{{ $teamTaskStats['in_progress'] }}</div>
                        <div style="font-size:.73rem;color:#9ca3af;margin-top:3px;">In Progress</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem;font-weight:800;color:#f59e0b;line-height:1;">{{ $teamTaskStats['pending'] }}</div>
                        <div style="font-size:.73rem;color:#9ca3af;margin-top:3px;">Pending</div>
                    </div>
                </div>
            </div>
            @if($teamTaskStats['total'] > 0)
            <div style="margin-top:16px;">
                @php
                    $completedPct   = $teamTaskStats['total'] > 0 ? round($teamTaskStats['completed']   / $teamTaskStats['total'] * 100) : 0;
                    $inProgressPct  = $teamTaskStats['total'] > 0 ? round($teamTaskStats['in_progress'] / $teamTaskStats['total'] * 100) : 0;
                    $pendingPct     = $teamTaskStats['total'] > 0 ? round($teamTaskStats['pending']      / $teamTaskStats['total'] * 100) : 0;
                @endphp
                <div style="height:8px;background:#f3f4f6;border-radius:99px;overflow:hidden;display:flex;">
                    <div style="width:{{ $completedPct }}%;background:#16a34a;transition:width .6s;"></div>
                    <div style="width:{{ $inProgressPct }}%;background:#0ea5e9;transition:width .6s;"></div>
                    <div style="width:{{ $pendingPct }}%;background:#f59e0b;transition:width .6s;"></div>
                </div>
                <div class="d-flex gap-3 mt-2" style="font-size:.7rem;color:#9ca3af;">
                    <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#16a34a;margin-right:4px;"></span>Done {{ $completedPct }}%</span>
                    <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#0ea5e9;margin-right:4px;"></span>In Progress {{ $inProgressPct }}%</span>
                    <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#f59e0b;margin-right:4px;"></span>Pending {{ $pendingPct }}%</span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Bottom Row: Pending Leaves + Team Attendance --}}
<div class="row g-3">

    {{-- Pending Leave Requests --}}
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">
                    Pending Leave Requests
                    @if($pendingLeaves->count() > 0)
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#dc2626;color:#fff;font-size:.65rem;font-weight:700;margin-left:6px;">{{ $pendingLeaves->count() }}</span>
                    @endif
                </span>
                <a href="{{ route('admin.attendance.leaves') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;border-radius:7px;">Manage All</a>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLeaves as $leave)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $leave->employee->user->avatar_url }}"
                                        style="width:28px;height:28px;border-radius:50%;object-fit:cover;" alt="">
                                    <div>
                                        <div style="font-weight:700;font-size:.83rem;color:#111827;">{{ $leave->employee->full_name }}</div>
                                        <div style="font-size:.7rem;color:#9ca3af;">{{ $leave->employee->designation }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="spill spill-info" style="font-size:.7rem;">{{ ucfirst($leave->type) }}</span>
                            </td>
                            <td>
                                <div style="font-size:.8rem;color:#374151;">{{ $leave->from_date->format('M d') }} – {{ $leave->to_date->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <span style="font-size:.85rem;font-weight:700;color:#374151;">{{ $leave->days }}d</span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button onclick="approveLeave({{ $leave->id }})" class="btn btn-sm"
                                        style="padding:3px 10px;font-size:.72rem;border-radius:6px;background:#dcfce7;color:#16a34a;border:none;font-weight:600;">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button onclick="rejectLeave({{ $leave->id }})" class="btn btn-sm"
                                        style="padding:3px 10px;font-size:.72rem;border-radius:6px;background:#fee2e2;color:#dc2626;border:none;font-weight:600;">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5">
                            <div class="empty-state"><i class="bi bi-check-circle"></i><p>No pending leave requests</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Team Attendance Today --}}
    <div class="col-lg-6">
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">Team Attendance Today</span>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;border-radius:7px;">Full Report</a>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teamEmployees as $member)
                        @php
                            $att = $teamAttendanceToday->get($member->id);
                            if ($att) {
                                $attStatus = $att->status;
                                $attColor  = match($att->status) {
                                    'present'  => 'success',
                                    'late'     => 'warning',
                                    'on_leave' => 'info',
                                    default    => 'danger',
                                };
                            } else {
                                $attStatus = 'absent';
                                $attColor  = 'danger';
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $member->user->avatar_url }}"
                                        style="width:28px;height:28px;border-radius:50%;object-fit:cover;" alt="">
                                    <div>
                                        <div style="font-weight:700;font-size:.83rem;color:#111827;">{{ $member->full_name }}</div>
                                        <div style="font-size:.7rem;color:#9ca3af;">{{ $member->designation }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="spill spill-{{ $attColor }}" style="font-size:.7rem;">{{ ucfirst($attStatus) }}</span>
                            </td>
                            <td>
                                <span style="font-size:.82rem;font-family:monospace;color:#374151;">
                                    {{ $att?->check_in?->format('H:i') ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size:.82rem;color:#6b7280;">
                                    {{ $att?->work_hours ? $att->work_hours . 'h' : '—' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4">
                            <div class="empty-state"><i class="bi bi-people"></i><p>No team members assigned</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Pending Work Reports --}}
@if($pendingWorkReports->count() > 0)
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">
                    Pending Work Reports
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#f59e0b;color:#fff;font-size:.65rem;font-weight:700;margin-left:6px;">{{ $pendingWorkReports->count() }}</span>
                </span>
                <a href="{{ route('admin.work-reports.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;border-radius:7px;">Review All</a>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Report Date</th>
                            <th>Hours Worked</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingWorkReports as $report)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $report->employee->user->avatar_url }}"
                                        style="width:28px;height:28px;border-radius:50%;object-fit:cover;" alt="">
                                    <span style="font-weight:700;font-size:.83rem;color:#111827;">{{ $report->employee->full_name }}</span>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:.83rem;color:#374151;">{{ $report->report_date->format('M d, Y') }}</span>
                            </td>
                            <td>
                                <span style="font-size:.83rem;color:#374151;font-family:monospace;">{{ $report->hours_worked }}h</span>
                            </td>
                            <td>
                                <span style="font-size:.78rem;color:#9ca3af;">{{ $report->submitted_at?->diffForHumans() ?? '—' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.work-reports.show', $report) }}"
                                    class="btn btn-sm"
                                    style="padding:3px 12px;font-size:.75rem;border-radius:6px;background:#ede9fe;color:#6d28d9;border:none;font-weight:600;">
                                    Review
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
{{-- Live clock --}}
setInterval(() => {
    const el = document.getElementById('clock');
    if (el) el.textContent = new Date().toLocaleTimeString('en-US', { hour12: false });
}, 1000);

{{-- Work timer --}}
@if($isCheckedIn && $todayAttendance?->check_in)
const checkInTime = new Date('{{ $todayAttendance->check_in->toISOString() }}');
setInterval(() => {
    const diff = new Date() - checkInTime;
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    const el = document.getElementById('workTimer');
    if (el) el.textContent = `${h}h ${m}m ${s}s working`;
}, 1000);
@endif

function checkIn() {
    APP.ajax('/employee/attendance/check-in', 'POST')
        .done(res => {
            if (res.success) { APP.toast('Checked in at ' + res.check_in + '!'); setTimeout(() => location.reload(), 800); }
        })
        .fail(err => APP.toast(err.responseJSON?.message || 'Failed to check in', 'error'));
}

function checkOut() {
    APP.confirm('Check Out', 'Are you sure you want to check out?', () => {
        APP.ajax('/employee/attendance/check-out', 'POST')
            .done(res => {
                if (res.success) { APP.toast(`Checked out! Worked ${res.work_hours}h`); setTimeout(() => location.reload(), 800); }
            })
            .fail(err => APP.toast(err.responseJSON?.message || 'Failed to check out', 'error'));
    });
}

function approveLeave(id) {
    APP.confirm('Approve Leave', 'Approve this leave request?', () => {
        APP.ajax(`/admin/attendance/leaves/${id}/approve`, 'POST')
            .done(res => {
                if (res.success) { APP.toast('Leave approved!'); setTimeout(() => location.reload(), 800); }
            })
            .fail(err => APP.toast(err.responseJSON?.message || 'Failed', 'error'));
    });
}

function rejectLeave(id) {
    APP.confirm('Reject Leave', 'Reject this leave request?', () => {
        APP.ajax(`/admin/attendance/leaves/${id}/approve`, 'POST', { action: 'reject' })
            .done(res => {
                if (res.success) { APP.toast('Leave rejected.', 'warning'); setTimeout(() => location.reload(), 800); }
            })
            .fail(err => APP.toast(err.responseJSON?.message || 'Failed', 'error'));
    });
}
</script>
@endpush
