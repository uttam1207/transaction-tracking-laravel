@extends('layouts.app')
@section('title', 'Audit Logs')

@section('content')

<div class="page-hero">
    <div style="position:relative;z-index:1;">
        <h4>Audit Logs</h4>
        <p>System-wide audit trail for compliance and security monitoring</p>
    </div>
</div>

<div class="filter-card">
    <form method="GET" action="{{ route('admin.reports.audit-logs') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="flabel">Search</label>
            <input type="text" name="search" class="form-control"
                placeholder="Search user, model, event…" value="{{ request('search') }}"
                style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
        </div>
        <div class="col-md-2">
            <label class="flabel">Event</label>
            <select name="event" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Events</option>
                @foreach(['created','updated','deleted','restored'] as $e)
                    <option value="{{ $e }}" @selected(request('event') === $e)>{{ ucfirst($e) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Module</label>
            <select name="module" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Modules</option>
                @foreach(['User','Employee','Transaction','FraudAlert','Task','Leave','Setting'] as $mod)
                    <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ $mod }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Date</label>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}"
                style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-sm btn-primary-grad px-4">Search</button>
        </div>
        <div class="col-md-auto">
            <a href="{{ route('admin.reports.audit-logs') }}" class="btn btn-sm btn-outline-secondary px-3">Reset</a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="card-header"><span class="card-title">Audit Trail</span></div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
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
                @php
                    $eMap = ['created'=>'success','updated'=>'warning','deleted'=>'danger','restored'=>'info'];
                @endphp
                <tr>
                    <td style="font-weight:700;font-size:.87rem;color:#111827;">{{ $log->user->name ?? 'System' }}</td>
                    <td>
                        <span class="spill spill-{{ $eMap[$log->event] ?? 'secondary' }}" style="font-size:.7rem;">{{ ucfirst($log->event) }}</span>
                    </td>
                    <td style="font-size:.8rem;color:#6b7280;">
                        {{ class_basename($log->auditable_type) }} <span style="font-family:monospace;color:#4f46e5;">#{{ $log->auditable_id }}</span>
                    </td>
                    <td>
                        <span style="background:#f0f4ff;color:#4f46e5;padding:2px 8px;border-radius:6px;font-size:.72rem;font-weight:700;">{{ $log->module ?? '—' }}</span>
                    </td>
                    <td style="font-family:monospace;font-size:.8rem;color:#6b7280;">{{ $log->ip_address ?? '—' }}</td>
                    <td>
                        @if($log->new_values && count($log->new_values ?? []))
                        <button class="act-btn act-view" title="View Changes"
                            onclick="showChanges({{ $log->id }}, @json($log->old_values ?? []), @json($log->new_values ?? []))">
                            <i class="bi bi-eye"></i>
                        </button>
                        @else
                        <span style="color:#9ca3af;font-size:.8rem;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem;color:#9ca3af;">{{ $log->created_at->format('M d, H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-journal-text"></i><p>No audit logs found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="pagination-wrap">{{ $logs->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Changes Modal --}}
<div class="modal fade" id="changesModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Change Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div style="font-size:.78rem;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Before</div>
                        <pre id="oldValues" style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:9px;padding:12px;font-size:.78rem;max-height:280px;overflow:auto;color:#7f1d1d;margin:0;"></pre>
                    </div>
                    <div class="col-md-6">
                        <div style="font-size:.78rem;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">After</div>
                        <pre id="newValues" style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:9px;padding:12px;font-size:.78rem;max-height:280px;overflow:auto;color:#14532d;margin:0;"></pre>
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
