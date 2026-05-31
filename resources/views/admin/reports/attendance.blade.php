@extends('layouts.app')
@section('title', 'Attendance Report')

@section('content')

@php
    $totalPresent = collect($report ?? [])->sum('present');
    $totalAbsent = collect($report ?? [])->sum('absent');
    $totalLate = collect($report ?? [])->sum('late');
    $avgAtt = count($report ?? []) > 0 ? collect($report ?? [])->avg('attendance_percentage') : 0;
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Attendance Report</h4>
            <p>Monthly attendance analytics and summary</p>
        </div>
        <a href="{{ route('admin.reports.pdf', 'attendance') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
            <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
        </a>
    </div>
</div>

<div class="filter-card">
    <form method="GET" action="{{ route('admin.reports.attendance') }}" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="flabel">Month</label>
            <select name="month" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected(request('month', date('n')) == $m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Year</label>
            <select name="year" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                    <option value="{{ $y }}" @selected(request('year', date('Y')) == $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label class="flabel">Department</label>
            <select name="department" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-sm btn-primary-grad px-4">Generate</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:18px;border-top:4px solid #16a34a;">
            <div style="font-size:1.8rem;font-weight:800;color:#16a34a;line-height:1;">{{ $totalPresent }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Total Present Days</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:18px;border-top:4px solid #dc2626;">
            <div style="font-size:1.8rem;font-weight:800;color:#dc2626;line-height:1;">{{ $totalAbsent }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Total Absent Days</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:18px;border-top:4px solid #f59e0b;">
            <div style="font-size:1.8rem;font-weight:800;color:#f59e0b;line-height:1;">{{ $totalLate }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Total Late Days</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:18px;border-top:4px solid #6366f1;">
            <div style="font-size:1.8rem;font-weight:800;color:#6366f1;line-height:1;">{{ number_format($avgAtt, 1) }}%</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Avg Attendance</div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="card-header"><span class="card-title">Attendance Breakdown</span></div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th class="text-center">Working Days</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Late</th>
                    <th class="text-center">On Leave</th>
                    <th class="text-center">Total Hours</th>
                    <th class="text-center">Overtime</th>
                    <th class="text-center">Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report ?? [] as $row)
                @php $pct = $row['attendance_percentage'] ?? 0; @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ $row['employee_name'] }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">{{ $row['employee_id'] }}</div>
                    </td>
                    <td class="text-center" style="font-size:.84rem;color:#374151;">{{ $row['working_days'] }}</td>
                    <td class="text-center"><span style="font-weight:700;color:#16a34a;font-size:.85rem;">{{ $row['present'] }}</span></td>
                    <td class="text-center"><span style="font-weight:700;color:#dc2626;font-size:.85rem;">{{ $row['absent'] }}</span></td>
                    <td class="text-center"><span style="font-weight:700;color:#f59e0b;font-size:.85rem;">{{ $row['late'] }}</span></td>
                    <td class="text-center"><span style="font-weight:700;color:#0ea5e9;font-size:.85rem;">{{ $row['leaves'] }}</span></td>
                    <td class="text-center"><span style="background:#eff6ff;color:#2563eb;padding:2px 7px;border-radius:6px;font-size:.75rem;font-weight:700;">{{ number_format($row['total_hours'] ?? 0, 1) }}h</span></td>
                    <td class="text-center">
                        @if(($row['overtime'] ?? 0) > 0)
                            <span style="color:#6366f1;font-weight:700;font-size:.83rem;">+{{ number_format($row['overtime'], 1) }}h</span>
                        @else <span style="color:#9ca3af;">—</span> @endif
                    </td>
                    <td class="text-center">
                        <span class="spill spill-{{ $pct >= 90 ? 'success' : ($pct >= 75 ? 'warning' : 'danger') }}" style="font-size:.72rem;">{{ $pct }}%</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9">
                    <div class="empty-state"><i class="bi bi-calendar3"></i><p>No data for selected period</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
