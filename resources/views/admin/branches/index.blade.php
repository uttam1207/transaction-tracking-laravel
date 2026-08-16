@extends('layouts.app')
@section('title', 'Branch Management')
@section('breadcrumb')
    <li class="breadcrumb-item active">Branches</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Branch Management</h4>
            <p>Manage company branches and locations</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat"><div class="v">{{ $branches->total() }}</div><div class="l">Total</div></div>
            <div class="hero-vr"></div>
            <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#branchModal" onclick="openCreate()">
                <i class="bi bi-plus-lg me-1"></i>Add Branch
            </button>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="flabel">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Branch name or code…"
                value="{{ request('search') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:220px;">
        </div>
        <div>
            <label class="flabel">Status</label>
            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:130px;">
                <option value="">All</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
        @endif
    </form>
</div>

<div class="table-card">
    <div class="card-header">
        <span class="card-title">All Branches</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Code</th>
                    <th>City / State</th>
                    <th>Manager</th>
                    <th>HQ</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td style="font-weight:700;font-size:.88rem;color:#111827;">{{ $branch->name }}</td>
                    <td>
                        @if($branch->code)
                        <span style="background:#f0f4ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:monospace;">{{ $branch->code }}</span>
                        @else<span style="color:#9ca3af;">—</span>@endif
                    </td>
                    <td style="font-size:.83rem;color:#374151;">
                        {{ collect([$branch->city, $branch->state])->filter()->join(', ') ?: '—' }}
                    </td>
                    <td style="font-size:.84rem;color:#374151;">{{ $branch->manager->name ?? '—' }}</td>
                    <td>
                        @if($branch->is_headquarters)
                        <span style="background:#fef3c7;color:#d97706;padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:700;">HQ</span>
                        @else<span style="color:#9ca3af;font-size:.8rem;">—</span>@endif
                    </td>
                    <td><span class="spill {{ $branch->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" title="Edit"
                                onclick="openEdit(
                                    {{ $branch->id }},
                                    '{{ addslashes($branch->name) }}',
                                    '{{ $branch->code }}',
                                    '{{ addslashes($branch->address ?? '') }}',
                                    '{{ $branch->city }}',
                                    '{{ $branch->state }}',
                                    '{{ $branch->country }}',
                                    '{{ $branch->phone }}',
                                    '{{ $branch->email }}',
                                    {{ $branch->manager_id ?? 'null' }},
                                    {{ $branch->is_headquarters ? 1 : 0 }},
                                    {{ $branch->is_active ? 1 : 0 }}
                                )">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" title="Delete" onclick="deleteBranch({{ $branch->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-building"></i><p>No branches found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($branches->hasPages())
    <div class="pagination-wrap">{{ $branches->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="branchModalTitle">Add Branch</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="branchForm" method="POST">
                @csrf
                <span id="branchMethod"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="flabel">Branch Name <span class="req">*</span></label>
                            <input type="text" name="name" id="bName" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Code</label>
                            <input type="text" name="code" id="bCode" class="form-control" placeholder="HQ01" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Address</label>
                            <input type="text" name="address" id="bAddress" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">City</label>
                            <input type="text" name="city" id="bCity" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">State</label>
                            <input type="text" name="state" id="bState" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Country</label>
                            <input type="text" name="country" id="bCountry" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Phone</label>
                            <input type="text" name="phone" id="bPhone" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Email</label>
                            <input type="email" name="email" id="bEmail" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Branch Manager</label>
                            <select name="manager_id" id="bManager" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">No Manager</option>
                                @foreach($managers as $mgr)
                                    <option value="{{ $mgr->id }}">{{ $mgr->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Headquarters?</label>
                            <select name="is_headquarters" id="bHq" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="0">No</option>
                                <option value="1">Yes (HQ)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Status</label>
                            <select name="is_active" id="bStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="branchSubmitBtn">Create</button>
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
    document.getElementById('branchModalTitle').textContent = 'Add Branch';
    document.getElementById('branchForm').action = '{{ route("admin.branches.store") }}';
    document.getElementById('branchMethod').innerHTML = '';
    document.getElementById('branchSubmitBtn').textContent = 'Create';
    ['bName','bCode','bAddress','bCity','bState','bCountry','bPhone','bEmail'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('bManager').value = '';
    document.getElementById('bHq').value = '0';
    document.getElementById('bStatus').value = '1';
}

function openEdit(id, name, code, address, city, state, country, phone, email, managerId, isHq, isActive) {
    document.getElementById('branchModalTitle').textContent = 'Edit Branch';
    document.getElementById('branchForm').action = `/admin/branches/${id}`;
    document.getElementById('branchMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('branchSubmitBtn').textContent = 'Save Changes';
    document.getElementById('bName').value    = name;
    document.getElementById('bCode').value    = code || '';
    document.getElementById('bAddress').value = address || '';
    document.getElementById('bCity').value    = city || '';
    document.getElementById('bState').value   = state || '';
    document.getElementById('bCountry').value = country || '';
    document.getElementById('bPhone').value   = phone || '';
    document.getElementById('bEmail').value   = email || '';
    document.getElementById('bManager').value = managerId || '';
    document.getElementById('bHq').value      = isHq;
    document.getElementById('bStatus').value  = isActive;
    new bootstrap.Modal(document.getElementById('branchModal')).show();
}

function deleteBranch(id) {
    APP.confirm('Delete branch?', 'This cannot be undone.', function() {
        fetch(`/admin/branches/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Branch deleted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Cannot delete.', 'error');
        });
    });
}
</script>
@endpush
