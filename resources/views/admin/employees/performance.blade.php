@extends('layouts.app')
@section('title', 'Employee Performance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.show', $employee) }}">{{ $employee->full_name }}</a></li>
    <li class="breadcrumb-item active">Performance</li>
@endsection

@section('content')

@php
    $statusColors = ['pending'=>'secondary','assigned'=>'info','in_progress'=>'warning','review'=>'info','completed'=>'success','cancelled'=>'danger'];
    $reportColors = ['draft'=>'secondary','submitted'=>'warning','approved'=>'success','rejected'=>'danger'];
    $attPct = $attendanceStats['percentage'];
    $attColor = $attPct >= 80 ? '#16a34a' : ($attPct >= 60 ? '#f59e0b' : '#dc2626');
    $taskPct = $taskStats['total'] > 0 ? round(($taskStats['completed'] / $taskStats['total']) * 100) : 0;
    $taskColor = $taskPct >= 75 ? '#16a34a' : ($taskPct >= 40 ? '#f59e0b' : '#dc2626');
@endphp

<a href="{{ route('admin.employees.show', $employee) }}" class="back-btn">
    <i class="bi bi-arrow-left"></i>Back to {{ $employee->full_name }}
</a>

{{-- Hero --}}
<div class="page-hero" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $employee->user?->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($employee->full_name).'&size=56&background=6366f1&color=fff' }}"
                class="rounded-circle" style="width:56px;height:56px;object-fit:cover;border:3px solid rgba(255,255,255,.35);" alt="">
            <div>
                <h4 style="font-weight:800;color:#fff;margin-bottom:2px;">{{ $employee->full_name }}</h4>
                <p style="font-size:.83rem;opacity:.75;color:#fff;margin:0;">
                    {{ $employee->designation }} &nbsp;·&nbsp; {{ $employee->department->name ?? '—' }}
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat">
                <div class="v">{{ $taskStats['completed'] }}</div>
                <div class="l">Tasks Done</div>
            </div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat">
                <div class="v" style="color:#86efac;">{{ $attPct }}%</div>
                <div class="l">Attendance</div>
            </div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat">
                <div class="v" style="color:#fde047;">{{ $reportsStats['approved'] }}</div>
                <div class="l">Reports OK</div>
            </div>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    {{-- Task Completion Rate --}}
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:20px;border-top:4px solid {{ $taskColor }};">
            <div style="font-size:2rem;font-weight:800;color:{{ $taskColor }};line-height:1;">{{ $taskPct }}%</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Task Completion Rate</div>
            <div style="font-size:.76rem;color:#9ca3af;margin-top:2px;">{{ $taskStats['completed'] }} / {{ $taskStats['total'] }} tasks</div>
        </div>
    </div>
    {{-- Attendance Rate --}}
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:20px;border-top:4px solid {{ $attColor }};">
            <div style="font-size:2rem;font-weight:800;color:{{ $attColor }};line-height:1;">{{ $attPct }}%</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Attendance (This Month)</div>
            <div style="font-size:.76rem;color:#9ca3af;margin-top:2px;">{{ $attendanceStats['present'] }} days present</div>
        </div>
    </div>
    {{-- Avg Work Hours --}}
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:20px;border-top:4px solid #6366f1;">
            <div style="font-size:2rem;font-weight:800;color:#6366f1;line-height:1;">{{ $attendanceStats['avg_hours'] }}h</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Avg. Work Hours/Day</div>
            <div style="font-size:.76rem;color:#9ca3af;margin-top:2px;">Current month</div>
        </div>
    </div>
    {{-- Work Reports Approved --}}
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:20px;border-top:4px solid #16a34a;">
            <div style="font-size:2rem;font-weight:800;color:#16a34a;line-height:1;">{{ $reportsStats['approved'] }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Reports Approved</div>
            <div style="font-size:.76rem;color:#9ca3af;margin-top:2px;">of {{ $reportsStats['total'] }} total</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Attendance Chart --}}
    <div class="col-lg-8">
        <div class="info-card" style="height:100%;">
            <div class="info-card-hdr"><i class="bi bi-bar-chart me-2"></i>Monthly Attendance (Last 6 Months)</div>
            <div class="info-card-body" style="padding:16px;">
                <div id="attChart" style="height:220px;"></div>
            </div>
        </div>
    </div>

    {{-- Task Breakdown --}}
    <div class="col-lg-4">
        <div class="info-card" style="height:100%;">
            <div class="info-card-hdr"><i class="bi bi-list-task me-2"></i>Task Breakdown</div>
            <div class="info-card-body">
                @foreach([
                    ['Completed', $taskStats['completed'], '#16a34a'],
                    ['In Progress', $taskStats['in_progress'], '#6366f1'],
                    ['Pending', $taskStats['pending'], '#9ca3af'],
                    ['Overdue', $taskStats['overdue'], '#dc2626'],
                ] as [$label, $count, $color])
                <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f3f4f6;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></div>
                        <span style="font-size:.85rem;color:#374151;">{{ $label }}</span>
                    </div>
                    <span style="font-size:.85rem;font-weight:700;color:#111827;">{{ $count }}</span>
                </div>
                @endforeach

                {{-- Completion bar --}}
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.78rem;color:#6b7280;">Completion Rate</span>
                        <span style="font-size:.78rem;font-weight:700;color:{{ $taskColor }};">{{ $taskPct }}%</span>
                    </div>
                    <div style="background:#e5e7eb;border-radius:6px;height:8px;overflow:hidden;">
                        <div style="height:8px;border-radius:6px;width:{{ $taskPct }}%;background:{{ $taskColor }};"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Attendance Details --}}
    <div class="col-lg-4">
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-calendar-check me-2"></i>Attendance This Month</div>
            <div class="info-card-body">
                @foreach([
                    ['Present', $attendanceStats['present'], '#16a34a'],
                    ['Absent', $attendanceStats['absent'], '#dc2626'],
                    ['Late', $attendanceStats['late'], '#f59e0b'],
                ] as [$label, $count, $color])
                <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:.85rem;color:#374151;">{{ $label }}</span>
                    <span style="font-size:.9rem;font-weight:800;color:{{ $color }};">{{ $count }}</span>
                </div>
                @endforeach

                <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:.85rem;color:#374151;">Avg. Hours/Day</span>
                    <span style="font-size:.9rem;font-weight:800;color:#6366f1;">{{ $attendanceStats['avg_hours'] }}h</span>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.78rem;color:#6b7280;">Attendance Rate</span>
                        <span style="font-size:.78rem;font-weight:700;color:{{ $attColor }};">{{ $attPct }}%</span>
                    </div>
                    <div style="background:#e5e7eb;border-radius:6px;height:8px;overflow:hidden;">
                        <div style="height:8px;border-radius:6px;width:{{ $attPct }}%;background:{{ $attColor }};"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Work Reports Summary --}}
        <div class="info-card mt-3">
            <div class="info-card-hdr"><i class="bi bi-file-earmark-check me-2"></i>Work Reports</div>
            <div class="info-card-body">
                @foreach([
                    ['Total', $reportsStats['total'], '#6366f1'],
                    ['Approved', $reportsStats['approved'], '#16a34a'],
                    ['Pending Review', $reportsStats['submitted'], '#f59e0b'],
                    ['Rejected', $reportsStats['rejected'], '#dc2626'],
                ] as [$label, $count, $color])
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:.85rem;color:#374151;">{{ $label }}</span>
                    <span style="font-size:.9rem;font-weight:800;color:{{ $color }};">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Recent Tasks --}}
    <div class="col-lg-8">
        <div class="table-card mb-3">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-check2-square me-2"></i>Recent Tasks</span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Project</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTasks as $task)
                        @php
                            $pColors = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
                            $taskOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && !in_array($task->status, ['completed','cancelled']);
                        @endphp
                        <tr>
                            <td style="font-weight:600;font-size:.87rem;color:#111827;">{{ Str::limit($task->title, 35) }}</td>
                            <td style="font-size:.83rem;color:#6b7280;">{{ $task->project->name ?? '—' }}</td>
                            <td><span class="spill spill-{{ $pColors[$task->priority] ?? 'secondary' }}" style="font-size:.7rem;">{{ ucfirst($task->priority) }}</span></td>
                            <td style="font-size:.82rem;color:{{ $taskOverdue ? '#dc2626' : '#6b7280' }};font-weight:{{ $taskOverdue ? '700' : '400' }};">
                                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '—' }}
                            </td>
                            <td><span class="spill spill-{{ $statusColors[$task->status] ?? 'secondary' }}" style="font-size:.7rem;">{{ ucwords(str_replace('_',' ',$task->status)) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5"><div class="empty-state"><i class="bi bi-list-task"></i><p>No tasks assigned</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Work Reports --}}
        <div class="table-card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-file-text me-2"></i>Recent Work Reports</span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Report</th>
                            <th>Date</th>
                            <th>Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReports as $report)
                        @php $scoreColor = $report->productivity_score >= 75 ? '#16a34a' : ($report->productivity_score >= 50 ? '#f59e0b' : '#dc2626'); @endphp
                        <tr>
                            <td style="font-weight:600;font-size:.87rem;color:#111827;">{{ Str::limit($report->title ?? 'Work Report', 35) }}</td>
                            <td style="font-size:.83rem;color:#6b7280;">{{ $report->report_date ? \Carbon\Carbon::parse($report->report_date)->format('M d, Y') : '—' }}</td>
                            <td>
                                @if($report->productivity_score)
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <div style="background:#e5e7eb;border-radius:4px;height:6px;width:60px;overflow:hidden;flex-shrink:0;">
                                        <div style="height:6px;border-radius:4px;width:{{ $report->productivity_score }}%;background:{{ $scoreColor }};"></div>
                                    </div>
                                    <span style="font-size:.78rem;font-weight:700;color:{{ $scoreColor }};">{{ $report->productivity_score }}%</span>
                                </div>
                                @else
                                <span style="font-size:.82rem;color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <td><span class="spill spill-{{ $reportColors[$report->status] ?? 'secondary' }}" style="font-size:.7rem;">{{ ucfirst($report->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><div class="empty-state"><i class="bi bi-file-earmark"></i><p>No work reports submitted</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const chartLabels = @json($chartLabels);
const chartPresent = @json($chartPresent);

new ApexCharts(document.querySelector('#attChart'), {
    chart: { type: 'bar', height: 220, toolbar: { show: false } },
    series: [{ name: 'Days Present', data: chartPresent }],
    xaxis: { categories: chartLabels, labels: { style: { fontSize: '11px' } } },
    colors: ['#6366f1'],
    plotOptions: { bar: { borderRadius: 5, columnWidth: '45%' } },
    dataLabels: { enabled: false },
    grid: { borderColor: '#f3f4f6' },
    tooltip: { y: { formatter: v => v + ' days' } },
    yaxis: { labels: { style: { fontSize: '11px' } } },
}).render();
</script>
@endpush
