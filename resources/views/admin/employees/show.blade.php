@extends('layouts.app')
@section('title', 'Employee Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')

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
            <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#assignTaskModal"
                style="background:rgba(255,255,255,.22);color:#fff;border:1.5px solid rgba(255,255,255,.4);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
                <i class="bi bi-list-task me-1"></i>Assign Task
            </button>
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
        <div class="card-glass p-4 text-center">
            <img src="{{ $employee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($employee->full_name).'&size=100&background=6366f1&color=fff' }}"
                class="rounded-circle mx-auto mb-3"
                style="width:88px;height:88px;border:3px solid #e0e7ff;object-fit:cover;display:block;" alt="">
            <div style="font-size:1.05rem;font-weight:800;color:#111827;">{{ $employee->full_name }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">{{ $employee->designation ?? 'Employee' }}</div>
            <div style="font-size:.78rem;color:#9ca3af;font-family:monospace;margin-top:2px;">{{ $employee->employee_id }}</div>
            <span class="spill spill-{{ $status === 'active' ? 'active' : 'inactive' }}" style="margin-top:10px;display:inline-block;">{{ ucfirst($status) }}</span>

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

        <div class="card-glass overflow-hidden mt-3">
            <div style="background:linear-gradient(135deg,#16a34a,#059669);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar3" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Leave Balance</span>
                </div>
            </div>
            <div class="p-3">
                <dl class="dl mb-0">
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
        <div class="card-glass overflow-hidden mb-3">
            <div style="background:linear-gradient(135deg,#0891b2,#2563eb);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-briefcase-fill" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Employment Details</span>
                </div>
            </div>
            <div class="p-4">
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

{{-- ═══════════════════════════════════════════
     Assign Task Modal
════════════════════════════════════════════ --}}
@php $projects = \App\Models\Project::where('status','active')->orderBy('name')->get(); @endphp

<div class="modal fade" id="assignTaskModal" tabindex="-1" aria-labelledby="assignTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:18px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.18);">

            {{-- Header --}}
            <div class="modal-header" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:18px 18px 0 0;padding:20px 28px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:rgba(255,255,255,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-list-task" style="color:#fff;font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="assignTaskModalLabel" style="color:#fff;">Assign New Task</h5>
                        <div style="font-size:.78rem;color:rgba(255,255,255,.75);margin-top:2px;">
                            Assigning to <strong>{{ $employee->full_name }}</strong>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body px-4 py-4">

                {{-- Success / Error alert (hidden by default) --}}
                <div id="taskAlertBox" class="alert d-none mb-3" role="alert"></div>

                <div class="row g-3">
                    {{-- Task Title --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:.82rem;">Task Title <span class="text-danger">*</span></label>
                        <input type="text" id="taskTitle" class="form-control"
                               placeholder="e.g. Milk collection morning route, Feed stock audit…" maxlength="255">
                        <div class="invalid-feedback" id="taskTitleErr"></div>
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:.82rem;">Description</label>
                        <textarea id="taskDescription" class="form-control" rows="3"
                                  placeholder="Detailed instructions or context for this task…"></textarea>
                    </div>

                    {{-- Priority + Due Date --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" style="font-size:.82rem;">Priority <span class="text-danger">*</span></label>
                        <select id="taskPriority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" style="font-size:.82rem;">Due Date</label>
                        <input type="date" id="taskDueDate" class="form-control"
                               min="{{ now()->addDay()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" style="font-size:.82rem;">Estimated Hours</label>
                        <input type="number" id="taskEstHours" class="form-control" min="0" placeholder="e.g. 4">
                    </div>

                    {{-- Project (optional) --}}
                    @if($projects->isNotEmpty())
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:.82rem;">Link to Project <span class="text-muted fw-normal">(optional)</span></label>
                        <select id="taskProjectId" class="form-select">
                            <option value="">— No project —</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Recurring toggle --}}
                    <div class="col-12">
                        <div style="background:#f5f3ff;border:1.5px solid #e0e7ff;border-radius:12px;padding:14px 16px;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div style="font-size:.84rem;font-weight:700;color:#4f46e5;">
                                        <i class="bi bi-arrow-repeat me-1"></i>Repeat this task
                                    </div>
                                    <div style="font-size:.74rem;color:#6b7280;margin-top:2px;">
                                        Automatically re-assigns this task every day / week / month
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="taskIsRecurring"
                                           style="width:42px;height:22px;cursor:pointer;" onchange="toggleRecurring(this.checked)">
                                </div>
                            </div>

                            {{-- Recurring options (hidden by default) --}}
                            <div id="recurringOptions" style="display:none;margin-top:14px;border-top:1px solid #e0e7ff;padding-top:14px;">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold" style="font-size:.78rem;color:#4f46e5;">Repeat Every</label>
                                        <select id="taskRecurrenceType" class="form-select form-select-sm" style="border-color:#c7d2fe;">
                                            <option value="daily" selected>Daily — every day</option>
                                            <option value="weekly">Weekly — same day each week</option>
                                            <option value="monthly">Monthly — same date each month</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold" style="font-size:.78rem;color:#4f46e5;">End Date <span class="fw-normal text-muted">(optional)</span></label>
                                        <input type="date" id="taskRecurringEndsAt" class="form-control form-control-sm"
                                               style="border-color:#c7d2fe;"
                                               min="{{ now()->addDay()->format('Y-m-d') }}">
                                        <div style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Leave blank = repeat indefinitely</div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div style="font-size:.7rem;color:#6366f1;background:#eef2ff;border-radius:8px;padding:6px 8px;text-align:center;line-height:1.4;">
                                            <i class="bi bi-infinity d-block mb-1" style="font-size:1rem;"></i>
                                            Auto
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Priority legend --}}
                <div class="d-flex gap-3 mt-3" style="font-size:.72rem;color:#9ca3af;">
                    <span><i class="bi bi-circle-fill me-1" style="color:#16a34a;font-size:.5rem;vertical-align:middle;"></i>Low</span>
                    <span><i class="bi bi-circle-fill me-1" style="color:#d97706;font-size:.5rem;vertical-align:middle;"></i>Medium</span>
                    <span><i class="bi bi-circle-fill me-1" style="color:#dc2626;font-size:.5rem;vertical-align:middle;"></i>High</span>
                    <span><i class="bi bi-circle-fill me-1" style="color:#7c3aed;font-size:.5rem;vertical-align:middle;"></i>Urgent</span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 28px;border-radius:0 0 18px 18px;">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="assignTaskBtn" class="btn btn-primary-grad px-5 fw-semibold">
                    <i class="bi bi-send me-2"></i>Assign Task
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('assignTaskBtn').addEventListener('click', function () {
    const btn   = this;
    const title = document.getElementById('taskTitle').value.trim();
    const alert = document.getElementById('taskAlertBox');

    // Client-side validation
    if (!title) {
        document.getElementById('taskTitle').classList.add('is-invalid');
        document.getElementById('taskTitleErr').textContent = 'Task title is required.';
        return;
    }
    document.getElementById('taskTitle').classList.remove('is-invalid');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Assigning…';

    const isRecurring = document.getElementById('taskIsRecurring').checked;

    const payload = {
        _token:             '{{ csrf_token() }}',
        title:              title,
        description:        document.getElementById('taskDescription').value,
        assigned_to:        {{ $employee->id }},
        priority:           document.getElementById('taskPriority').value,
        due_date:           document.getElementById('taskDueDate').value || null,
        estimated_hours:    document.getElementById('taskEstHours').value || null,
        project_id:         document.getElementById('taskProjectId') ? document.getElementById('taskProjectId').value || null : null,
        is_recurring:       isRecurring,
        recurrence_type:    isRecurring ? document.getElementById('taskRecurrenceType').value : null,
        recurring_ends_at:  isRecurring ? (document.getElementById('taskRecurringEndsAt').value || null) : null,
    };

    fetch('{{ route('admin.tasks.store') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i>Assign Task';

        if (data.success) {
            alert.className = 'alert alert-success mb-3';
            alert.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Task "<strong>' + (data.task?.title ?? title) + '</strong>" assigned successfully!';
            // Reset form
            document.getElementById('taskTitle').value           = '';
            document.getElementById('taskDescription').value     = '';
            document.getElementById('taskPriority').value        = 'medium';
            document.getElementById('taskDueDate').value         = '';
            document.getElementById('taskEstHours').value        = '';
            document.getElementById('taskIsRecurring').checked   = false;
            document.getElementById('taskRecurrenceType').value  = 'daily';
            document.getElementById('taskRecurringEndsAt').value = '';
            document.getElementById('recurringOptions').style.display = 'none';
            if (document.getElementById('taskProjectId')) document.getElementById('taskProjectId').value = '';
            // Auto-close after 1.8s and reload tasks section
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('assignTaskModal')).hide();
                alert.className = 'alert d-none mb-3';
                window.location.reload();
            }, 1800);
        } else {
            alert.className = 'alert alert-danger mb-3';
            alert.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + (data.message ?? 'Something went wrong. Please try again.');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i>Assign Task';
        alert.className = 'alert alert-danger mb-3';
        alert.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Network error. Please try again.';
    });
});

function toggleRecurring(checked) {
    document.getElementById('recurringOptions').style.display = checked ? 'block' : 'none';
}

// Clear validation on typing
document.getElementById('taskTitle').addEventListener('input', function () {
    this.classList.remove('is-invalid');
});

// Clear alert when modal closes
document.getElementById('assignTaskModal').addEventListener('hidden.bs.modal', function () {
    const alert = document.getElementById('taskAlertBox');
    alert.className = 'alert d-none mb-3';
    alert.innerHTML = '';
    document.getElementById('taskTitle').classList.remove('is-invalid');
});
</script>
@endpush

@endsection
