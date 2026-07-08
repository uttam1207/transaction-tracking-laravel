@extends('layouts.app')
@section('title', 'Task Details')

@section('content')

@php
    $statusColors = ['pending'=>'secondary','assigned'=>'info','in_progress'=>'warning','review'=>'info','completed'=>'success','cancelled'=>'danger'];
    $priorityColors = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
    $ts = $task->status ?? 'pending';
    $pBorderColor = ['low'=>'#16a34a','medium'=>'#f59e0b','high'=>'#dc2626','urgent'=>'#7f1d1d'][$task->priority] ?? '#6366f1';
@endphp

<a href="{{ route('employee.tasks.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Tasks</a>

<div class="page-hero" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div style="flex:1;min-width:0;">
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="spill spill-{{ $statusColors[$ts] ?? 'secondary' }}" style="font-size:.72rem;">
                    {{ ucwords(str_replace('_', ' ', $ts)) }}
                </span>
                <span class="spill" style="font-size:.72rem;background:{{ $pBorderColor }};color:#fff;">
                    {{ ucfirst($task->priority) }} Priority
                </span>
            </div>
            <h4 style="font-weight:800;margin-bottom:4px;color:#fff;">{{ $task->title }}</h4>
            <p style="font-size:.83rem;opacity:.75;color:#fff;margin:0;">
                <i class="bi bi-tag me-1"></i>{{ $task->task_id }}
                &nbsp;·&nbsp;
                <i class="bi bi-person me-1"></i>Assigned by {{ $task->assignedBy->name ?? '—' }}
            </p>
        </div>
        @if(!in_array($ts, ['completed', 'cancelled']))
        <div class="d-flex gap-2 align-items-center">
            @if(!$activeTimer)
            <form id="startTimerForm" action="{{ route('employee.tasks.timer.start', $task) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1.5px solid rgba(255,255,255,.4);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
                    <i class="bi bi-play-fill me-1"></i>Start Timer
                </button>
            </form>
            @else
            <form id="stopTimerForm" action="{{ route('employee.tasks.timer.stop', $task) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm" id="stopTimerBtn" style="background:#fbbf24;color:#111;border:none;border-radius:9px;font-weight:700;">
                    <i class="bi bi-stop-fill me-1"></i>Stop&nbsp;<span id="timerDisplay" style="font-family:monospace;"></span>
                </button>
            </form>
            @endif
        </div>
        @endif
    </div>
</div>

<div class="row g-3">
    {{-- Main Column --}}
    <div class="col-lg-8">
        {{-- Task Details Card --}}
        <div class="info-card mb-3">
            <div class="info-card-hdr"><i class="bi bi-info-circle me-2"></i>Task Details</div>
            <div class="info-card-body">
                @if($task->description)
                <p style="font-size:.87rem;color:#374151;line-height:1.6;margin-bottom:16px;">{{ $task->description }}</p>
                @endif

                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-4">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Due Date</div>
                        @php
                            $isOverdue = $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && $ts !== 'completed';
                        @endphp
                        <div style="font-size:.87rem;font-weight:600;color:{{ $isOverdue ? '#dc2626' : '#111827' }};">
                            {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '—' }}
                            @if($isOverdue)<span class="spill spill-danger ms-1" style="font-size:.65rem;">Overdue</span>@endif
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Estimated Hours</div>
                        <div style="font-size:.87rem;font-weight:600;color:#111827;">{{ $task->estimated_hours ?? '—' }}h</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Actual Hours</div>
                        <div style="font-size:.87rem;font-weight:600;color:#111827;">{{ number_format($task->actual_hours ?? 0, 1) }}h</div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:.8rem;color:#6b7280;">Progress</span>
                        <span style="font-size:.8rem;font-weight:700;color:#4f46e5;">{{ $task->progress ?? 0 }}%</span>
                    </div>
                    <div style="background:#e5e7eb;border-radius:6px;height:8px;overflow:hidden;">
                        <div style="height:8px;border-radius:6px;width:{{ $task->progress ?? 0 }}%;background:linear-gradient(90deg,#4f46e5,#8b5cf6);transition:width .4s;"></div>
                    </div>
                </div>

                {{-- Status Actions --}}
                @if(!in_array($ts, ['completed', 'cancelled']))
                <div class="d-flex gap-2 flex-wrap">
                    @foreach(['in_progress' => ['label'=>'Start Working','color'=>'#f59e0b'], 'review' => ['label'=>'Submit for Review','color'=>'#6366f1'], 'completed' => ['label'=>'Mark Complete','color'=>'#16a34a']] as $status => $cfg)
                    @if($ts !== $status)
                    <button class="btn btn-sm" onclick="updateStatus('{{ $status }}')"
                        style="border-radius:8px;font-weight:600;font-size:.8rem;border:1.5px solid {{ $cfg['color'] }};color:{{ $cfg['color'] }};background:transparent;">
                        {{ $cfg['label'] }}
                    </button>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Comments --}}
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-chat-dots me-2"></i>Comments ({{ $task->comments->count() }})</div>
            <div class="info-card-body">
                @forelse($task->comments as $comment)
                <div class="d-flex gap-3 mb-3" style="padding-bottom:14px;border-bottom:1px solid #f3f4f6;">
                    <img src="{{ $comment->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($comment->user->name ?? 'U').'&size=36&background=6366f1&color=fff' }}"
                        class="rounded-circle" width="34" height="34" style="flex-shrink:0;object-fit:cover;">
                    <div style="flex:1;min-width:0;">
                        <div style="background:#f8f9fa;border-radius:10px;padding:10px 14px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-weight:700;font-size:.84rem;color:#111827;">{{ $comment->user->name ?? 'Unknown' }}</span>
                                <span style="font-size:.75rem;color:#9ca3af;">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p style="font-size:.84rem;color:#374151;margin:0;">{{ $comment->comment }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:32px 0;">
                    <i class="bi bi-chat-square-text"></i>
                    <p>No comments yet</p>
                </div>
                @endforelse

                {{-- Add Comment Form --}}
                <form action="{{ route('employee.tasks.comments.store', $task) }}" method="POST" class="mt-3">
                    @csrf
                    <div class="d-flex gap-2 align-items-end">
                        <textarea name="comment" class="form-control" rows="2" required
                            placeholder="Add a comment..."
                            style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;resize:none;flex:1;"></textarea>
                        <button type="submit" class="btn btn-sm btn-primary-grad" style="height:38px;padding:0 16px;flex-shrink:0;">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Time Logs --}}
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-stopwatch me-2"></i>Time Logs</div>
            @forelse($task->timesheets ?? [] as $log)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid #f3f4f6;">
                <div>
                    <div style="font-size:.84rem;font-weight:600;color:#111827;">
                        {{ \Carbon\Carbon::parse($log->start_time)->format('h:i A') }}
                        @if($log->end_time) &mdash; {{ \Carbon\Carbon::parse($log->end_time)->format('h:i A') }} @endif
                    </div>
                    <div style="font-size:.75rem;color:#9ca3af;">{{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}</div>
                </div>
                @if($log->hours)
                <span class="spill spill-info" style="font-size:.72rem;">{{ number_format($log->hours, 1) }}h</span>
                @else
                <span class="spill spill-warning" style="font-size:.72rem;">Running…</span>
                @endif
            </div>
            @empty
            <div class="empty-state" style="padding:32px 0;">
                <i class="bi bi-stopwatch"></i>
                <p>No time logged yet</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
@if($activeTimer)
const startTime = new Date('{{ $activeTimer->start_time }}').getTime();
function updateTimer() {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    const h = Math.floor(elapsed / 3600);
    const m = Math.floor((elapsed % 3600) / 60);
    const s = elapsed % 60;
    const el = document.getElementById('timerDisplay');
    if (el) el.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
}
setInterval(updateTimer, 1000);
updateTimer();
@endif

function updateStatus(status) {
    fetch('{{ route('employee.tasks.status', $task) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            APP.toast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            APP.toast(data.message || 'Failed to update status.', 'error');
        }
    })
    .catch(() => APP.toast('Something went wrong.', 'error'));
}

function timerFetch(form, onSuccess) {
    const btn = form.querySelector('[type=submit]');
    btn.disabled = true;
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new FormData(form)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) onSuccess(data);
        else APP.toast(data.message || 'Action failed.', 'error');
    })
    .catch(() => APP.toast('Something went wrong.', 'error'))
    .finally(() => btn.disabled = false);
}

document.getElementById('startTimerForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    timerFetch(this, () => location.reload());
});

document.getElementById('stopTimerForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    timerFetch(this, (data) => {
        APP.toast(data.message, 'success');
        setTimeout(() => location.reload(), 800);
    });
});
</script>
@endpush
