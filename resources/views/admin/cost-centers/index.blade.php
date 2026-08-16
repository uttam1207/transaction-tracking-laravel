@extends('layouts.app')
@section('title', 'Cost Centers')
@section('breadcrumb')
    <li class="breadcrumb-item active">Cost Centers</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Cost Centers</h4>
            <p>Track budgets and expenditure per department or branch</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#ccModal" onclick="openCreate()">
            <i class="bi bi-plus-lg me-1"></i>Add Cost Center
        </button>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="flabel">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or code…"
                value="{{ request('search') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:200px;">
        </div>
        <div>
            <label class="flabel">Department</label>
            <select name="department_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:170px;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="flabel">Branch</label>
            <select name="branch_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:160px;">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        @if(request()->hasAny(['search','department_id','branch_id']))
            <a href="{{ route('admin.cost-centers.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
        @endif
    </form>
</div>

<div class="table-card">
    <div class="card-header">
        <span class="card-title">All Cost Centers</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Cost Center</th>
                    <th>Code</th>
                    <th>Department</th>
                    <th>Branch</th>
                    <th>Monthly Budget</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costCenters as $cc)
                <tr>
                    <td style="font-weight:700;font-size:.88rem;color:#111827;">{{ $cc->name }}</td>
                    <td>
                        @if($cc->code)
                        <span style="background:#f0f4ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:monospace;">{{ $cc->code }}</span>
                        @else<span style="color:#9ca3af;">—</span>@endif
                    </td>
                    <td style="font-size:.84rem;color:#374151;">{{ $cc->department->name ?? '—' }}</td>
                    <td style="font-size:.84rem;color:#374151;">{{ $cc->branch->name ?? '—' }}</td>
                    <td>
                        @if($cc->monthly_budget)
                        <span style="font-weight:700;color:#059669;font-size:.85rem;">
                            {{ number_format($cc->monthly_budget, 2) }}
                        </span>
                        @else<span style="color:#9ca3af;font-size:.83rem;">No budget</span>@endif
                    </td>
                    <td><span class="spill {{ $cc->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $cc->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" title="Edit"
                                onclick="openEdit({{ $cc->id }}, '{{ addslashes($cc->name) }}', '{{ $cc->code }}', {{ $cc->department_id ?? 'null' }}, {{ $cc->branch_id ?? 'null' }}, '{{ addslashes($cc->description ?? '') }}', '{{ $cc->monthly_budget ?? '' }}', {{ $cc->is_active ? 1 : 0 }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" title="Delete" onclick="deleteCc({{ $cc->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-cash-stack"></i><p>No cost centers found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($costCenters->hasPages())
    <div class="pagination-wrap">{{ $costCenters->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="ccModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="ccModalTitle">Add Cost Center</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="ccForm" method="POST">
                @csrf
                <span id="ccMethod"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="flabel">Name <span class="req">*</span></label>
                            <input type="text" name="name" id="ccName" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Code <span class="req">*</span></label>
                            <input type="text" name="code" id="ccCode" class="form-control" placeholder="CC001" required style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Department</label>
                            <select name="department_id" id="ccDept" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">No Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Branch</label>
                            <select name="branch_id" id="ccBranch" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">No Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Monthly Budget</label>
                            <input type="number" name="monthly_budget" id="ccBudget" class="form-control" step="0.01" min="0" placeholder="0.00" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Status</label>
                            <select name="is_active" id="ccStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" id="ccDesc" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="ccSubmitBtn">Create</button>
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
    document.getElementById('ccModalTitle').textContent = 'Add Cost Center';
    document.getElementById('ccForm').action = '{{ route("admin.cost-centers.store") }}';
    document.getElementById('ccMethod').innerHTML = '';
    document.getElementById('ccSubmitBtn').textContent = 'Create';
    ['ccName','ccCode','ccBudget','ccDesc'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('ccDept').value   = '';
    document.getElementById('ccBranch').value = '';
    document.getElementById('ccStatus').value = '1';
}

function openEdit(id, name, code, deptId, branchId, desc, budget, isActive) {
    document.getElementById('ccModalTitle').textContent = 'Edit Cost Center';
    document.getElementById('ccForm').action = `/admin/cost-centers/${id}`;
    document.getElementById('ccMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('ccSubmitBtn').textContent = 'Save Changes';
    document.getElementById('ccName').value   = name;
    document.getElementById('ccCode').value   = code || '';
    document.getElementById('ccDept').value   = deptId || '';
    document.getElementById('ccBranch').value = branchId || '';
    document.getElementById('ccDesc').value   = desc || '';
    document.getElementById('ccBudget').value = budget || '';
    document.getElementById('ccStatus').value = isActive;
    new bootstrap.Modal(document.getElementById('ccModal')).show();
}

function deleteCc(id) {
    APP.confirm('Delete cost center?', 'This cannot be undone.', function() {
        fetch(`/admin/cost-centers/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Cost center deleted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Cannot delete.', 'error');
        });
    });
}
</script>
@endpush
