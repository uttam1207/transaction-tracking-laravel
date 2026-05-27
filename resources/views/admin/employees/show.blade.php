@extends('layouts.app')

@section('title', 'Employee Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.employees.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Employees
        </a>
        <h4 class="mb-0 fw-bold mt-1">Employee Profile</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('admin.employees.performance', $employee) }}" class="btn btn-outline-success">
            <i class="bi bi-graph-up me-1"></i>Performance
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Profile Card --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <img src="{{ $employee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($employee->full_name).'&size=100&background=6366f1&color=fff' }}"
                class="rounded-circle mx-auto mb-3" width="100" height="100">
            <h5 class="fw-bold mb-1">{{ $employee->full_name }}</h5>
            <p class="text-muted mb-1">{{ $employee->designation ?? 'Employee' }}</p>
            <p class="text-muted small">{{ $employee->employee_id }}</p>
            @php $status = $employee->status ?? 'active'; @endphp
            <span class="badge bg-{{ $status === 'active' ? 'success' : 'danger' }} px-3">
                {{ ucfirst($status) }}
            </span>

            <hr>

            <div class="text-start">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-envelope text-muted"></i>
                    <small>{{ $employee->email }}</small>
                </div>
                @if($employee->user->phone)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-telephone text-muted"></i>
                    <small>{{ $employee->user->phone }}</small>
                </div>
                @endif
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-building text-muted"></i>
                    <small>{{ $employee->department->name ?? 'N/A' }}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt text-muted"></i>
                    <small>{{ ucfirst($employee->work_location ?? 'office') }}</small>
                </div>
            </div>
        </div>

        {{-- Leave Balance --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-transparent fw-semibold">Leave Balance</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Annual Leave</span>
                    <span class="fw-semibold">{{ $employee->annual_leave_balance ?? 0 }} days</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Sick Leave</span>
                    <span class="fw-semibold">{{ $employee->sick_leave_balance ?? 0 }} days</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Details Panel --}}
    <div class="col-lg-8">
        {{-- Employment Details --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Employment Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Employee ID</label>
                        <div class="fw-semibold">{{ $employee->employee_id }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Employment Type</label>
                        <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $employee->employment_type ?? 'full_time')) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Department</label>
                        <div class="fw-semibold">{{ $employee->department->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Manager</label>
                        <div class="fw-semibold">{{ $employee->manager->full_name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Performance Score</label>
                        <div class="d-flex align-items-center gap-2">
                            @php $score = $employee->performance_score ?? 0; @endphp
                            <div class="progress flex-fill" style="height: 8px;">
                                <div class="progress-bar bg-{{ $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger') }}"
                                    style="width: {{ $score }}%"></div>
                            </div>
                            <strong>{{ $score }}%</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Work Location</label>
                        <div class="fw-semibold">{{ ucfirst($employee->work_location ?? 'office') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Attendance --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Recent Attendance</span>
                <a href="{{ route('admin.attendance.index') }}?employee={{ $employee->id }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
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
                            <tr>
                                <td>{{ $att->date }}</td>
                                <td>{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('h:i A') : '—' }}</td>
                                <td>{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('h:i A') : '—' }}</td>
                                <td>{{ $att->work_hours ? number_format($att->work_hours, 1) . 'h' : '—' }}</td>
                                <td>
                                    @php
                                        $attStatus = $att->status ?? 'present';
                                        $colors = ['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'half_day' => 'info'];
                                    @endphp
                                    <span class="badge bg-{{ $colors[$attStatus] ?? 'secondary' }}">
                                        {{ ucfirst($attStatus) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No attendance records</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Tasks --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Assigned Tasks</span>
                <a href="{{ route('admin.tasks.index') }}?employee={{ $employee->id }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
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
                            <tr>
                                <td>{{ Str::limit($task->title, 30) }}</td>
                                <td>
                                    @php
                                        $priorityColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $priorityColors[$task->priority] ?? 'secondary' }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>{{ $task->due_date ?? '—' }}</td>
                                <td>
                                    <div class="progress" style="height: 6px; min-width: 60px;">
                                        <div class="progress-bar" style="width: {{ $task->progress ?? 0 }}%"></div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No tasks assigned</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
