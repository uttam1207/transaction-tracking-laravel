@extends('layouts.app')
@section('title', 'Queue Monitor')
@section('breadcrumb')
    <li class="breadcrumb-item active">Queue Monitor</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Queue Monitor</h4>
        <p class="text-muted small mb-0">Monitor background jobs and failed queue entries</p>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.queue.retry-all') }}">
            @csrf
            <button class="btn btn-warning btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Retry All Failed</button>
        </form>
        <form method="POST" action="{{ route('admin.queue.flush') }}">
            @csrf
            <button class="btn btn-danger btn-sm" onclick="return confirm('Flush ALL failed jobs?')">
                <i class="bi bi-trash me-1"></i>Flush Failed
            </button>
        </form>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-15 text-primary"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="h3 mb-0 fw-bold">{{ $stats['pending'] }}</div>
                    <div class="text-muted small">Pending Jobs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-15 text-danger"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="h3 mb-0 fw-bold">{{ $stats['failed'] }}</div>
                    <div class="text-muted small">Failed Jobs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-15 text-success"><i class="bi bi-layers"></i></div>
                <div>
                    <div class="h3 mb-0 fw-bold">{{ count($stats['byQueue']) }}</div>
                    <div class="text-muted small">Active Queues</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($stats['byQueue']))
<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">Jobs by Queue</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>Queue</th><th>Pending</th></tr></thead>
            <tbody>
                @foreach($stats['byQueue'] as $queue => $count)
                <tr>
                    <td><span class="badge bg-primary bg-opacity-15 text-primary">{{ $queue }}</span></td>
                    <td>{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Pending Jobs --}}
<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold d-flex justify-content-between">
        <span>Pending Jobs</span>
        <span class="badge bg-warning text-dark">{{ $stats['pending'] }}</span>
    </div>
    <div class="card-body p-0">
        @if($pending->isEmpty())
            <div class="p-4 text-center text-muted small">No pending jobs</div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>ID</th><th>Queue</th><th>Job Class</th><th>Attempts</th><th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pending as $job)
                    @php $payload = json_decode($job->payload, true); @endphp
                    <tr>
                        <td class="font-monospace">{{ $job->id }}</td>
                        <td><span class="badge bg-primary bg-opacity-15 text-primary">{{ $job->queue }}</span></td>
                        <td>{{ class_basename($payload['displayName'] ?? 'Unknown') }}</td>
                        <td>{{ $job->attempts }}</td>
                        <td>{{ \Carbon\Carbon::createFromTimestamp($job->created_at)->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Failed Jobs --}}
<div class="card shadow-sm">
    <div class="card-header fw-semibold d-flex justify-content-between">
        <span>Failed Jobs</span>
        <span class="badge bg-danger">{{ $stats['failed'] }}</span>
    </div>
    <div class="card-body p-0">
        @if($failed->isEmpty())
            <div class="p-4 text-center text-muted small">No failed jobs</div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>UUID</th><th>Queue</th><th>Job</th><th>Failed At</th><th>Exception</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($failed as $job)
                    @php $payload = json_decode($job->payload, true); @endphp
                    <tr>
                        <td class="font-monospace small">{{ substr($job->uuid, 0, 8) }}...</td>
                        <td><span class="badge bg-danger bg-opacity-15 text-danger">{{ $job->queue }}</span></td>
                        <td>{{ class_basename($payload['displayName'] ?? 'Unknown') }}</td>
                        <td>{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</td>
                        <td class="text-danger small" style="max-width:250px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;" title="{{ $job->exception }}">
                            {{ Str::limit($job->exception, 80) }}
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.queue.retry', $job->uuid) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-warning py-0 px-2" title="Retry">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.queue.delete-failed', $job->uuid) }}" class="d-inline ms-1">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger py-0 px-2" onclick="return confirm('Delete?')" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
