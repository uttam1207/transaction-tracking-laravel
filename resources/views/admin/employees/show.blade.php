@extends('layouts.app')
@section('title', 'Employee Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')

<a href="{{ route('admin.employees.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Employees</a>

@php
    $status = $employee->status ?? 'active';
    $score = $employee->performance_score ?? 0;
    $scoreColor = $score >= 80 ? '#16a34a' : ($score >= 60 ? '#d97706' : '#dc2626');
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $employee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($employee->full_name).'&size=80&background=ffffff&color=4f46e5' }}"
                class="rounded-circle" style="width:64px;height:64px;border:2.5px solid rgba(255,255,255,.4);object-fit:cover;" alt="">
            <div>
                <h4 style="margin:0;font-weight:800;">{{ $employee->full_name }}</h4>
                <p style="opacity:.8;margin:2px 0 0;font-size:.85rem;">{{ $employee->designation ?? 'Employee' }} &bull; <span style="font-family:monospace;">{{ $employee->employee_id }}</span></p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <a href="{{ route('admin.employees.performance', $employee) }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
                <i class="bi bi-graph-up me-1"></i>Performance
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left Sidebar --}}
    <div class="col-lg-4">
        <div class="info-card text-center" style="padding:24px 20px;">
            <img src="{{ $employee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($employee->full_name).'&size=100&background=6366f1&color=fff' }}"
                class="rounded-circle mx-auto mb-3"
                style="width:88px;height:88px;border:3px solid #e0e7ff;object-fit:cover;display:block;" alt="">
            <div style="font-size:1.05rem;font-weight:800;color:#111827;">{{ $employee->full_name }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">{{ $employee->designation ?? 'Employee' }}</div>
            <div style="font-size:.78rem;color:#9ca3af;font-family:monospace;margin-top:2px;">{{ $employee->employee_id }}</div>
            <span class="spill spill-{{ $status === 'active' ? 'active' : 'inactive' }}" style="margin-top:10px;">{{ ucfirst($status) }}</span>

            <div style="border-top:1px solid #f3f4f6;margin-top:20px;padding-top:16px;text-align:left;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <i class="bi bi-envelope" style="color:#9ca3af;width:16px;"></i>
                    <span style="font-size:.82rem;color:#374151;">{{ $employee->email }}</span>
                </div>
                @if($employee->user->phone)
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <i class="bi bi-telephone" style="color:#9ca3af;width:16px;"></i>
                    <span style="font-size:.82rem;color:#374151;">{{ $employee->user->phone }}</span>
                </div>
                @endif
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <i class="bi bi-building" style="color:#9ca3af;width:16px;"></i>
                    <span style="font-size:.82rem;color:#374151;">{{ $employee->department->name ?? 'N/A' }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <i class="bi bi-geo-alt" style="color:#9ca3af;width:16px;"></i>
                    <span style="font-size:.82rem;color:#374151;">{{ ucfirst($employee->work_location ?? 'office') }}</span>
                </div>
            </div>
        </div>

        <div class="info-card mt-3">
            <div class="info-card-hdr"><i class="bi bi-calendar3 me-2"></i>Leave Balance</div>
            <div class="info-card-body">
                <dl class="dl">
                    <dt>Annual Leave</dt>
                    <dd><span style="color:#16a34a;font-weight:700;">{{ $employee->annual_leave_balance ?? 0 }}</span> days</dd>
                    <dt>Sick Leave</dt>
                    <dd><span style="color:#0ea5e9;font-weight:700;">{{ $employee->sick_leave_balance ?? 0 }}</span> days</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="col-lg-8">
        <div class="info-card mb-3">
            <div class="info-card-hdr"><i class="bi bi-briefcase me-2"></i>Employment Details</div>
            <div class="info-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div style="font-size:.74rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Employee ID</div>
                        <div style="font-weight:700;font-family:monospace;color:#4f46e5;">{{ $employee->employee_id }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:.74rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Employment Type</div>
                        <div style="font-weight:600;color:#374151;">{{ ucfirst(str_replace('_', ' ', $employee->employment_type ?? 'full_time')) }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:.74rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Department</div>
                        <div style="font-weight:600;color:#374151;">{{ $employee->department->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:.74rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Manager</div>
                        <div style="font-weight:600;color:#374151;">{{ $employee->manager->full_name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:.74rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Performance Score</div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                <div style="width:{{ $score }}%;height:100%;background:{{ $scoreColor }};border-radius:3px;"></div>
                            </div>
                            <span style="font-weight:700;color:{{ $scoreColor }};font-size:.83rem;">{{ $score }}%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:.74rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Work Location</div>
                        <div style="font-weight:600;color:#374151;">{{ ucfirst($employee->work_location ?? 'office') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Attendance --}}
        <div class="table-card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">Recent Attendance</span>
                <a href="{{ route('admin.attendance.index') }}?employee={{ $employee->id }}" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;border-radius:7px;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendance ?? [] as $att)
                        @php
                            $attColors = ['present'=>'success','absent'=>'danger','late'=>'warning','half_day'=>'info'];
                            $attSt = $att->status ?? 'present';
                        @endphp
                        <tr>
                            <td style="font-weight:600;font-size:.85rem;">{{ \Carbon\Carbon::parse($att->date)->format('d M Y') }}</td>
                            <td style="font-size:.83rem;">{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '—' }}</td>
                            <td style="font-size:.83rem;">{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '—' }}</td>
                            <td style="font-size:.83rem;">{{ $att->work_hours ? number_format($att->work_hours, 1) . 'h' : '—' }}</td>
                            <td><span class="spill spill-{{ $attColors[$attSt] ?? 'secondary' }}" style="font-size:.7rem;">{{ ucfirst($attSt) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5">
                            <div class="empty-state" style="padding:16px 0;"><i class="bi bi-calendar-x"></i><p>No attendance records</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Tasks --}}
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">Assigned Tasks</span>
                <a href="{{ route('admin.tasks.index') }}?employee={{ $employee->id }}" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;border-radius:7px;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTasks ?? [] as $task)
                        @php
                            $pMap = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
                            $sMap = ['pending'=>'secondary','assigned'=>'info','in_progress'=>'processing','review'=>'warning','completed'=>'success','cancelled'=>'cancelled'];
                        @endphp
                        <tr>
                            <td style="font-weight:600;font-size:.85rem;max-width:160px;">{{ Str::limit($task->title, 30) }}</td>
                            <td><span class="spill spill-{{ $pMap[$task->priority] ?? 'secondary' }}" style="font-size:.7rem;">{{ ucfirst($task->priority) }}</span></td>
                            <td style="font-size:.82rem;color:#6b7280;">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '—' }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;min-width:70px;">
                                    <div style="flex:1;height:5px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                        <div style="width:{{ $task->progress ?? 0 }}%;height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:3px;"></div>
                                    </div>
                                    <span style="font-size:.72rem;color:#6b7280;">{{ $task->progress ?? 0 }}%</span>
                                </div>
                            </td>
                            <td><span class="spill spill-{{ $sMap[$task->status] ?? 'secondary' }}" style="font-size:.7rem;">{{ ucwords(str_replace('_',' ',$task->status)) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5">
                            <div class="empty-state" style="padding:16px 0;"><i class="bi bi-list-task"></i><p>No tasks assigned</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
