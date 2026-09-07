@extends('layouts.app')
@section('title', 'CRM Leads')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">CRM</a></li>
    <li class="breadcrumb-item active">Leads</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Leads Pipeline</h4>
            <p>Track and manage sales leads through the pipeline</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary-grad btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                <i class="bi bi-plus-lg me-1"></i> New Lead
            </button>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div style="background:#eff6ff;border-radius:14px;padding:18px 20px;">
            <div class="fw-bold" style="font-size:1.5rem;color:#2563eb;">{{ $stats['total'] }}</div>
            <div style="font-size:.75rem;color:#6b7280;">Total Leads</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:#f0fdf4;border-radius:14px;padding:18px 20px;">
            <div class="fw-bold" style="font-size:1.5rem;color:#16a34a;">{{ $stats['new'] }}</div>
            <div style="font-size:.75rem;color:#6b7280;">New</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:#fefce8;border-radius:14px;padding:18px 20px;">
            <div class="fw-bold" style="font-size:1.5rem;color:#ca8a04;">{{ $stats['won'] }}</div>
            <div style="font-size:.75rem;color:#6b7280;">Won</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:#faf5ff;border-radius:14px;padding:18px 20px;">
            <div class="fw-bold" style="font-size:1.5rem;color:#7c3aed;">{{ number_format($stats['pipeline'], 2) }}</div>
            <div style="font-size:.75rem;color:#6b7280;">Pipeline Value</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-glass mb-3 px-4 py-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Name, company, email…" value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="priority" class="form-select">
                <option value="">All Priority</option>
                @foreach ($priorities as $p)
                    <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="assigned_to" class="form-select">
                <option value="">All Assignees</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected(request('assigned_to') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary-grad btn-sm px-4">Filter</button>
            <a href="{{ route('admin.crm-leads.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
        </div>
    </form>
</div>

<div class="card-glass overflow-hidden">
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Name / Company</th>
                    <th>Contact</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th class="text-end">Deal Value</th>
                    <th>Assigned To</th>
                    <th>Expected Close</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($leads as $lead)
                <tr>
                    <td>
                        <a href="{{ route('admin.crm-leads.show', $lead) }}" class="fw-semibold text-primary">{{ $lead->name }}</a>
                        @if ($lead->company)<div class="text-muted small">{{ $lead->company }}</div>@endif
                    </td>
                    <td class="small text-muted">
                        @if ($lead->email){{ $lead->email }}<br>@endif
                        @if ($lead->phone){{ $lead->phone }}@endif
                    </td>
                    <td class="text-muted small">{{ $lead->source ?? '—' }}</td>
                    <td><span class="badge bg-{{ $lead->status_color }}">{{ ucfirst($lead->status) }}</span></td>
                    <td><span class="badge bg-{{ $lead->priority_color }}-subtle text-{{ $lead->priority_color }}">{{ ucfirst($lead->priority) }}</span></td>
                    <td class="text-end">{{ $lead->deal_value ? number_format($lead->deal_value, 2) : '—' }}</td>
                    <td class="small text-muted">{{ $lead->assignedTo?->name ?? '—' }}</td>
                    <td class="small">{{ $lead->expected_close?->format('d M Y') ?? '—' }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.crm-leads.show', $lead) }}" class="act-btn"><i class="bi bi-eye"></i></a>
                            <button class="act-btn text-danger" onclick="deleteLead({{ $lead->id }})"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No leads found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($leads->hasPages())
    <div class="px-3 py-2">{{ $leads->withQueryString()->links() }}</div>
    @endif
</div>

{{-- Add Lead Modal --}}
<div class="modal fade" id="addLeadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">New Lead</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                    <input type="text" id="l_name" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Company</label>
                    <input type="text" id="l_company" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="l_email" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" id="l_phone" class="form-control" pattern="[6-9][0-9]{9}" maxlength="10" placeholder="e.g. 9876543210">
                    <div class="form-text" style="font-size:.72rem;color:#9ca3af;">10 digits, starting with 6–9</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Source</label>
                    <input type="text" id="l_source" class="form-control" placeholder="website, referral…">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select id="l_status" class="form-select">
                        @foreach ($statuses as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Priority <span class="text-danger">*</span></label>
                    <select id="l_priority" class="form-select">
                        @foreach ($priorities as $p)<option value="{{ $p }}" @selected($p === 'medium')>{{ ucfirst($p) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Deal Value</label>
                    <input type="number" id="l_deal_value" class="form-control" step="0.01" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Expected Close</label>
                    <input type="date" id="l_expected_close" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Assigned To</label>
                    <select id="l_assigned" class="form-select">
                        <option value="">Unassigned</option>
                        @foreach ($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea id="l_notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="createLead()">Create Lead</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function createLead() {
    const name  = document.getElementById('l_name').value.trim();
    const email = document.getElementById('l_email').value.trim();
    const phone = document.getElementById('l_phone').value.trim();

    if (!name)  { APP.toast('Contact name is required.', 'error'); return; }
    if (!email) { APP.toast('Email is required.', 'error'); return; }
    if (!phone) { APP.toast('Phone is required.', 'error'); return; }
    if (!/^[6-9][0-9]{9}$/.test(phone)) { APP.toast('Phone must be 10 digits starting with 6–9.', 'error'); return; }

    fetch('{{ route("admin.crm-leads.store") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name,
            company: document.getElementById('l_company').value,
            email,
            phone,
            source: document.getElementById('l_source').value,
            status: document.getElementById('l_status').value,
            priority: document.getElementById('l_priority').value,
            deal_value: document.getElementById('l_deal_value').value || null,
            expected_close: document.getElementById('l_expected_close').value || null,
            assigned_to: document.getElementById('l_assigned').value || null,
            notes: document.getElementById('l_notes').value,
        })
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else APP.toast(d.message, 'error'); });
}

function deleteLead(id) {
    APP.confirm('Delete Lead', 'Delete this lead? This cannot be undone.', () => {
        fetch(`/admin/crm-leads/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => { if (d.success) location.reload(); else APP.toast(d.message, 'error'); });
    });
}
</script>
@endpush

@endsection
