@extends('layouts.app')
@section('title', 'Queue Monitor')
@section('breadcrumb')
    <li class="breadcrumb-item active">Queue Monitor</li>
@endsection

@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#374151,#1f2937);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Queue Monitor</h4>
            <p>Monitor background jobs and failed queue entries</p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.queue.retry-all') }}">
                @csrf
                <button class="btn btn-sm" style="background:rgba(251,191,36,.2);color:#fbbf24;border:1.5px solid rgba(251,191,36,.4);border-radius:9px;font-weight:600;">
                    <i class="bi bi-arrow-repeat me-1"></i>Retry All Failed
                </button>
            </form>
            <form method="POST" action="{{ route('admin.queue.flush') }}">
                @csrf
                <button class="btn btn-sm" onclick="return confirm('Flush ALL failed jobs? This cannot be undone.')"
                    style="background:rgba(239,68,68,.2);color:#ef4444;border:1.5px solid rgba(239,68,68,.4);border-radius:9px;font-weight:600;">
                    <i class="bi bi-trash me-1"></i>Flush Failed
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="info-card text-center" style="padding:16px;border-top:4px solid #6366f1;">
            <div style="font-size:1.7rem;font-weight:800;color:#6366f1;line-height:1;">{{ $stats['pending'] }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Pending Jobs</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="info-card text-center" style="padding:16px;border-top:4px solid #dc2626;">
            <div style="font-size:1.7rem;font-weight:800;color:#dc2626;line-height:1;">{{ $stats['failed'] }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Failed Jobs</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="info-card text-center" style="padding:16px;border-top:4px solid #16a34a;">
            <div style="font-size:1.7rem;font-weight:800;color:#16a34a;line-height:1;">{{ count($stats['byQueue']) }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Active Queues</div>
        </div>
    </div>
</div>

@if(!empty($stats['byQueue']))
<div class="table-card mb-3">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-layers me-2"></i>Jobs by Queue</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Queue Name</th>
                    <th>Pending Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['byQueue'] as $queue => $count)
                <tr>
                    <td>
                        <span style="font-family:monospace;background:#f0f4ff;color:#4f46e5;padding:2px 10px;border-radius:6px;font-size:.83rem;font-weight:700;">
                            {{ $queue }}
                        </span>
                    </td>
                    <td>
                        <span class="spill spill-info" style="font-size:.72rem;">{{ $count }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Pending Jobs --}}
<div class="table-card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title"><i class="bi bi-hourglass-split me-2"></i>Pending Jobs</span>
        <span class="spill spill-warning" style="font-size:.72rem;">{{ $stats['pending'] }}</span>
    </div>
    @if($pending->isEmpty())
    <div class="empty-state"><i class="bi bi-inbox"></i><p>No pending jobs</p></div>
    @else
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Queue</th>
                    <th>Job Class</th>
                    <th>Attempts</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pending as $job)
                @php $payload = json_decode($job->payload, true); @endphp
                <tr>
                    <td style="font-family:monospace;font-size:.82rem;color:#6b7280;">{{ $job->id }}</td>
                    <td>
                        <span style="font-family:monospace;background:#f0f4ff;color:#4f46e5;padding:2px 8px;border-radius:6px;font-size:.76rem;font-weight:700;">
                            {{ $job->queue }}
                        </span>
                    </td>
                    <td style="font-size:.84rem;color:#374151;">{{ class_basename($payload['displayName'] ?? 'Unknown') }}</td>
                    <td style="font-size:.84rem;color:#374151;">{{ $job->attempts }}</td>
                    <td style="font-size:.82rem;color:#9ca3af;">{{ \Carbon\Carbon::createFromTimestamp($job->created_at)->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Failed Jobs --}}
<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title"><i class="bi bi-x-circle me-2"></i>Failed Jobs</span>
        <span class="spill spill-danger" style="font-size:.72rem;">{{ $stats['failed'] }}</span>
    </div>
    @if($failed->isEmpty())
    <div class="empty-state"><i class="bi bi-shield-check"></i><p>No failed jobs</p></div>
    @else
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>UUID</th>
                    <th>Queue</th>
                    <th>Job</th>
                    <th>Failed At</th>
                    <th>Exception</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($failed as $job)
                @php $payload = json_decode($job->payload, true); @endphp
                <tr>
                    <td style="font-family:monospace;font-size:.82rem;color:#6b7280;">{{ substr($job->uuid, 0, 8) }}…</td>
                    <td>
                        <span style="font-family:monospace;background:#fef2f2;color:#dc2626;padding:2px 8px;border-radius:6px;font-size:.76rem;font-weight:700;">
                            {{ $job->queue }}
                        </span>
                    </td>
                    <td style="font-size:.84rem;color:#374151;">{{ class_basename($payload['displayName'] ?? 'Unknown') }}</td>
                    <td style="font-size:.82rem;color:#9ca3af;">{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</td>
                    <td style="font-size:.78rem;color:#dc2626;max-width:220px;" class="text-truncate" title="{{ $job->exception }}">
                        {{ Str::limit($job->exception, 70) }}
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <form method="POST" action="{{ route('admin.queue.retry', $job->uuid) }}" class="d-inline">
                                @csrf
                                <button class="act-btn act-green" title="Retry"><i class="bi bi-arrow-repeat"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.queue.delete-failed', $job->uuid) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="act-btn act-delete" onclick="return confirm('Delete this failed job?')" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
