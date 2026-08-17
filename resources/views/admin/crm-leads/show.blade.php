@extends('layouts.app')

@section('title', 'Lead — ' . $lead->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">{{ $lead->name }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.crm-leads.index') }}">Leads</a></li>
                <li class="breadcrumb-item active">{{ $lead->name }}</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            @if (!in_array($lead->status, ['won','lost']))
            <button class="btn btn-success btn-sm" onclick="convertLead({{ $lead->id }})">
                <i class="bi bi-person-check me-1"></i> Convert to Customer
            </button>
            @endif
            <a href="{{ route('admin.crm-leads.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Lead Info --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Lead Details</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleEditMode()"><i class="bi bi-pencil"></i></button>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small" id="leadInfo">
                        <dt class="col-4 text-muted">Name</dt><dd class="col-8">{{ $lead->name }}</dd>
                        <dt class="col-4 text-muted">Company</dt><dd class="col-8">{{ $lead->company ?? '—' }}</dd>
                        <dt class="col-4 text-muted">Email</dt><dd class="col-8">{{ $lead->email ?? '—' }}</dd>
                        <dt class="col-4 text-muted">Phone</dt><dd class="col-8">{{ $lead->phone ?? '—' }}</dd>
                        <dt class="col-4 text-muted">Source</dt><dd class="col-8">{{ $lead->source ?? '—' }}</dd>
                        <dt class="col-4 text-muted">Status</dt><dd class="col-8"><span class="badge bg-{{ $lead->status_color }}">{{ ucfirst($lead->status) }}</span></dd>
                        <dt class="col-4 text-muted">Priority</dt><dd class="col-8"><span class="badge bg-{{ $lead->priority_color }}-subtle text-{{ $lead->priority_color }}">{{ ucfirst($lead->priority) }}</span></dd>
                        <dt class="col-4 text-muted">Deal Value</dt><dd class="col-8">{{ $lead->deal_value ? number_format($lead->deal_value, 2) : '—' }}</dd>
                        <dt class="col-4 text-muted">Close Date</dt><dd class="col-8">{{ $lead->expected_close?->format('d M Y') ?? '—' }}</dd>
                        <dt class="col-4 text-muted">Assigned To</dt><dd class="col-8">{{ $lead->assignedTo?->name ?? 'Unassigned' }}</dd>
                    </dl>

                    {{-- Edit form (hidden) --}}
                    <form id="editForm" class="d-none" onsubmit="saveEdit(event)">
                        <div class="mb-2"><label class="form-label small">Name</label><input type="text" name="name" class="form-control form-control-sm" value="{{ $lead->name }}" required></div>
                        <div class="mb-2"><label class="form-label small">Company</label><input type="text" name="company" class="form-control form-control-sm" value="{{ $lead->company }}"></div>
                        <div class="mb-2"><label class="form-label small">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                @foreach ($activityTypes as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label small">Priority</label>
                            <select name="priority" class="form-select form-select-sm">
                                @foreach (['low','medium','high'] as $p)<option value="{{ $p }}" @selected($lead->priority === $p)>{{ ucfirst($p) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label small">Deal Value</label><input type="number" name="deal_value" class="form-control form-control-sm" value="{{ $lead->deal_value }}" step="0.01"></div>
                        <div class="mb-2"><label class="form-label small">Assigned To</label>
                            <select name="assigned_to" class="form-select form-select-sm">
                                <option value="">Unassigned</option>
                                @foreach ($users as $u)<option value="{{ $u->id }}" @selected($lead->assigned_to == $u->id)>{{ $u->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditMode()">Cancel</button>
                        </div>
                    </form>

                    @if ($lead->notes)
                    <div class="mt-3 pt-3 border-top">
                        <div class="text-muted small mb-1">Notes</div>
                        <p class="mb-0 small">{{ $lead->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            @if ($lead->isConverted())
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-1"></i>
                Converted to <a href="#">{{ $lead->convertedCustomer?->name }}</a>
                on {{ $lead->converted_at?->format('d M Y') }}
            </div>
            @endif
        </div>

        {{-- Activity Feed --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Activity History</span>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                        <i class="bi bi-plus-lg me-1"></i> Log Activity
                    </button>
                </div>
                <div class="card-body">
                    @forelse ($lead->activities->sortByDesc('activity_at') as $act)
                    <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                        <div class="flex-shrink-0 text-muted fs-5 mt-1">
                            <i class="bi {{ $act->type_icon }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold small">{{ ucfirst($act->type) }}@if ($act->subject): {{ $act->subject }}@endif</span>
                                <span class="text-muted small">{{ $act->activity_at?->format('d M Y H:i') ?? $act->created_at->format('d M Y') }}</span>
                            </div>
                            @if ($act->description)
                            <p class="mb-0 text-muted small mt-1">{{ $act->description }}</p>
                            @endif
                            <div class="text-muted small mt-1">By {{ $act->createdBy?->name ?? '—' }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">No activities logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Activity Modal --}}
<div class="modal fade" id="addActivityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Log Activity</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select id="act_type" class="form-select">
                        @foreach ($activityTypes as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" id="act_subject" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea id="act_description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Activity Date/Time</label>
                    <input type="datetime-local" id="act_at" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="logActivity()">Log Activity</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleEditMode() {
    document.getElementById('leadInfo').classList.toggle('d-none');
    document.getElementById('editForm').classList.toggle('d-none');
}

function saveEdit(e) {
    e.preventDefault();
    const form = document.getElementById('editForm');
    const data = Object.fromEntries(new FormData(form));
    fetch(`/admin/crm-leads/{{ $lead->id }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ _method: 'PUT', ...data })
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}

function logActivity() {
    fetch('{{ route("admin.crm-leads.activities.store", $lead) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: document.getElementById('act_type').value,
            subject: document.getElementById('act_subject').value,
            description: document.getElementById('act_description').value,
            activity_at: document.getElementById('act_at').value || null,
        })
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}

function convertLead(id) {
    if (!confirm('Convert this lead to a customer?')) return;
    fetch(`/admin/crm-leads/${id}/convert`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}
</script>
@endpush
@endsection
