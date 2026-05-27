@extends('layouts.app')

@section('title', 'Monthly Attendance Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.attendance.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Attendance
        </a>
        <h4 class="mb-0 fw-bold mt-1">Monthly Attendance Report</h4>
    </div>
    <a href="{{ route('admin.reports.pdf', 'attendance') }}" class="btn btn-outline-danger">
        <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.attendance.report') }}" class="row g-2">
            <div class="col-md-3">
                <select name="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected(request('month', date('n')) == $m)>
                            {{ date('F', mktime(0,0,0,$m,1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <select name="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                        <option value="{{ $y }}" @selected(request('year', date('Y')) == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
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
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
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
                            <div class="fw-semibold">{{ $row['employee_name'] }}</div>
                            <small class="text-muted">{{ $row['employee_id'] }}</small>
                        </td>
                        <td>{{ $row['department'] ?? '—' }}</td>
                        <td class="text-center">{{ $row['working_days'] }}</td>
                        <td class="text-center text-success fw-semibold">{{ $row['present'] }}</td>
                        <td class="text-center text-danger fw-semibold">{{ $row['absent'] }}</td>
                        <td class="text-center text-warning fw-semibold">{{ $row['late'] }}</td>
                        <td class="text-center text-info fw-semibold">{{ $row['leaves'] }}</td>
                        <td class="text-center">{{ number_format($row['total_hours'] ?? 0, 1) }}h</td>
                        <td class="text-center">
                            @if(($row['overtime'] ?? 0) > 0)
                                <span class="text-info">+{{ number_format($row['overtime'], 1) }}h</span>
                            @else —
                            @endif
                        </td>
                        <td class="text-center">
                            @php $pct = $row['attendance_percentage'] ?? 0; @endphp
                            <div class="d-flex align-items-center gap-1 justify-content-center">
                                <div class="progress" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-{{ $pct >= 90 ? 'success' : ($pct >= 75 ? 'warning' : 'danger') }}"
                                        style="width: {{ $pct }}%"></div>
                                </div>
                                <small>{{ $pct }}%</small>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar3 fs-1 d-block mb-2"></i>
                            No data for selected period
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
