@extends('layouts.app')
@section('title', 'My Attendance')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="mb-0 fw-bold" style="color:#111827;">My Attendance</h4>
        <p class="text-muted mb-0" style="font-size:.85rem;">Track your check-ins and work hours</p>
    </div>
    <a href="{{ route('employee.attendance.leaves') }}" class="btn btn-sm btn-primary-grad px-4">
        <i class="bi bi-calendar-minus me-1"></i>Leave Requests
    </a>
</div>

{{-- Today Check-In/Out Card --}}
<div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:24px;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-30px;right:-30px;width:160px;height:160px;background:rgba(255,255,255,.06);border-radius:50%;"></div>
    <div class="row align-items-center" style="position:relative;z-index:1;">
        <div class="col-md-8">
            <h5 style="font-weight:700;margin-bottom:6px;">Today: {{ now()->format('l, d F Y') }}</h5>
            @if($todayAttendance)
                <p style="opacity:.8;font-size:.88rem;margin-bottom:4px;">
                    Check-in: <strong>{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</strong>
                    @if($todayAttendance->check_out)
                        &mdash; Check-out: <strong>{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}</strong>
                    @endif
                </p>
                @if(!$todayAttendance->check_out)
                    <p style="opacity:.75;font-size:.84rem;margin:0;">You are currently checked in.</p>
                @else
                    <p style="opacity:.75;font-size:.84rem;margin:0;">
                        Total hours today: <strong>{{ number_format($todayAttendance->work_hours, 1) }}h</strong>
                    </p>
                @endif
            @else
                <p style="opacity:.75;font-size:.84rem;margin:0;">You haven't checked in yet today.</p>
            @endif
        </div>
        <div class="col-md-4 text-end">
            @if(!$todayAttendance)
            <form action="{{ route('employee.attendance.check-in') }}" method="POST">
                @csrf
                <button type="submit" class="btn" style="background:rgba(255,255,255,.2);color:#fff;border:1.5px solid rgba(255,255,255,.4);border-radius:10px;font-weight:600;padding:10px 24px;backdrop-filter:blur(6px);">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Check In
                </button>
            </form>
            @elseif(!$todayAttendance->check_out)
            <form action="{{ route('employee.attendance.check-out') }}" method="POST">
                @csrf
                <button type="submit" class="btn" style="background:#fbbf24;color:#78350f;border:none;border-radius:10px;font-weight:700;padding:10px 24px;">
                    <i class="bi bi-box-arrow-right me-2"></i>Check Out
                </button>
            </form>
            @else
            <div style="text-align:center;">
                <i class="bi bi-check-circle-fill" style="font-size:2.5rem;opacity:.9;"></i>
                <p style="margin-top:6px;opacity:.8;font-size:.85rem;">Day Complete!</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Monthly Summary --}}
@php
    $month = now()->format('F Y');
    $present = $monthlyStats['present'] ?? 0;
    $absent = $monthlyStats['absent'] ?? 0;
    $totalHours = $monthlyStats['total_hours'] ?? 0;
    $attPct = $monthlyStats['attendance_percentage'] ?? 0;
    $attColor = $attPct >= 90 ? '#16a34a' : ($attPct >= 75 ? '#d97706' : '#dc2626');
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:20px;">
            <div style="font-size:2rem;font-weight:800;color:#16a34a;line-height:1;">{{ $present }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Present Days</div>
            <div style="font-size:.72rem;color:#9ca3af;margin-top:2px;">{{ $month }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:20px;">
            <div style="font-size:2rem;font-weight:800;color:#dc2626;line-height:1;">{{ $absent }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Absent Days</div>
            <div style="font-size:.72rem;color:#9ca3af;margin-top:2px;">{{ $month }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:20px;">
            <div style="font-size:2rem;font-weight:800;color:#6366f1;line-height:1;">{{ number_format($totalHours, 1) }}h</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Total Hours</div>
            <div style="font-size:.72rem;color:#9ca3af;margin-top:2px;">{{ $month }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:20px;">
            <div style="font-size:2rem;font-weight:800;color:{{ $attColor }};line-height:1;">{{ $attPct }}%</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Attendance Rate</div>
            <div style="height:5px;background:#f3f4f6;border-radius:3px;overflow:hidden;margin-top:8px;">
                <div style="width:{{ $attPct }}%;height:100%;background:{{ $attColor }};border-radius:3px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Attendance History --}}
<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="card-title">Attendance History</span>
        <form method="GET" action="{{ route('employee.attendance.index') }}" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm" style="border-radius:8px;border:1.5px solid #e5e7eb;font-size:.83rem;width:auto;">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected(request('month', date('n')) == $m)>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm btn-primary-grad px-3">Go</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
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
                @php
                    $colors = ['present'=>'success','absent'=>'danger','late'=>'warning','half_day'=>'info','on_leave'=>'secondary'];
                    $st = $att->status ?? 'present';
                    $isLate = $att->check_in && \Carbon\Carbon::parse($att->check_in)->format('H:i') > '09:15';
                @endphp
                <tr>
                    <td style="font-weight:700;font-size:.87rem;color:#111827;">{{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ \Carbon\Carbon::parse($att->date)->format('D') }}</td>
                    <td>
                        @if($att->check_in)
                            <span style="font-size:.83rem;{{ $isLate ? 'color:#d97706;font-weight:600;' : '' }}">
                                {{ \Carbon\Carbon::parse($att->check_in)->format('h:i A') }}
                                @if($isLate) <span class="spill spill-warning" style="font-size:.65rem;padding:1px 5px;margin-left:4px;">Late</span> @endif
                            </span>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '—' }}</td>
                    <td>
                        @if($att->work_hours)
                            <span style="font-size:.83rem;font-weight:700;color:{{ $att->work_hours < 8 ? '#d97706' : '#16a34a' }};">
                                {{ number_format($att->work_hours, 1) }}h
                            </span>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($att->overtime_hours > 0)
                            <span style="color:#6366f1;font-weight:700;font-size:.83rem;">+{{ number_format($att->overtime_hours, 1) }}h</span>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="spill spill-{{ $colors[$st] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-calendar-x"></i><p>No attendance records for this month</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
