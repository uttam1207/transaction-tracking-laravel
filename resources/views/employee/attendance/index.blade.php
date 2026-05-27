@extends('layouts.app')

@section('title', 'My Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">My Attendance</h4>
        <p class="text-muted mb-0">Track your check-ins and work hours</p>
    </div>
    <a href="{{ route('employee.attendance.leaves') }}" class="btn btn-outline-warning">
        <i class="bi bi-calendar-minus me-1"></i>Leave Requests
    </a>
</div>

{{-- Check-in / Check-out Card --}}
<div class="card border-0 shadow-sm mb-4 bg-gradient" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
    <div class="card-body text-white">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1">Today: {{ now()->format('l, d F Y') }}</h5>
                @if($todayAttendance)
                    <p class="mb-1 opacity-75">
                        Check-in: {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}
                        @if($todayAttendance->check_out)
                            &mdash; Check-out: {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}
                        @endif
                    </p>
                    @if(!$todayAttendance->check_out)
                    <p class="mb-0 opacity-75">You are currently checked in.</p>
                    @else
                    <p class="mb-0 opacity-75">
                        Total hours today: <strong>{{ number_format($todayAttendance->work_hours, 1) }}h</strong>
                    </p>
                    @endif
                @else
                    <p class="mb-0 opacity-75">You haven't checked in yet today.</p>
                @endif
            </div>
            <div class="col-md-4 text-end">
                @if(!$todayAttendance)
                <form action="{{ route('employee.attendance.check-in') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-light btn-lg px-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Check In
                    </button>
                </form>
                @elseif(!$todayAttendance->check_out)
                <form action="{{ route('employee.attendance.check-out') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-lg px-4">
                        <i class="bi bi-box-arrow-right me-2"></i>Check Out
                    </button>
                </form>
                @else
                <div class="text-center">
                    <i class="bi bi-check-circle-fill fs-1"></i>
                    <p class="mb-0 mt-1">Day Complete!</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Monthly Summary --}}
<div class="row g-3 mb-4">
    @php
        $month = now()->format('F Y');
        $present = $monthlyStats['present'] ?? 0;
        $absent = $monthlyStats['absent'] ?? 0;
        $totalHours = $monthlyStats['total_hours'] ?? 0;
        $attPct = $monthlyStats['attendance_percentage'] ?? 0;
    @endphp
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-success">{{ $present }}</div>
            <div class="text-muted">Present Days</div>
            <small class="text-muted">{{ $month }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-danger">{{ $absent }}</div>
            <div class="text-muted">Absent Days</div>
            <small class="text-muted">{{ $month }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-primary">{{ number_format($totalHours, 1) }}h</div>
            <div class="text-muted">Total Hours</div>
            <small class="text-muted">{{ $month }}</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-{{ $attPct >= 90 ? 'success' : ($attPct >= 75 ? 'warning' : 'danger') }}">
                {{ $attPct }}%
            </div>
            <div class="text-muted">Attendance Rate</div>
            <small class="text-muted">{{ $month }}</small>
        </div>
    </div>
</div>

{{-- Attendance History --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Attendance History</span>
        <div>
            <form method="GET" action="{{ route('employee.attendance.index') }}" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected(request('month', date('n')) == $m)>
                            {{ date('F', mktime(0,0,0,$m,1)) }}
                        </option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Work Hours</th>
                        <th>Overtime</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendance as $att)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($att->date)->format('D') }}</td>
                        <td>
                            @if($att->check_in)
                                <span class="{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') > '09:15' ? 'text-warning' : '' }}">
                                    {{ \Carbon\Carbon::parse($att->check_in)->format('h:i A') }}
                                </span>
                            @else — @endif
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
                                $colors = ['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'half_day' => 'info', 'on_leave' => 'secondary'];
                                $st = $att->status ?? 'present';
                            @endphp
                            <span class="badge bg-{{ $colors[$st] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            No attendance records
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
