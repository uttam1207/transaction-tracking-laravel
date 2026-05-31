@extends('layouts.app')
@section('title', 'Attendance')

@section('breadcrumb')
    <li class="breadcrumb-item active">Attendance</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Attendance</h4>
            <p>Track daily employee check-ins and work hours</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            @foreach(['present'=>['Present','#86efac'],'absent'=>['Absent','#fca5a5'],'on_leave'=>['On Leave','#fde047'],'late'=>['Late','#93c5fd']] as $k=>$v)
            <div class="page-hero-stat">
                <div class="v" style="color:{{ $v[1] }};">{{ $stats[$k] ?? 0 }}</div>
                <div class="l">{{ $v[0] }}</div>
            </div>
            @if(!$loop->last)<div class="hero-vr"></div>@endif
            @endforeach
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attendance.report') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:8px;font-size:.8rem;font-weight:600;">
                <i class="bi bi-file-earmark-bar-graph me-1"></i>Monthly Report
            </a>
            <a href="{{ route('admin.attendance.leaves') }}" class="btn btn-sm" style="background:rgba(234,179,8,.2);color:#fde047;border:1px solid rgba(234,179,8,.35);border-radius:8px;font-size:.8rem;font-weight:600;">
                <i class="bi bi-calendar-minus me-1"></i>Leaves
                @if(($pendingLeaves ?? 0) > 0)
                    <span style="background:#ef4444;color:#fff;padding:0 5px;border-radius:10px;font-size:.65rem;margin-left:3px;">{{ $pendingLeaves }}</span>
                @endif
            </a>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="flabel" style="font-size:.72rem;">Date</label>
            <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="flabel" style="font-size:.72rem;">Department</label>
            <select name="department_id" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id')==$dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel" style="font-size:.72rem;">Status</label>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="present" @selected(request('status')==='present')>Present</option>
                <option value="absent" @selected(request('status')==='absent')>Absent</option>
                <option value="late" @selected(request('status')==='late')>Late</option>
                <option value="half_day" @selected(request('status')==='half_day')>Half Day</option>
                <option value="on_leave" @selected(request('status')==='on_leave')>On Leave</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2" style="margin-top:auto;">
            <button type="submit" class="btn btn-filter btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="{{ route('admin.attendance.index') }}" class="btn btn-filter btn-outline-secondary px-3"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="card-header">
        <span class="card-title">Attendance — {{ $date->format('d M Y') }}</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Work Hours</th>
                    <th>Overtime</th>
                    <th>Status</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendance as $att)
                @php
                    $st    = $att->status ?? 'present';
                    $stMap = ['present'=>'active','absent'=>'danger','late'=>'warning','half_day'=>'info','on_leave'=>'secondary'];
                    $stSpill = ['present'=>'spill-active','absent'=>'spill-danger','late'=>'spill-warning','half_day'=>'spill-info','on_leave'=>'spill-secondary'];
                    $late  = $att->check_in && \Carbon\Carbon::parse($att->check_in)->format('H:i') > '09:15';
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $att->employee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($att->employee->full_name ?? 'E').'&size=32&background=4f46e5&color=fff' }}"
                                class="rounded-circle" width="32" height="32">
                            <div>
                                <div style="font-weight:700;font-size:.85rem;color:#111827;">{{ $att->employee->full_name ?? 'Unknown' }}</div>
                                <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">{{ $att->employee->employee_id ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $att->employee->department->name ?? '—' }}</td>
                    <td>
                        @if($att->check_in)
                            <span style="font-size:.83rem;font-weight:600;color:{{ $late ? '#d97706' : '#16a34a' }};">
                                {{ \Carbon\Carbon::parse($att->check_in)->format('h:i A') }}
                            </span>
                            @if($late)<span style="font-size:.68rem;color:#d97706;margin-left:4px;">(Late)</span>@endif
                        @else <span style="color:#9ca3af;">—</span> @endif
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '—' }}</td>
                    <td>
                        @if($att->work_hours)
                            <span style="font-size:.83rem;font-weight:600;color:{{ $att->work_hours < 8 ? '#d97706' : '#16a34a' }};">
                                {{ number_format($att->work_hours,1) }}h
                            </span>
                        @else <span style="color:#9ca3af;">—</span> @endif
                    </td>
                    <td>
                        @if($att->overtime_hours > 0)
                            <span style="font-size:.83rem;font-weight:600;color:#2563eb;">+{{ number_format($att->overtime_hours,1) }}h</span>
                        @else <span style="color:#9ca3af;">—</span> @endif
                    </td>
                    <td><span class="spill {{ $stSpill[$st] ?? 'spill-secondary' }}">{{ ucfirst(str_replace('_',' ',$st)) }}</span></td>
                    <td style="font-family:monospace;font-size:.75rem;color:#9ca3af;">{{ $att->check_in_ip ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="8">
                    <div class="empty-state"><i class="bi bi-calendar-x"></i><p>No attendance records for this date</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($attendance->hasPages())
    <div class="pagination-wrap">{{ $attendance->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
