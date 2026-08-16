@extends('layouts.app')
@section('title', 'Leave Types')
@section('breadcrumb')
    <li class="breadcrumb-item active">Leave Types</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Leave Types</h4>
            <p>Configure leave categories, entitlements, and carry-forward rules</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#ltModal" onclick="openCreate()">
            <i class="bi bi-plus-lg me-1"></i>Add Leave Type
        </button>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="flabel">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or code…"
                value="{{ request('search') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:220px;">
        </div>
        <div>
            <label class="flabel">Status</label>
            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:140px;">
                <option value="">All</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.leave-types.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
        @endif
    </form>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">All Leave Types</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $leaveTypes->total() }} types</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Days/Year</th>
                    <th>Carry Forward</th>
                    <th>Paid</th>
                    <th>Approval Required</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes as $lt)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($lt->color)
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $lt->color }};display:inline-block;flex-shrink:0;"></span>
                            @endif
                            <span style="font-weight:700;font-size:.88rem;color:#111827;">{{ $lt->name }}</span>
                        </div>
                    </td>
                    <td>
                        <span style="background:#f0f4ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:monospace;">{{ $lt->code }}</span>
                    </td>
                    <td style="font-weight:600;color:#374151;">{{ $lt->max_days_per_year }} days</td>
                    <td>
                        @if($lt->carry_forward_days > 0)
                        <span class="badge bg-info bg-opacity-15 text-info" style="font-size:.72rem;font-weight:700;border-radius:20px;">{{ $lt->carry_forward_days }} days</span>
                        @else
                        <span style="color:#9ca3af;font-size:.8rem;">None</span>
                        @endif
                    </td>
                    <td>
                        @if($lt->is_paid)
                        <span class="badge bg-success bg-opacity-15 text-success" style="font-size:.72rem;border-radius:20px;font-weight:700;">Paid</span>
                        @else
                        <span class="badge bg-secondary bg-opacity-15 text-secondary" style="font-size:.72rem;border-radius:20px;font-weight:700;">Unpaid</span>
                        @endif
                    </td>
                    <td>
                        @if($lt->requires_approval)
                        <span class="badge bg-warning bg-opacity-15 text-warning" style="font-size:.72rem;border-radius:20px;font-weight:700;">Yes</span>
                        @else
                        <span style="color:#9ca3af;font-size:.8rem;">No</span>
                        @endif
                    </td>
                    <td><span class="spill {{ $lt->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $lt->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" title="Edit"
                                onclick="openEdit({{ $lt->id }}, '{{ addslashes($lt->name) }}', '{{ $lt->code }}', '{{ $lt->color }}', {{ $lt->max_days_per_year }}, {{ $lt->carry_forward_days }}, {{ $lt->applicable_after_days }}, {{ $lt->is_paid ? 1 : 0 }}, {{ $lt->requires_approval ? 1 : 0 }}, {{ $lt->is_active ? 1 : 0 }}, '{{ addslashes($lt->description ?? '') }}')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" title="Delete" onclick="deleteLt({{ $lt->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8">
                    <div class="empty-state"><i class="bi bi-calendar-x"></i><p>No leave types configured yet</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaveTypes->hasPages())
    <div class="pagination-wrap">{{ $leaveTypes->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="ltModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="ltModalTitle">Add Leave Type</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="ltForm" method="POST">
                @csrf
                <span id="ltMethod"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Name <span class="req">*</span></label>
                            <input type="text" name="name" id="ltName" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Code <span class="req">*</span></label>
                            <input type="text" name="code" id="ltCode" class="form-control" placeholder="ANNUAL" required style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;text-transform:uppercase;">
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Color</label>
                            <input type="color" name="color" id="ltColor" class="form-control form-control-color" value="#4f46e5" style="border-radius:9px;border:1.5px solid #e5e7eb;height:38px;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Days Per Year <span class="req">*</span></label>
                            <input type="number" name="max_days_per_year" id="ltMaxDays" class="form-control" min="0" max="366" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Carry Forward Days</label>
                            <input type="number" name="carry_forward_days" id="ltCarryFwd" class="form-control" min="0" value="0" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Applicable After (days)</label>
                            <input type="number" name="applicable_after_days" id="ltApplicable" class="form-control" min="0" value="0" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Paid Leave</label>
                            <select name="is_paid" id="ltPaid" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Yes (Paid)</option>
                                <option value="0">No (Unpaid)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Requires Approval</label>
                            <select name="requires_approval" id="ltApproval" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status</label>
                            <select name="is_active" id="ltStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" id="ltDesc" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="ltSubmitBtn">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

function openCreate() {
    document.getElementById('ltModalTitle').textContent = 'Add Leave Type';
    document.getElementById('ltForm').action = '{{ route("admin.leave-types.store") }}';
    document.getElementById('ltMethod').innerHTML = '';
    document.getElementById('ltSubmitBtn').textContent = 'Create';
    document.getElementById('ltName').value = '';
    document.getElementById('ltCode').value = '';
    document.getElementById('ltColor').value = '#4f46e5';
    document.getElementById('ltMaxDays').value = 21;
    document.getElementById('ltCarryFwd').value = 0;
    document.getElementById('ltApplicable').value = 0;
    document.getElementById('ltPaid').value = 1;
    document.getElementById('ltApproval').value = 1;
    document.getElementById('ltStatus').value = 1;
    document.getElementById('ltDesc').value = '';
}

function openEdit(id, name, code, color, maxDays, carryFwd, applicable, isPaid, requiresApproval, isActive, desc) {
    document.getElementById('ltModalTitle').textContent = 'Edit Leave Type';
    document.getElementById('ltForm').action = `/admin/leave-types/${id}`;
    document.getElementById('ltMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('ltSubmitBtn').textContent = 'Save Changes';
    document.getElementById('ltName').value = name;
    document.getElementById('ltCode').value = code;
    document.getElementById('ltColor').value = color || '#4f46e5';
    document.getElementById('ltMaxDays').value = maxDays;
    document.getElementById('ltCarryFwd').value = carryFwd;
    document.getElementById('ltApplicable').value = applicable;
    document.getElementById('ltPaid').value = isPaid;
    document.getElementById('ltApproval').value = requiresApproval;
    document.getElementById('ltStatus').value = isActive;
    document.getElementById('ltDesc').value = desc;
    new bootstrap.Modal(document.getElementById('ltModal')).show();
}

function deleteLt(id) {
    APP.confirm('Delete leave type?', 'Existing leave records linked to this type will not be deleted.', function() {
        fetch(`/admin/leave-types/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Leave type deleted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Cannot delete.', 'error');
        });
    });
}
</script>
@endpush
