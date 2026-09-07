@extends('layouts.app')

@section('title', 'Monthly Attendance Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Monthly Report</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Monthly Attendance Report</h4>
            <p>View and export attendance summaries by period and department</p>
        </div>
        <a href="{{ route('admin.reports.pdf', 'attendance') }}" class="btn btn-sm btn-outline-danger px-4">
            <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card-glass mb-3 px-4 py-3">
    <form method="GET" action="{{ route('admin.attendance.report') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Month</label>
            <select name="month" class="form-select">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected(request('month', date('n')) == $m)>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Year</label>
            <select name="year" class="form-select">
                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                    <option value="{{ $y }}" @selected(request('year', date('Y')) == $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Department</label>
            <select name="department" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary-grad w-100" style="height:42px;border-radius:9px;">
                <i class="bi bi-funnel me-1"></i>Generate
            </button>
        </div>
    </form>
</div>

{{-- Report Table --}}
<div class="card-glass overflow-hidden">
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="text-center">Working Days</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Late</th>
                    <th class="text-center">Leaves</th>
                    <th class="text-center">Total Hours</th>
                    <th class="text-center">Overtime</th>
                    <th class="text-center">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report ?? [] as $row)
                <tr>
                    <td>
                        <div class="fw-semibold" style="font-size:.87rem;">{{ $row['employee_name'] }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;">{{ $row['employee_id'] }}</div>
                    </td>
                    <td style="font-size:.82rem;">{{ $row['department'] ?? '—' }}</td>
                    <td class="text-center" style="font-size:.85rem;">{{ $row['working_days'] }}</td>
                    <td class="text-center fw-semibold" style="color:#059669;">{{ $row['present'] }}</td>
                    <td class="text-center fw-semibold" style="color:#dc2626;">{{ $row['absent'] }}</td>
                    <td class="text-center fw-semibold" style="color:#d97706;">{{ $row['late'] }}</td>
                    <td class="text-center fw-semibold" style="color:#0284c7;">{{ $row['leaves'] }}</td>
                    <td class="text-center" style="font-size:.82rem;">{{ number_format($row['total_hours'] ?? 0, 1) }}h</td>
                    <td class="text-center" style="font-size:.82rem;">
                        @if(($row['overtime'] ?? 0) > 0)
                            <span style="color:#0284c7;">+{{ number_format($row['overtime'], 1) }}h</span>
                        @else —
                        @endif
                    </td>
                    <td class="text-center">
                        @php $pct = $row['attendance_percentage'] ?? 0; @endphp
                        <div class="d-flex align-items-center gap-1 justify-content-center">
                            <div class="progress" style="height:6px;width:60px;border-radius:3px;">
                                <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $pct >= 90 ? '#059669' : ($pct >= 75 ? '#d97706' : '#dc2626') }};border-radius:3px;"></div>
                            </div>
                            <small style="font-size:.72rem;">{{ $pct }}%</small>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="empty-state">
                        <i class="bi bi-calendar3"></i>
                        <p>No data for selected period</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
