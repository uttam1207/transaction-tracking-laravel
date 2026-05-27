@extends('layouts.app')

@section('title', 'Employee Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Employee Performance Report</h4>
        <p class="text-muted mb-0">Employee productivity and performance analytics</p>
    </div>
    <a href="{{ route('admin.reports.pdf', 'employees') }}" class="btn btn-outline-danger">
        <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.employees') }}" class="row g-2">
            <div class="col-md-3">
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Generate</button>
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
                        <th class="text-center">Tasks Completed</th>
                        <th class="text-center">Hours Worked</th>
                        <th class="text-center">Attendance %</th>
                        <th class="text-center">Performance Score</th>
                        <th class="text-center">Reports Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeeData ?? [] as $row)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $row['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($row['name']).'&size=32&background=6366f1&color=fff' }}"
                                    class="rounded-circle" width="32" height="32">
                                <div>
                                    <div class="fw-semibold">{{ $row['name'] }}</div>
                                    <small class="text-muted">{{ $row['employee_id'] }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $row['department'] ?? '—' }}</td>
                        <td class="text-center fw-semibold text-success">{{ $row['tasks_completed'] ?? 0 }}</td>
                        <td class="text-center">{{ number_format($row['hours_worked'] ?? 0, 1) }}h</td>
                        <td class="text-center">
                            @php $att = $row['attendance_pct'] ?? 0; @endphp
                            <span class="badge bg-{{ $att >= 90 ? 'success' : ($att >= 75 ? 'warning' : 'danger') }}">
                                {{ $att }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @php $score = $row['performance_score'] ?? 0; @endphp
                            <div class="d-flex align-items-center gap-1 justify-content-center">
                                <div class="progress" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-{{ $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger') }}"
                                        style="width: {{ $score }}%"></div>
                                </div>
                                <small>{{ $score }}%</small>
                            </div>
                        </td>
                        <td class="text-center">{{ $row['reports_count'] ?? 0 }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2"></i>No data available
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
