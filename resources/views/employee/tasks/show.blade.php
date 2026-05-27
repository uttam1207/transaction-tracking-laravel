@extends('layouts.app')

@section('title', 'Task Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('employee.tasks.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Tasks
        </a>
        <h4 class="mb-0 fw-bold mt-1">{{ $task->title }}</h4>
    </div>
    @if(!in_array($task->status, ['completed', 'cancelled']))
    <div class="d-flex gap-2">
        @if(!$activeTimer)
        <form action="{{ route('employee.tasks.timer.start', $task) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="bi bi-play-fill me-1"></i>Start Timer
            </button>
        </form>
        @else
        <form action="{{ route('employee.tasks.timer.stop', $task) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-warning" id="stopTimerBtn">
                <i class="bi bi-stop-fill me-1"></i>Stop Timer
                <span class="ms-1" id="timerDisplay"></span>
            </button>
        </form>
        @endif
    </div>
    @endif
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Task Details --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Task Details</span>
                @php
                    $statusColors = ['pending' => 'secondary', 'assigned' => 'info', 'in_progress' => 'primary', 'review' => 'warning', 'completed' => 'success', 'cancelled' => 'danger'];
                    $priorityColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'];
                    $ts = $task->status ?? 'pending';
                @endphp
                <span class="badge bg-{{ $statusColors[$ts] ?? 'secondary' }} fs-6">
                    {{ ucwords(str_replace('_', ' ', $ts)) }}
                </span>
            </div>
            <div class="card-body">
                @if($task->description)
                <p>{{ $task->description }}</p>
                @endif

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="text-muted small">Priority</label>
                        <div>
                            <span class="badge bg-{{ $priorityColors[$task->priority] ?? 'secondary' }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Due Date</label>
                        <div class="{{ $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && $ts !== 'completed' ? 'text-danger fw-semibold' : '' }}">
                            {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '—' }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Assigned By</label>
                        <div>{{ $task->assignedBy->name ?? '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Estimated Hours</label>
                        <div>{{ $task->estimated_hours ?? '—' }}h</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Actual Hours</label>
                        <div>{{ number_format($task->actual_hours ?? 0, 1) }}h</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Task ID</label>
                        <div><code>{{ $task->task_id }}</code></div>
                    </div>
                </div>

                {{-- Progress --}}
                <div class="mb-3">
                    <label class="text-muted small">Progress ({{ $task->progress ?? 0 }}%)</label>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-{{ $statusColors[$ts] ?? 'primary' }}"
                            style="width: {{ $task->progress ?? 0 }}%"></div>
                    </div>
                </div>

                {{-- Update Status --}}
                @if(!in_array($ts, ['completed', 'cancelled']))
                <div class="d-flex gap-2 flex-wrap">
                    @foreach(['in_progress' => 'Start Working', 'review' => 'Submit for Review', 'completed' => 'Mark Complete'] as $status => $label)
                    @if($ts !== $status)
                    <form action="{{ route('employee.tasks.status', $task) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="{{ $status }}">
                        <button type="submit" class="btn btn-sm btn-outline-{{ $statusColors[$status] }}">
                            {{ $label }}
                        </button>
                    </form>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Comments --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">
                Comments ({{ $task->comments->count() }})
            </div>
            <div class="card-body">
                @forelse($task->comments as $comment)
                <div class="d-flex gap-3 mb-3">
                    <img src="{{ $comment->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($comment->user->name ?? 'U').'&size=36&background=6366f1&color=fff' }}"
                        class="rounded-circle" width="36" height="36" style="flex-shrink: 0;">
                    <div class="flex-fill">
                        <div class="bg-light rounded p-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong class="small">{{ $comment->user->name ?? 'Unknown' }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0 small">{{ $comment->comment }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">No comments yet</p>
                @endforelse

                {{-- Add Comment --}}
                <form action="{{ route('employee.tasks.comments.store', $task) }}" method="POST" class="mt-3">
                    @csrf
                    <div class="d-flex gap-2">
                        <textarea name="comment" class="form-control" rows="2"
                            placeholder="Add a comment..." required></textarea>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Time Logs --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Time Logs</div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($task->timesheets ?? [] as $log)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small fw-semibold">{{ \Carbon\Carbon::parse($log->start_time)->format('h:i A') }}
                                    @if($log->end_time) — {{ \Carbon\Carbon::parse($log->end_time)->format('h:i A') }} @endif
                                </div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}</small>
                            </div>
                            <span class="badge bg-primary">
                                {{ $log->hours ? number_format($log->hours, 1).'h' : 'Running...' }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted py-4">
                        <i class="bi bi-stopwatch fs-3 d-block mb-1"></i>
                        No time logged yet
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
@if($activeTimer)
// Show running timer
const startTime = new Date('{{ $activeTimer->start_time }}').getTime();
function updateTimer() {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    const h = Math.floor(elapsed / 3600);
    const m = Math.floor((elapsed % 3600) / 60);
    const s = elapsed % 60;
    const display = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    const el = document.getElementById('timerDisplay');
    if (el) el.textContent = display;
}
setInterval(updateTimer, 1000);
updateTimer();
@endif
</script>
@endpush
