@extends('layouts.app')

@section('title', 'Project Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.projects.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Projects
        </a>
        <h4 class="mb-0 fw-bold mt-1">{{ $project->name }}</h4>
    </div>
    @php
        $statusColors = ['planning' => 'info', 'active' => 'success', 'on_hold' => 'warning', 'completed' => 'primary', 'cancelled' => 'danger'];
        $ps = $project->status ?? 'planning';
    @endphp
    <span class="badge bg-{{ $statusColors[$ps] }} fs-6">{{ ucwords(str_replace('_',' ',$ps)) }}</span>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold">Project Info</div>
            <div class="card-body">
                @foreach([
                    ['Code', $project->code ?? '—'],
                    ['Manager', $project->manager->name ?? '—'],
                    ['Department', $project->department->name ?? '—'],
                    ['Start Date', \Carbon\Carbon::parse($project->start_date)->format('M d, Y')],
                    ['End Date', $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y') : '—'],
                    ['Budget', $project->budget ? '$'.number_format($project->budget) : '—'],
                    ['Total Tasks', $project->tasks->count()],
                ] as [$label, $value])
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ $label }}</span>
                    <strong>{{ $value }}</strong>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Task Progress --}}
        @php
            $total = $project->tasks->count();
            $done = $project->tasks->where('status', 'completed')->count();
            $pct = $total > 0 ? round(($done / $total) * 100) : 0;
        @endphp
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Overall Progress</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">{{ $done }} of {{ $total }} tasks done</small>
                    <strong>{{ $pct }}%</strong>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @if($project->description)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Description</div>
            <div class="card-body"><p class="mb-0">{{ $project->description }}</p></div>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Tasks ({{ $project->tasks->count() }})</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Task</th>
                                <th>Assigned To</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->tasks as $task)
                            <tr>
                                <td>{{ Str::limit($task->title, 35) }}</td>
                                <td>{{ $task->assignedEmployee->full_name ?? '—' }}</td>
                                <td>
                                    @php $pc = ['low'=>'success','medium'=>'warning','high'=>'danger','urgent'=>'danger']; @endphp
                                    <span class="badge bg-{{ $pc[$task->priority] ?? 'secondary' }}">{{ ucfirst($task->priority) }}</span>
                                </td>
                                <td><small>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d') : '—' }}</small></td>
                                <td>
                                    @php $sc = ['pending'=>'secondary','assigned'=>'info','in_progress'=>'primary','review'=>'warning','completed'=>'success','cancelled'=>'danger']; @endphp
                                    <span class="badge bg-{{ $sc[$task->status] ?? 'secondary' }}">{{ ucwords(str_replace('_',' ',$task->status)) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No tasks</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
