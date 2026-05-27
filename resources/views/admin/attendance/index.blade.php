@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Attendance Management</h4>
        <p class="text-muted mb-0">Track employee attendance records</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.attendance.report') }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-bar-graph me-1"></i>Monthly Report
        </a>
        <a href="{{ route('admin.attendance.leaves') }}" class="btn btn-outline-warning">
            <i class="bi bi-calendar-minus me-1"></i>Leave Requests
            @if(($pendingLeaves ?? 0) > 0)
                <span class="badge bg-danger">{{ $pendingLeaves }}</span>
            @endif
        </a>
    </div>
</div>

{{-- Date & Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="date" name="date" class="form-control"
                    value="{{ request('date', date('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="present" @selected(request('status') === 'present')>Present</option>
                    <option value="absent" @selected(request('status') === 'absent')>Absent</option>
                    <option value="late" @selected(request('status') === 'late')>Late</option>
                    <option value="half_day" @selected(request('status') === 'half_day')>Half Day</option>
                    <option value="on_leave" @selected(request('status') === 'on_leave')>On Leave</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary for the day --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-success border-4">
            <div class="fs-2 fw-bold text-success">{{ $stats['present'] }}</div>
            <div class="text-muted">Present</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-danger border-4">
            <div class="fs-2 fw-bold text-danger">{{ $stats['absent'] }}</div>
            <div class="text-muted">Absent</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-warning border-4">
            <div class="fs-2 fw-bold text-warning">{{ $stats['on_leave'] }}</div>
            <div class="text-muted">On Leave</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-info border-4">
            <div class="fs-2 fw-bold text-info">{{ $stats['late'] }}</div>
            <div class="text-muted">Late</div>
        </div>
    </div>
</div>

{{-- Attendance Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <span class="fw-semibold">Attendance for {{ $date->format('d M Y') }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Work Hours</th>
                        <th>Overtime</th>
                        <th>Status</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendance as $att)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $att->employee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($att->employee->full_name ?? 'E').'&size=32&background=6366f1&color=fff' }}"
                                    class="rounded-circle" width="32" height="32">
                                <div>
                                    <div class="fw-semibold small">{{ $att->employee->full_name ?? 'Unknown' }}</div>
                                    <small class="text-muted">{{ $att->employee->employee_id ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $att->employee->department->name ?? '—' }}</td>
                        <td>
                            @if($att->check_in)
                                <span class="{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') > '09:15' ? 'text-warning' : 'text-success' }}">
                                    {{ \Carbon\Carbon::parse($att->check_in)->format('h:i A') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '—' }}</td>
                        <td>
                            @if($att->work_hours)
                                <span class="{{ $att->work_hours < 8 ? 'text-warning' : 'text-success' }}">
                                    {{ number_format($att->work_hours, 1) }}h
                                </span>
                            @else — @endif
                        </td>
                        <td>
                            @if($att->overtime_hours > 0)
                                <span class="text-info">+{{ number_format($att->overtime_hours, 1) }}h</span>
                            @else — @endif
                        </td>
                        <td>
                            @php
                                $statusColors = ['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'half_day' => 'info', 'on_leave' => 'secondary'];
                                $st = $att->status ?? 'present';
                            @endphp
                            <span class="badge bg-{{ $statusColors[$st] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                            </span>
                        </td>
                        <td><small class="text-muted"><code>{{ $att->check_in_ip ?? '—' }}</code></small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            No attendance records for this date
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($attendance->hasPages())
    <div class="card-footer bg-transparent">
        {{ $attendance->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
