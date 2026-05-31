@extends('layouts.app')
@section('title', 'Employee Report')

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Employee Performance Report</h4>
            <p>Employee productivity and performance analytics</p>
        </div>
        <a href="{{ route('admin.reports.pdf', 'employees') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
            <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
        </a>
    </div>
</div>

<div class="filter-card">
    <form method="GET" action="{{ route('admin.reports.employees') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="flabel">Department</label>
            <select name="department" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
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
        <div class="col-md-auto">
            <button type="submit" class="btn btn-sm btn-primary-grad px-4">Generate</button>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="card-header"><span class="card-title">Employee Performance Data</span></div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="text-center">Tasks Done</th>
                    <th class="text-center">Hours</th>
                    <th class="text-center">Attendance</th>
                    <th class="text-center">Performance</th>
                    <th class="text-center">Reports</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employeeData ?? [] as $row)
                @php
                    $att = $row['attendance_pct'] ?? 0;
                    $sc = $row['performance_score'] ?? 0;
                    $attColor = $att >= 90 ? '#16a34a' : ($att >= 75 ? '#d97706' : '#dc2626');
                    $scColor = $sc >= 80 ? '#16a34a' : ($sc >= 60 ? '#d97706' : '#dc2626');
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $row['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($row['name']).'&size=32&background=6366f1&color=fff' }}"
                                class="rounded-circle" style="width:32px;height:32px;object-fit:cover;flex-shrink:0;" alt="">
                            <div>
                                <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ $row['name'] }}</div>
                                <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">{{ $row['employee_id'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $row['department'] ?? '—' }}</td>
                    <td class="text-center"><span style="font-weight:700;color:#16a34a;font-size:.85rem;">{{ $row['tasks_completed'] ?? 0 }}</span></td>
                    <td class="text-center"><span style="background:#eff6ff;color:#2563eb;padding:2px 7px;border-radius:6px;font-size:.75rem;font-weight:700;">{{ number_format($row['hours_worked'] ?? 0, 1) }}h</span></td>
                    <td class="text-center">
                        <span class="spill spill-{{ $att >= 90 ? 'success' : ($att >= 75 ? 'warning' : 'danger') }}" style="font-size:.72rem;">{{ $att }}%</span>
                    </td>
                    <td class="text-center">
                        <div style="display:flex;align-items:center;gap:6px;justify-content:center;min-width:90px;">
                            <div style="flex:1;height:5px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                <div style="width:{{ $sc }}%;height:100%;background:{{ $scColor }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:.75rem;font-weight:700;color:{{ $scColor }};">{{ $sc }}%</span>
                        </div>
                    </td>
                    <td class="text-center" style="font-weight:600;font-size:.85rem;">{{ $row['reports_count'] ?? 0 }}</td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-people"></i><p>No data available for selected period</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
