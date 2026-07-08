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
    $grad = $statusGrad[$ps] ?? '4f46e5,#7c3aed';
    $total = $project->tasks->count();
    $done  = $project->tasks->where('status', 'completed')->count();
    $pct   = $total > 0 ? round(($done / $total) * 100) : 0;
    $pColor = $pct >= 75 ? '#16a34a' : ($pct >= 40 ? '#f59e0b' : '#dc2626');
@endphp

<a href="{{ route('admin.projects.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Projects</a>

<div class="page-hero" style="background:linear-gradient(135deg,{{ $grad }});">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="spill spill-{{ $statusColors[$ps] ?? 'secondary' }}" style="font-size:.72rem;">
                    {{ ucwords(str_replace('_', ' ', $ps)) }}
                </span>
                @if($project->code)
                <span style="background:rgba(255,255,255,.2);color:#fff;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:700;font-family:monospace;">
                    {{ $project->code }}
                </span>
                @endif
            </div>
            <h4 style="font-weight:800;color:#fff;margin-bottom:4px;">{{ $project->name }}</h4>
            <p style="font-size:.83rem;opacity:.75;color:#fff;margin:0;">
                <i class="bi bi-person me-1"></i>{{ $project->manager->name ?? 'No Manager' }}
                @if($project->department)&nbsp;·&nbsp;<i class="bi bi-building me-1"></i>{{ $project->department->name }}@endif
            </p>
        </div>
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
    </div>
</div>

<div class="row g-3">
    {{-- Left Sidebar --}}
    <div class="col-lg-4">
        {{-- Project Info --}}
        <div class="info-card mb-3">
            <div class="info-card-hdr"><i class="bi bi-folder2 me-2"></i>Project Info</div>
            <div class="info-card-body">
                <dl class="dl">
                    <dt>Project Code</dt>
                    <dd><span style="font-family:monospace;background:#f3f4f6;padding:2px 8px;border-radius:5px;font-size:.84rem;">{{ $project->code ?? '—' }}</span></dd>

                    <dt>Manager</dt>
                    <dd>{{ $project->manager->name ?? '—' }}</dd>

                    <dt>Department</dt>
                    <dd>{{ $project->department->name ?? '—' }}</dd>

                    <dt>Start Date</dt>
                    <dd>{{ \Carbon\Carbon::parse($project->start_date)->format('M d, Y') }}</dd>

                    <dt>End Date</dt>
                    <dd>
                        @if($project->end_date)
                            @php $endDate = \Carbon\Carbon::parse($project->end_date); @endphp
                            <span style="color:{{ ($ps !== 'completed' && $endDate->isPast()) ? '#dc2626' : 'inherit' }};">
                                {{ $endDate->format('M d, Y') }}
                                @if($ps !== 'completed' && $endDate->isPast())
                                    <span class="spill spill-danger ms-1" style="font-size:.65rem;">Overdue</span>
                                @endif
                            </span>
                        @else
                            —
                        @endif
                    </dd>

                    <dt>Budget</dt>
                    <dd>{{ $project->budget ? '₹'.number_format($project->budget) : '—' }}</dd>

                    <dt>Total Tasks</dt>
                    <dd><strong style="color:#4f46e5;">{{ $total }}</strong></dd>
                </dl>
            </div>
        </div>

        {{-- Overall Progress --}}
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-bar-chart me-2"></i>Overall Progress</div>
            <div class="info-card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:.83rem;color:#6b7280;">{{ $done }} of {{ $total }} tasks completed</span>
                    <span style="font-size:.9rem;font-weight:800;color:{{ $pColor }};">{{ $pct }}%</span>
                </div>
                <div style="background:#e5e7eb;border-radius:8px;height:12px;overflow:hidden;">
                    <div style="height:12px;border-radius:8px;width:{{ $pct }}%;background:{{ $pColor }};transition:width .4s;"></div>
                </div>
                <div class="row g-2 mt-3">
                    @php
                        $statusBreak = ['completed'=>['#16a34a','Completed'], 'in_progress'=>['#6366f1','In Progress'], 'pending'=>['#9ca3af','Pending'], 'review'=>['#f59e0b','In Review']];
                    @endphp
                    @foreach($statusBreak as $st => [$color, $label])
                    @php $cnt = $project->tasks->where('status', $st)->count(); @endphp
                    @if($cnt > 0)
                    <div class="col-6">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div style="width:8px;height:8px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></div>
                            <span style="font-size:.78rem;color:#6b7280;">{{ $label }}</span>
                            <span style="font-size:.78rem;font-weight:700;color:#111827;margin-left:auto;">{{ $cnt }}</span>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div class="col-lg-8">
        @if($project->description)
        <div class="info-card mb-3">
            <div class="info-card-hdr"><i class="bi bi-text-paragraph me-2"></i>Description</div>
            <div class="info-card-body">
                <p style="font-size:.87rem;color:#374151;line-height:1.6;margin:0;">{{ $project->description }}</p>
            </div>
        </div>
        @endif

        {{-- Tasks Table --}}
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">Tasks ({{ $total }})</span>
                <button class="btn btn-sm btn-primary-grad px-3" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="bi bi-plus-circle me-1"></i>Add Task
                </button>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Task</th>
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
                            $pc = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
                            $sc = ['pending'=>'secondary','assigned'=>'info','in_progress'=>'warning','review'=>'info','completed'=>'success','cancelled'=>'danger'];
                            $taskOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed';
                        @endphp
                        <tr style="{{ $taskOverdue ? 'background:#fff5f5;' : '' }}">
                            <td style="font-weight:600;font-size:.87rem;color:#111827;">{{ Str::limit($task->title, 40) }}</td>
                            <td style="font-size:.83rem;color:#374151;">{{ $task->assignedTo->full_name ?? '—' }}</td>
                            <td>
                                <span class="spill spill-{{ $pc[$task->priority] ?? 'secondary' }}" style="font-size:.7rem;">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td style="font-size:.82rem;color:{{ $taskOverdue ? '#dc2626' : '#6b7280' }};font-weight:{{ $taskOverdue ? '700' : '400' }};">
                                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '—' }}
                            </td>
                            <td>
                                <span class="spill spill-{{ $sc[$task->status] ?? 'secondary' }}" style="font-size:.7rem;">
                                    {{ ucwords(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('employee.tasks.show', $task) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                                    <button class="act-btn act-delete" onclick="deleteProjectTask({{ $task->id }})" title="Delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6">
                            <div class="empty-state"><i class="bi bi-list-task"></i><p>No tasks for this project</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Task Modal --}}
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-list-task me-2"></i>Add Task to {{ $project->name }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTaskForm">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Task Title <span class="req">*</span></label>
                            <input type="text" name="title" class="form-control" required
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
                            <textarea name="description" class="form-control" rows="3"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
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

document.getElementById('addTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('[type=submit]');
    btn.disabled = true;

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
    .finally(() => btn.disabled = false);
});
</script>
@endpush
