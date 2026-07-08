@extends('layouts.app')
@section('title', 'Project Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.projects.index') }}">Projects</a></li>
    <li class="breadcrumb-item active">{{ $project->name }}</li>
@endsection

@section('content')

@php
    $statusColors = ['planning'=>'info','active'=>'success','on_hold'=>'warning','completed'=>'secondary','cancelled'=>'danger'];
    $statusGrad = ['planning'=>'#0ea5e9,#38bdf8','active'=>'#16a34a,#22c55e','on_hold'=>'#f59e0b,#fbbf24','completed'=>'#6366f1,#8b5cf6','cancelled'=>'#dc2626,#ef4444'];
    $ps = $project->status ?? 'planning';
    $grad = $statusGrad[$ps] ?? '#4f46e5,#7c3aed';
    $total = $project->tasks->count();
    $done  = $project->tasks->where('status', 'completed')->count();
    $pct   = $total > 0 ? round(($done / $total) * 100) : 0;
    $pColor = $pct >= 75 ? '#16a34a' : ($pct >= 40 ? '#f59e0b' : '#dc2626');
    $priorityBorder = ['low'=>'#16a34a','medium'=>'#f59e0b','high'=>'#ef4444','urgent'=>'#7f1d1d'];
    $priorityColors = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
    $statusTaskColors = ['pending'=>'secondary','assigned'=>'info','in_progress'=>'warning','review'=>'info','completed'=>'success','cancelled'=>'danger'];
    $taskStatuses = ['pending'=>'Pending','assigned'=>'Assigned','in_progress'=>'In Progress','review'=>'In Review','completed'=>'Completed','cancelled'=>'Cancelled'];

    // Unique assignees for team card
    $teamMembers = $project->tasks->pluck('assignedTo')->filter()->unique('id')->values();
@endphp

<style>
.proj-hero-actions .btn { font-size:.8rem; font-weight:700; border-radius:9px; }
.proj-hero-actions .btn-outline-light { border-color:rgba(255,255,255,.45); background:rgba(255,255,255,.12); backdrop-filter:blur(4px); }
.proj-hero-actions .btn-outline-light:hover { background:rgba(255,255,255,.22); color:#fff; }

.info-dl { display:grid; grid-template-columns:auto 1fr; gap:8px 16px; align-items:start; }
.info-dl dt { font-size:.72rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; padding-top:2px; }
.info-dl dd { font-size:.84rem; font-weight:600; color:#111827; margin:0; }

.task-row { border-left:3px solid transparent; transition:background .12s; }
.task-row:hover { background:#f5f3ff !important; }
.task-row.pri-low    { border-left-color:#16a34a; }
.task-row.pri-medium { border-left-color:#f59e0b; }
.task-row.pri-high   { border-left-color:#ef4444; }
.task-row.pri-urgent { border-left-color:#7f1d1d; }

.team-avatar { width:34px; height:34px; border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow:0 1px 4px rgba(0,0,0,.12); }
.team-member { display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #f3f4f6; }
.team-member:last-child { border-bottom:none; }

.stat-mini { background:#f8fafc; border-radius:10px; padding:10px 14px; text-align:center; }
.stat-mini .num { font-size:1.3rem; font-weight:800; line-height:1; }
.stat-mini .lbl { font-size:.67rem; text-transform:uppercase; letter-spacing:.04em; color:#9ca3af; margin-top:3px; }

.filter-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:12px; }
.ftab { padding:4px 12px; border-radius:20px; font-size:.77rem; font-weight:700; border:1.5px solid #e5e7eb;
        background:#fff; color:#6b7280; cursor:pointer; transition:all .15s; }
.ftab:hover, .ftab.active { background:#4f46e5; color:#fff; border-color:#4f46e5; }
.ftab.active { box-shadow:0 2px 8px rgba(79,70,229,.25); }

.pbar-wrap { background:#e5e7eb; border-radius:20px; height:8px; overflow:hidden; }
.pbar-fill { height:8px; border-radius:20px; transition:width .4s ease; }
</style>

<a href="{{ route('admin.projects.index') }}" class="back-btn mb-3"><i class="bi bi-arrow-left"></i>Back to Projects</a>

{{-- ── Hero ── --}}
<div class="page-hero mb-4" style="background:linear-gradient(135deg,{{ $grad }});padding:26px 28px;">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div style="flex:1;min-width:0;">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="spill spill-{{ $statusColors[$ps] ?? 'secondary' }}" style="font-size:.72rem;">
                    {{ ucwords(str_replace('_', ' ', $ps)) }}
                </span>
                @if($project->code)
                <span style="background:rgba(255,255,255,.2);color:#fff;padding:2px 10px;border-radius:6px;font-size:.7rem;font-weight:700;font-family:monospace;letter-spacing:.04em;">
                    {{ $project->code }}
                </span>
                @endif
                @if($project->end_date && \Carbon\Carbon::parse($project->end_date)->isPast() && $ps !== 'completed')
                <span style="background:rgba(220,38,38,.35);color:#fee2e2;padding:2px 10px;border-radius:6px;font-size:.7rem;font-weight:700;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue
                </span>
                @endif
            </div>
            <h4 style="font-weight:800;color:#fff;margin-bottom:4px;font-size:1.35rem;">{{ $project->name }}</h4>
            <p style="font-size:.83rem;opacity:.8;color:#fff;margin:0;">
                <i class="bi bi-person-fill me-1"></i>{{ $project->manager->name ?? 'No Manager' }}
                @if($project->department)
                &nbsp;<span style="opacity:.5;">·</span>&nbsp;
                <i class="bi bi-building me-1"></i>{{ $project->department->name }}
                @endif
                @if($project->start_date)
                &nbsp;<span style="opacity:.5;">·</span>&nbsp;
                <i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-3 flex-wrap">
            {{-- Stats --}}
            <div class="d-flex align-items-center gap-4">
                <div class="page-hero-stat">
                    <div class="v">{{ $total }}</div>
                    <div class="l">Tasks</div>
                </div>
                <div class="hero-vr"></div>
                <div class="page-hero-stat">
                    <div class="v" style="color:#86efac;">{{ $done }}</div>
                    <div class="l">Done</div>
                </div>
                <div class="hero-vr"></div>
                <div class="page-hero-stat">
                    <div class="v" style="color:#fde047;">{{ $pct }}%</div>
                    <div class="l">Progress</div>
                </div>
            </div>
            {{-- Actions --}}
            <div class="proj-hero-actions d-flex gap-2 flex-wrap ms-1">
                <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#editProjectModal">
                    <i class="bi bi-pencil-square me-1"></i>Edit
                </button>
                <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="bi bi-plus-circle me-1"></i>Add Task
                </button>
            </div>
        </div>
    </div>

    {{-- Progress Bar inside hero --}}
    <div class="mt-4" style="position:relative;z-index:1;">
        <div class="d-flex justify-content-between mb-1">
            <span style="font-size:.75rem;color:rgba(255,255,255,.75);">Overall Progress</span>
            <span style="font-size:.75rem;font-weight:800;color:#fde047;">{{ $pct }}%</span>
        </div>
        <div style="background:rgba(255,255,255,.2);border-radius:20px;height:7px;overflow:hidden;">
            <div style="height:7px;border-radius:20px;width:{{ $pct }}%;background:rgba(255,255,255,.9);transition:width .5s;"></div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- ── Left Sidebar ── --}}
    <div class="col-lg-4">

        {{-- Project Info --}}
        <div class="info-card mb-3">
            <div class="info-card-hdr"><i class="bi bi-folder2-open me-2"></i>Project Info</div>
            <div class="info-card-body">
                <dl class="info-dl mb-0">
                    @if($project->code)
                    <dt>Code</dt>
                    <dd><span style="font-family:monospace;background:#f3f4f6;padding:2px 8px;border-radius:5px;font-size:.83rem;">{{ $project->code }}</span></dd>
                    @endif

                    <dt>Manager</dt>
                    <dd>
                        @if($project->manager)
                        <span style="display:flex;align-items:center;gap:6px;">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($project->manager->name) }}&size=22&background=4f46e5&color=fff"
                                style="width:22px;height:22px;border-radius:50%;">
                            {{ $project->manager->name }}
                        </span>
                        @else —
                        @endif
                    </dd>

                    @if($project->department)
                    <dt>Department</dt>
                    <dd><i class="bi bi-building me-1 text-muted"></i>{{ $project->department->name }}</dd>
                    @endif

                    <dt>Start Date</dt>
                    <dd><i class="bi bi-calendar-check me-1 text-muted"></i>{{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}</dd>

                    <dt>End Date</dt>
                    <dd>
                        @if($project->end_date)
                            @php $endDate = \Carbon\Carbon::parse($project->end_date); @endphp
                            <span style="color:{{ ($ps !== 'completed' && $endDate->isPast()) ? '#dc2626' : 'inherit' }};display:flex;align-items:center;gap:6px;">
                                <i class="bi bi-calendar-x me-1 {{ ($ps !== 'completed' && $endDate->isPast()) ? 'text-danger' : 'text-muted' }}"></i>
                                {{ $endDate->format('M d, Y') }}
                                @if($ps !== 'completed' && $endDate->isPast())
                                    <span class="spill spill-danger" style="font-size:.63rem;">Overdue</span>
                                @endif
                            </span>
                        @else —
                        @endif
                    </dd>

                    @if($project->budget)
                    <dt>Budget</dt>
                    <dd><i class="bi bi-currency-rupee me-1 text-muted"></i>{{ number_format($project->budget) }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Task Breakdown --}}
        <div class="info-card mb-3">
            <div class="info-card-hdr"><i class="bi bi-bar-chart-steps me-2"></i>Task Breakdown</div>
            <div class="info-card-body">
                {{-- Mini stat grid --}}
                @php
                    $breakStats = [
                        'completed'   => ['#16a34a','Completed','bi-check-circle-fill'],
                        'in_progress' => ['#6366f1','In Progress','bi-play-circle-fill'],
                        'review'      => ['#f59e0b','In Review','bi-eye-fill'],
                        'pending'     => ['#9ca3af','Pending','bi-hourglass-split'],
                    ];
                @endphp
                <div class="row g-2 mb-3">
                    @foreach($breakStats as $st => [$color, $label, $icon])
                    @php $cnt = $project->tasks->where('status', $st)->count(); @endphp
                    <div class="col-6">
                        <div class="stat-mini">
                            <div class="num" style="color:{{ $color }};">{{ $cnt }}</div>
                            <div class="lbl">{{ $label }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Progress bar with label --}}
                <div class="d-flex justify-content-between mb-1" style="font-size:.78rem;">
                    <span style="color:#6b7280;">{{ $done }} of {{ $total }} completed</span>
                    <span style="font-weight:800;color:{{ $pColor }};">{{ $pct }}%</span>
                </div>
                <div class="pbar-wrap">
                    <div class="pbar-fill" style="width:{{ $pct }}%;background:{{ $pColor }};"></div>
                </div>
            </div>
        </div>

        {{-- Team Members --}}
        @if($teamMembers->count() > 0)
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-people me-2"></i>Team Members <span style="font-size:.75rem;color:#9ca3af;font-weight:600;margin-left:4px;">{{ $teamMembers->count() }}</span></div>
            <div class="info-card-body" style="padding:12px 16px;">
                @foreach($teamMembers as $member)
                @php
                    $memberTasks = $project->tasks->where('assigned_to', $member->id);
                    $memberDone  = $memberTasks->where('status', 'completed')->count();
                @endphp
                <div class="team-member">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($member->full_name ?? 'U') }}&size=68&background=6366f1&color=fff"
                        class="team-avatar">
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:.84rem;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $member->full_name ?? '—' }}
                        </div>
                        <div style="font-size:.72rem;color:#9ca3af;">
                            {{ $memberTasks->count() }} task{{ $memberTasks->count() !== 1 ? 's' : '' }}
                            &middot; {{ $memberDone }} done
                        </div>
                    </div>
                    <span class="spill spill-{{ $memberDone === $memberTasks->count() && $memberTasks->count() > 0 ? 'success' : 'secondary' }}" style="font-size:.67rem;">
                        {{ $memberTasks->count() > 0 ? round($memberDone / $memberTasks->count() * 100) : 0 }}%
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- ── Right Column ── --}}
    <div class="col-lg-8">

        {{-- Description --}}
        @if($project->description)
        <div class="info-card mb-3">
            <div class="info-card-hdr"><i class="bi bi-text-paragraph me-2"></i>Description</div>
            <div class="info-card-body">
                <p style="font-size:.87rem;color:#374151;line-height:1.7;margin:0;">{{ $project->description }}</p>
            </div>
        </div>
        @endif

        {{-- Tasks Table --}}
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <span class="card-title"><i class="bi bi-list-task me-2 text-indigo-600" style="color:#4f46e5;"></i>Tasks <span style="font-size:.78rem;color:#9ca3af;font-weight:600;">{{ $total }}</span></span>
                <button class="btn btn-sm btn-primary-grad px-3" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="bi bi-plus-circle me-1"></i>Add Task
                </button>
            </div>

            {{-- Filter Tabs --}}
            @if($total > 0)
            <div style="padding:10px 20px 0;border-bottom:1px solid #f3f4f6;">
                <div class="filter-tabs" id="taskFilterTabs">
                    <button class="ftab active" data-filter="all">All <span style="font-size:.7rem;opacity:.7;">{{ $total }}</span></button>
                    @foreach(['in_progress'=>'In Progress','review'=>'In Review','pending'=>'Pending','completed'=>'Completed'] as $fst => $flabel)
                    @php $fCnt = $project->tasks->where('status', $fst)->count(); @endphp
                    @if($fCnt > 0)
                    <button class="ftab" data-filter="{{ $fst }}">{{ $flabel }} <span style="font-size:.7rem;opacity:.7;">{{ $fCnt }}</span></button>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table modern-table mb-0" id="projectTasksTable">
                    <thead>
                        <tr>
                            <th style="width:40%;">Task</th>
                            <th>Assigned To</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->tasks as $task)
                        @php
                            $taskOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed';
                            $priClass = 'pri-' . ($task->priority ?? 'medium');
                        @endphp
                        <tr class="task-row {{ $priClass }}" data-status="{{ $task->status }}"
                            style="{{ $taskOverdue ? 'background:#fff8f8;' : '' }}">
                            <td>
                                <div style="font-weight:700;font-size:.87rem;color:#111827;line-height:1.3;">
                                    {{ Str::limit($task->title, 48) }}
                                </div>
                                @if($task->description)
                                <div style="font-size:.75rem;color:#9ca3af;margin-top:2px;">{{ Str::limit($task->description, 60) }}</div>
                                @endif
                                @if($taskOverdue)
                                <span class="spill spill-danger mt-1" style="font-size:.63rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue</span>
                                @endif
                            </td>
                            <td>
                                @if($task->assignedTo)
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($task->assignedTo->full_name ?? 'U') }}&size=52&background=6366f1&color=fff"
                                        style="width:26px;height:26px;border-radius:50%;">
                                    <span style="font-size:.83rem;color:#374151;font-weight:600;">{{ $task->assignedTo->full_name ?? '—' }}</span>
                                </div>
                                @else
                                <span style="font-size:.83rem;color:#9ca3af;">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="spill spill-{{ $priorityColors[$task->priority] ?? 'secondary' }}" style="font-size:.69rem;">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td style="font-size:.81rem;color:{{ $taskOverdue ? '#dc2626' : '#6b7280' }};font-weight:{{ $taskOverdue ? '700' : '500' }};">
                                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '—' }}
                            </td>
                            <td>
                                <span class="spill spill-{{ $statusTaskColors[$task->status] ?? 'secondary' }}" style="font-size:.69rem;">
                                    {{ $taskStatuses[$task->status] ?? ucwords(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('employee.tasks.show', $task) }}" class="act-btn act-view" title="View Task"><i class="bi bi-eye"></i></a>
                                    <button class="act-btn act-delete" onclick="deleteProjectTask({{ $task->id }})" title="Delete Task"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state" style="padding:48px 0;">
                                    <i class="bi bi-list-task" style="font-size:2.2rem;color:#d1d5db;"></i>
                                    <p style="color:#9ca3af;font-size:.87rem;margin:10px 0 16px;">No tasks yet for this project</p>
                                    <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                        <i class="bi bi-plus-circle me-1"></i>Create First Task
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Add Task Modal ── --}}
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:18px 24px;">
                <h6 class="modal-title fw-bold" style="font-size:.97rem;">
                    <i class="bi bi-plus-circle me-2" style="color:#4f46e5;"></i>Add Task to <span style="color:#4f46e5;">{{ $project->name }}</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTaskForm">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div class="modal-body" style="padding:20px 24px;">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Task Title <span class="req">*</span></label>
                            <input type="text" name="title" class="form-control" required
                                placeholder="e.g. Design homepage wireframes"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Assign To <span class="req">*</span></label>
                            <select name="assigned_to" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->designation }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Priority <span class="req">*</span></label>
                            <select name="priority" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Due Date</label>
                            <input type="date" name="due_date" class="form-control"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Estimated Hours</label>
                            <input type="number" name="estimated_hours" class="form-control" min="0" placeholder="e.g. 8"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe what needs to be done..."
                                style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:14px 24px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="addTaskBtn">
                        <i class="bi bi-plus-circle me-1"></i>Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit Project Modal ── --}}
<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:18px 24px;">
                <h6 class="modal-title fw-bold" style="font-size:.97rem;">
                    <i class="bi bi-pencil-square me-2" style="color:#4f46e5;"></i>Edit Project
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProjectForm">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding:20px 24px;">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="flabel">Project Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" required value="{{ $project->name }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status <span class="req">*</span></label>
                            <select name="status" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach(['planning'=>'Planning','active'=>'Active','on_hold'=>'On Hold','completed'=>'Completed','cancelled'=>'Cancelled'] as $val => $lbl)
                                <option value="{{ $val }}" {{ $project->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Manager</label>
                            <select name="manager_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">No Manager</option>
                                @foreach(\App\Models\User::active()->orderBy('name')->get() as $mgr)
                                <option value="{{ $mgr->id }}" {{ $project->manager_id == $mgr->id ? 'selected' : '' }}>{{ $mgr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Department</label>
                            <select name="department_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">No Department</option>
                                @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                <option value="{{ $dept->id }}" {{ $project->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Start Date <span class="req">*</span></label>
                            <input type="date" name="start_date" class="form-control" required
                                value="{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '' }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">End Date</label>
                            <input type="date" name="end_date" class="form-control"
                                value="{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '' }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Budget (₹)</label>
                            <input type="number" name="budget" class="form-control" min="0"
                                value="{{ $project->budget }}" placeholder="e.g. 500000"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Project Code</label>
                            <input type="text" name="code" class="form-control"
                                value="{{ $project->code }}" placeholder="e.g. PRJ-001"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;">{{ $project->description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:14px 24px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="editProjectBtn">
                        <i class="bi bi-check-circle me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Task filter tabs ──
document.querySelectorAll('#taskFilterTabs .ftab').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#taskFilterTabs .ftab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('#projectTasksTable tbody tr[data-status]').forEach(row => {
            row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
        });
    });
});

// ── Delete Task ──
function deleteProjectTask(id) {
    APP.confirm('Delete this task?', 'This action cannot be undone.', function() {
        fetch(`/admin/tasks/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            APP.toast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 800);
        });
    });
}

// ── Add Task ──
document.getElementById('addTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('addTaskBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Creating…';

    fetch('{{ route('admin.tasks.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new FormData(form)
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (ok && data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addTaskModal')).hide();
            APP.toast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else if (data.errors) {
            const first = Object.values(data.errors)[0];
            APP.toast(Array.isArray(first) ? first[0] : first, 'error');
        } else {
            APP.toast(data.message || 'Failed to create task.', 'error');
        }
    })
    .catch(() => APP.toast('Something went wrong.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-plus-circle me-1"></i>Create Task';
    });
});

// ── Edit Project ──
document.getElementById('editProjectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('editProjectBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Saving…';

    const data = new FormData(form);

    fetch('{{ route('admin.projects.update', $project) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: data
    })
    .then(r => r.json().then(d => ({ ok: r.ok, d })))
    .then(({ ok, d }) => {
        if (ok && d.success) {
            bootstrap.Modal.getInstance(document.getElementById('editProjectModal')).hide();
            APP.toast(d.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else if (d.errors) {
            const first = Object.values(d.errors)[0];
            APP.toast(Array.isArray(first) ? first[0] : first, 'error');
        } else {
            APP.toast(d.message || 'Failed to update project.', 'error');
        }
    })
    .catch(() => APP.toast('Something went wrong.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save Changes';
    });
});
</script>
@endpush
