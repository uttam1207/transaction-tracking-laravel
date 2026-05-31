@extends('layouts.app')
@section('title', 'Kanban Board')

@push('styles')
<style>
.kanban-wrap { display:flex; gap:14px; overflow-x:auto; padding-bottom:16px; align-items:flex-start; }
.kanban-col { min-width:270px; max-width:270px; background:#f8f9fa; border-radius:14px; border:1.5px solid #e5e7eb; overflow:hidden; }
.kanban-col-hdr { padding:12px 16px; display:flex; align-items:center; justify-content:space-between; border-bottom:1.5px solid #e5e7eb; background:#fff; }
.kanban-col-hdr .k-title { font-weight:700; font-size:.84rem; color:#111827; }
.kanban-col-hdr .k-count { background:#f3f4f6; color:#6b7280; padding:2px 8px; border-radius:20px; font-size:.72rem; font-weight:700; }
.kanban-cards { padding:10px; min-height:180px; display:flex; flex-direction:column; gap:8px; }
.kanban-card {
    background:#fff; border-radius:10px; padding:12px 14px;
    border:1.5px solid #f0f0f5; cursor:pointer;
    border-left-width:4px;
    transition:box-shadow .2s, transform .2s;
}
.kanban-card:hover { box-shadow:0 6px 20px rgba(0,0,0,.09); transform:translateY(-2px); }
.kanban-card.priority-urgent, .kanban-card.priority-high { border-left-color:#dc2626; }
.kanban-card.priority-medium { border-left-color:#f59e0b; }
.kanban-card.priority-low { border-left-color:#16a34a; }
.k-empty { padding:24px 10px; text-align:center; font-size:.8rem; color:#9ca3af; }
/* Column accent colors */
.kcol-pending .kanban-col-hdr { border-top:3px solid #9ca3af; }
.kcol-assigned .kanban-col-hdr { border-top:3px solid #0ea5e9; }
.kcol-in_progress .kanban-col-hdr { border-top:3px solid #6366f1; }
.kcol-review .kanban-col-hdr { border-top:3px solid #f59e0b; }
.kcol-completed .kanban-col-hdr { border-top:3px solid #16a34a; }
</style>
@endpush

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Kanban Board</h4>
            <p>Visual task management across all stages</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
                <i class="bi bi-list me-1"></i>List View
            </a>
            <button class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1.5px solid rgba(255,255,255,.4);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);"
                data-bs-toggle="modal" data-bs-target="#createTaskModal">
                <i class="bi bi-plus-circle me-1"></i>New Task
            </button>
        </div>
    </div>
</div>

<div class="kanban-wrap">
    @php
        $columns = [
            'pending'     => 'Pending',
            'assigned'    => 'Assigned',
            'in_progress' => 'In Progress',
            'review'      => 'In Review',
            'completed'   => 'Completed',
        ];
        $prioColors = ['low'=>'#16a34a','medium'=>'#f59e0b','high'=>'#dc2626','urgent'=>'#7f1d1d'];
    @endphp

    @foreach($columns as $status => $colLabel)
    @php $colTasks = $grouped[$status] ?? collect(); @endphp
    <div class="kanban-col kcol-{{ $status }}">
        <div class="kanban-col-hdr">
            <span class="k-title">{{ $colLabel }}</span>
            <span class="k-count">{{ $colTasks->count() }}</span>
        </div>
        <div class="kanban-cards">
            @forelse($colTasks as $task)
            <div class="kanban-card priority-{{ $task->priority }}"
                data-bs-toggle="modal" data-bs-target="#taskDetailModal"
                data-id="{{ $task->id }}"
                data-title="{{ $task->title }}"
                data-status="{{ $task->status }}"
                data-priority="{{ $task->priority }}"
                data-progress="{{ $task->progress ?? 0 }}"
                data-due="{{ $task->due_date ?? '' }}"
                data-employee="{{ $task->assignedEmployee->full_name ?? 'Unassigned' }}">

                <div style="font-weight:700;font-size:.84rem;color:#111827;margin-bottom:6px;line-height:1.3;">
                    {{ Str::limit($task->title, 45) }}
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="spill" style="font-size:.65rem;background:{{ $prioColors[$task->priority] ?? '#6b7280' }}20;color:{{ $prioColors[$task->priority] ?? '#6b7280' }};border:1.5px solid {{ $prioColors[$task->priority] ?? '#6b7280' }}40;">
                        {{ ucfirst($task->priority) }}
                    </span>
                    @if($task->due_date)
                    @php $taskDue = \Carbon\Carbon::parse($task->due_date); @endphp
                    <span style="font-size:.73rem;color:{{ ($taskDue->isPast() && $status !== 'completed') ? '#dc2626' : '#9ca3af' }};font-weight:{{ ($taskDue->isPast() && $status !== 'completed') ? '700' : '400' }};">
                        <i class="bi bi-calendar3 me-1"></i>{{ $taskDue->format('M d') }}
                    </span>
                    @endif
                </div>

                @if($task->progress)
                <div style="background:#e5e7eb;border-radius:4px;height:4px;overflow:hidden;margin-bottom:8px;">
                    <div style="height:4px;border-radius:4px;width:{{ $task->progress }}%;background:#6366f1;"></div>
                </div>
                @endif

                @if($task->assignedEmployee)
                <div class="d-flex align-items-center gap-1">
                    <img src="{{ $task->assignedEmployee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($task->assignedEmployee->full_name).'&size=20&background=6366f1&color=fff' }}"
                        class="rounded-circle" width="20" height="20" style="object-fit:cover;">
                    <span style="font-size:.73rem;color:#6b7280;">{{ $task->assignedEmployee->full_name }}</span>
                </div>
                @endif
            </div>
            @empty
            <div class="k-empty"><i class="bi bi-inbox-fill d-block fs-4 mb-1 opacity-25"></i>No tasks</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

{{-- Task Detail Modal --}}
<div class="modal fade" id="taskDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="tdTitle">Task Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Priority</div>
                        <div id="tdPriority"></div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Status</div>
                        <div id="tdStatus"></div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Assigned To</div>
                        <div id="tdEmployee" style="font-size:.87rem;font-weight:600;color:#111827;"></div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Due Date</div>
                        <div id="tdDue" style="font-size:.87rem;font-weight:600;color:#111827;"></div>
                    </div>
                    <div class="col-12">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">Progress</div>
                        <div style="background:#e5e7eb;border-radius:6px;height:10px;overflow:hidden;">
                            <div id="tdProgress" style="height:10px;border-radius:6px;background:linear-gradient(90deg,#4f46e5,#8b5cf6);transition:width .4s;"></div>
                        </div>
                        <div id="tdProgressText" style="font-size:.75rem;color:#6b7280;margin-top:4px;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a id="tdApproveBtn" href="#" class="btn btn-sm btn-primary-grad px-4 d-none">
                    <i class="bi bi-check2 me-1"></i>Approve
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Create Task Modal --}}
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Create New Task</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Task Title <span class="req">*</span></label>
                            <input type="text" name="title" class="form-control" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Assign To</label>
                            <select name="assigned_to" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">Select Employee</option>
                                @foreach($employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="flabel">Priority</label>
                            <select name="priority" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="flabel">Due Date</label>
                            <input type="date" name="due_date" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
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
const prioColors = { low:'#16a34a', medium:'#f59e0b', high:'#dc2626', urgent:'#7f1d1d' };
const statusLabels = { pending:'Pending', assigned:'Assigned', in_progress:'In Progress', review:'In Review', completed:'Completed', cancelled:'Cancelled' };
const statusPillColors = { pending:'#9ca3af', assigned:'#0ea5e9', in_progress:'#6366f1', review:'#f59e0b', completed:'#16a34a', cancelled:'#dc2626' };

document.getElementById('taskDetailModal').addEventListener('show.bs.modal', function(e) {
    const card = e.relatedTarget;
    if (!card) return;

    document.getElementById('tdTitle').textContent = card.dataset.title;
    document.getElementById('tdEmployee').textContent = card.dataset.employee;
    document.getElementById('tdDue').textContent = card.dataset.due || '—';

    const pColor = prioColors[card.dataset.priority] || '#6b7280';
    document.getElementById('tdPriority').innerHTML =
        `<span style="background:${pColor}20;color:${pColor};border:1.5px solid ${pColor}40;padding:2px 10px;border-radius:20px;font-size:.76rem;font-weight:700;">${card.dataset.priority}</span>`;

    const sColor = statusPillColors[card.dataset.status] || '#6b7280';
    const sLabel = statusLabels[card.dataset.status] || card.dataset.status;
    document.getElementById('tdStatus').innerHTML =
        `<span style="background:${sColor}20;color:${sColor};border:1.5px solid ${sColor}40;padding:2px 10px;border-radius:20px;font-size:.76rem;font-weight:700;">${sLabel}</span>`;

    const prog = card.dataset.progress;
    document.getElementById('tdProgress').style.width = prog + '%';
    document.getElementById('tdProgressText').textContent = prog + '% complete';

    const approveBtn = document.getElementById('tdApproveBtn');
    if (['review', 'in_progress'].includes(card.dataset.status)) {
        approveBtn.href = `/admin/tasks/${card.dataset.id}/approve`;
        approveBtn.classList.remove('d-none');
    } else {
        approveBtn.classList.add('d-none');
    }
});
</script>
@endpush
