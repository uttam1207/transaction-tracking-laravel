@extends('layouts.app')
@section('title', 'Tasks')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tasks</li>
@endsection

@section('content')
@php
    $totalTasks   = $tasks->total();
    $inProgress   = $tasks->where('status','in_progress')->count();
    $completed    = $tasks->where('status','completed')->count();
    $overdue      = $tasks->filter(fn($t) => $t->due_date && \Carbon\Carbon::parse($t->due_date)->isPast() && !in_array($t->status,['completed','cancelled']))->count();
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Task Management</h4>
            <p>Assign, track and approve employee tasks</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat"><div class="v">{{ $totalTasks }}</div><div class="l">Total</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#93c5fd;">{{ $inProgress }}</div><div class="l">In Progress</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#86efac;">{{ $completed }}</div><div class="l">Completed</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#fca5a5;">{{ $overdue }}</div><div class="l">Overdue</div></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tasks.kanban') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:8px;font-size:.8rem;font-weight:600;">
                <i class="bi bi-kanban me-1"></i>Kanban
            </a>
            <button class="btn btn-sm btn-primary-grad px-4" style="border-radius:9px;" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                <i class="bi bi-plus-lg me-1"></i>New Task
            </button>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.tasks.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <div class="position-relative">
                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.8rem;pointer-events:none;"></i>
                <input type="text" name="search" class="form-control ps-4" placeholder="Search tasks…" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['pending','assigned','in_progress','review','completed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="priority" class="form-select">
                <option value="">All Priority</option>
                @foreach(['low','medium','high','urgent'] as $p)
                    <option value="{{ $p }}" @selected(request('priority')===$p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="employee" class="form-select">
                <option value="">All Employees</option>
                @foreach($employees ?? [] as $emp)
                    <option value="{{ $emp->id }}" @selected(request('employee')==$emp->id)>{{ $emp->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-filter btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-filter btn-outline-secondary px-3"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="card-header"><span class="card-title">All Tasks</span></div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Assigned To</th>
                    <th>Priority</th>
                    <th>Progress</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                @php
                    $ts = $task->status ?? 'pending';
                    $pr = $task->priority ?? 'medium';
                    $isOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && !in_array($ts,['completed','cancelled']);
                    $pMap = ['low'=>'spill-success','medium'=>'spill-warning','high'=>'spill-danger','urgent'=>'spill-danger'];
                    $sMap = ['pending'=>'spill-secondary','assigned'=>'spill-info','in_progress'=>'spill-processing','review'=>'spill-warning','completed'=>'spill-success','cancelled'=>'spill-cancelled'];
                @endphp
                <tr style="{{ $isOverdue ? 'background:#fff5f5;' : '' }}">
                    <td>
                        <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ Str::limit($task->title,45) }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">{{ $task->task_id }}</div>
                        @if($isOverdue)<span style="background:#fee2e2;color:#dc2626;font-size:.65rem;font-weight:700;padding:1px 6px;border-radius:4px;margin-top:3px;display:inline-block;">OVERDUE</span>@endif
                    </td>
                    <td>
                        @if($task->assignedTo)
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $task->assignedTo->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($task->assignedTo->full_name).'&size=24&background=4f46e5&color=fff' }}"
                                class="rounded-circle" width="24" height="24">
                            <span style="font-size:.83rem;color:#374151;">{{ $task->assignedTo->full_name }}</span>
                        </div>
                        @else <span style="color:#9ca3af;font-size:.82rem;">Unassigned</span> @endif
                    </td>
                    <td><span class="spill {{ $pMap[$pr] ?? 'spill-secondary' }}">{{ ucfirst($pr) }}</span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:5px;border-radius:3px;background:#e5e7eb;overflow:hidden;min-width:60px;">
                                <div style="height:100%;width:{{ $task->progress ?? 0 }}%;background:#4f46e5;border-radius:3px;"></div>
                            </div>
                            <span style="font-size:.75rem;color:#6b7280;min-width:28px;">{{ $task->progress ?? 0 }}%</span>
                        </div>
                    </td>
                    <td style="font-size:.83rem;{{ $isOverdue ? 'color:#dc2626;font-weight:700;' : 'color:#374151;' }}">
                        {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '—' }}
                    </td>
                    <td><span class="spill {{ $sMap[$ts] ?? 'spill-secondary' }}">{{ ucwords(str_replace('_',' ',$ts)) }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('employee.tasks.show', $task) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            @if(in_array($ts,['review','in_progress']))
                            <button class="act-btn act-green" title="Approve" onclick="approveTask('{{ route('admin.tasks.approve', $task) }}')"><i class="bi bi-check2"></i></button>
                            @endif
                            <button class="act-btn act-edit" title="Edit"
                                onclick="editTask({{ json_encode([
                                    'id'              => $task->id,
                                    'title'           => $task->title,
                                    'description'     => $task->description,
                                    'priority'        => $task->priority,
                                    'status'          => $task->status,
                                    'progress'        => $task->progress ?? 0,
                                    'due_date'        => $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '',
                                    'estimated_hours' => $task->estimated_hours,
                                    'assigned_to'     => $task->assigned_to,
                                ]) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" onclick="deleteTask({{ $task->id }})" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-list-task"></i><p>No tasks found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tasks->hasPages())
    <div class="pagination-wrap">{{ $tasks->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create Task Modal --}}
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Create New Task</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTaskForm" action="{{ route('admin.tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Task Title <span class="req">*</span></label>
                            <input type="text" name="title" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" class="form-control" rows="3" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Assign To</label>
                            <select name="assigned_to" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">Select Employee</option>
                                @foreach($employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
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
                            <input type="date" name="due_date" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Estimated Hours</label>
                            <input type="number" name="estimated_hours" class="form-control" step="0.5" min="0" style="border-radius:9px;border:1.5px solid #e5e7eb;">
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
{{-- Edit Task Modal --}}
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-warning"></i>Edit Task</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTaskForm">
                @csrf
                <input type="hidden" id="editTaskId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Task Title <span class="req">*</span></label>
                            <input type="text" id="editTitle" name="title" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea id="editDescription" name="description" class="form-control" rows="3" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Priority <span class="req">*</span></label>
                            <select id="editPriority" name="priority" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status <span class="req">*</span></label>
                            <select id="editStatus" name="status" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="pending">Pending</option>
                                <option value="assigned">Assigned</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">Review</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Assign To</label>
                            <select id="editAssignedTo" name="assigned_to" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">Unassigned</option>
                                @foreach($employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Due Date</label>
                            <input type="date" id="editDueDate" name="due_date" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Estimated Hours</label>
                            <input type="number" id="editEstimatedHours" name="estimated_hours" class="form-control" step="0.5" min="0" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Progress (%)</label>
                            <input type="number" id="editProgress" name="progress" class="form-control" min="0" max="100" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = () => document.querySelector('meta[name=csrf-token]').content;

// Create task via fetch (prevent plain form submit showing raw JSON)
document.getElementById('createTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    fetch(this.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF() },
        body: new FormData(this)
    }).then(r => r.json()).then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('createTaskModal')).hide();
            APP.toast('Task created!');
            setTimeout(() => location.reload(), 800);
        } else {
            btn.disabled = false;
            APP.toast(data.message || 'Error creating task.', 'danger');
        }
    }).catch(() => { btn.disabled = false; });
});

function approveTask(url) {
    APP.confirm('Approve task?', 'Mark this task as approved.', function() {
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF() }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Task approved!'); setTimeout(() => location.reload(), 800); }
        });
    });
}

function deleteTask(id) {
    APP.confirm('Delete task?', 'This cannot be undone.', function() {
        fetch(`/admin/tasks/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF() }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Task deleted!'); setTimeout(() => location.reload(), 800); }
        });
    });
}

function editTask(task) {
    document.getElementById('editTaskId').value         = task.id;
    document.getElementById('editTitle').value          = task.title;
    document.getElementById('editDescription').value    = task.description ?? '';
    document.getElementById('editPriority').value       = task.priority;
    document.getElementById('editStatus').value         = task.status;
    document.getElementById('editProgress').value       = task.progress ?? 0;
    document.getElementById('editDueDate').value        = task.due_date ?? '';
    document.getElementById('editEstimatedHours').value = task.estimated_hours ?? '';
    document.getElementById('editAssignedTo').value     = task.assigned_to ?? '';
    new bootstrap.Modal(document.getElementById('editTaskModal')).show();
}

document.getElementById('editTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id  = document.getElementById('editTaskId').value;
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true;
    const body = new FormData(this);
    body.append('_method', 'PUT');
    fetch(`/admin/tasks/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF() },
        body: body
    }).then(r => r.json()).then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editTaskModal')).hide();
            APP.toast('Task updated!');
            setTimeout(() => location.reload(), 800);
        } else {
            btn.disabled = false;
            APP.toast(data.message || 'Error updating task.', 'error');
        }
    }).catch(() => { btn.disabled = false; });
});
</script>
@endpush
