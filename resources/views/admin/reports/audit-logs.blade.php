@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Audit Logs</h4>
        <p class="text-muted mb-0">System-wide audit trail for compliance</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.audit-logs') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control"
                    placeholder="Search user, model, event..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="event" class="form-select">
                    <option value="">All Events</option>
                    @foreach(['created', 'updated', 'deleted', 'restored'] as $e)
                        <option value="{{ $e }}" @selected(request('event') === $e)>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="module" class="form-select">
                    <option value="">All Modules</option>
                    @foreach(['User', 'Employee', 'Transaction', 'FraudAlert', 'Task', 'Leave', 'Setting'] as $mod)
                        <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    <a href="{{ route('admin.reports.audit-logs') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sm">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Event</th>
                        <th>Model</th>
                        <th>Module</th>
                        <th>IP Address</th>
                        <th>Changes</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="fw-semibold small">{{ $log->user->name ?? 'System' }}</div>
                        </td>
                        <td>
                            @php
                                $eColors = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', 'restored' => 'info'];
                            @endphp
                            <span class="badge bg-{{ $eColors[$log->event] ?? 'secondary' }}">
                                {{ ucfirst($log->event) }}
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $log->module ?? '—' }}</span>
                        </td>
                        <td><code class="small">{{ $log->ip_address ?? '—' }}</code></td>
                        <td>
                            @if($log->new_values && count($log->new_values ?? []))
                            <button class="btn btn-xs btn-outline-secondary btn-sm py-0 px-1"
                                onclick="showChanges({{ $log->id }}, @json($log->old_values ?? []), @json($log->new_values ?? []))">
                                <small>View Changes</small>
                            </button>
                            @else — @endif
                        </td>
                        <td><small class="text-muted">{{ $log->created_at->format('M d, H:i') }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
                            No audit logs found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-transparent">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Changes Modal --}}
<div class="modal fade" id="changesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-danger">Before</h6>
                        <pre id="oldValues" class="bg-light p-3 rounded small" style="max-height: 300px; overflow: auto;"></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success">After</h6>
                        <pre id="newValues" class="bg-light p-3 rounded small" style="max-height: 300px; overflow: auto;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showChanges(id, oldVals, newVals) {
    document.getElementById('oldValues').textContent = JSON.stringify(oldVals, null, 2);
    document.getElementById('newValues').textContent = JSON.stringify(newVals, null, 2);
    new bootstrap.Modal(document.getElementById('changesModal')).show();
}
</script>
@endpush
