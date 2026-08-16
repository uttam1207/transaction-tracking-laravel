@extends('layouts.app')
@section('title', 'Salary Components')
@section('breadcrumb')
    <li class="breadcrumb-item active">Salary Components</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Salary Components</h4>
            <p>Define earnings and deductions used in salary structures</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#scModal" onclick="openCreate()">
            <i class="bi bi-plus-lg me-1"></i>Add Component
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
            <label class="flabel">Type</label>
            <select name="type" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:150px;">
                <option value="">All</option>
                <option value="earning" {{ request('type') === 'earning' ? 'selected' : '' }}>Earnings</option>
                <option value="deduction" {{ request('type') === 'deduction' ? 'selected' : '' }}>Deductions</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        @if(request()->hasAny(['search','type']))
            <a href="{{ route('admin.salary-components.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
        @endif
    </form>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">All Salary Components</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $components->total() }} components</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Calculation</th>
                    <th>Default Value</th>
                    <th>Mandatory</th>
                    <th>Taxable</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($components as $comp)
                @php
                    $calcLabels = [
                        'fixed'                => 'Fixed Amount',
                        'percentage_of_basic'  => '% of Basic',
                        'percentage_of_gross'  => '% of Gross',
                    ];
                @endphp
                <tr>
                    <td style="font-weight:700;font-size:.88rem;color:#111827;">{{ $comp->name }}</td>
                    <td>
                        <span style="background:#f0f4ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:monospace;">{{ $comp->code }}</span>
                    </td>
                    <td>
                        @if($comp->type === 'earning')
                        <span class="badge bg-success bg-opacity-15 text-success" style="font-size:.72rem;font-weight:700;border-radius:20px;">Earning</span>
                        @else
                        <span class="badge bg-danger bg-opacity-15 text-danger" style="font-size:.72rem;font-weight:700;border-radius:20px;">Deduction</span>
                        @endif
                    </td>
                    <td style="font-size:.82rem;color:#374151;">{{ $calcLabels[$comp->calculation_type] ?? $comp->calculation_type }}</td>
                    <td style="font-weight:600;color:#374151;">
                        @if($comp->calculation_type === 'fixed')
                            ₹{{ number_format($comp->default_value, 2) }}
                        @else
                            {{ number_format($comp->default_value, 2) }}%
                        @endif
                    </td>
                    <td>
                        @if($comp->is_mandatory)
                        <span class="badge bg-warning bg-opacity-15 text-warning" style="font-size:.72rem;border-radius:20px;font-weight:700;">Yes</span>
                        @else
                        <span style="color:#9ca3af;font-size:.8rem;">No</span>
                        @endif
                    </td>
                    <td>
                        @if($comp->is_taxable)
                        <span class="badge bg-info bg-opacity-15 text-info" style="font-size:.72rem;border-radius:20px;font-weight:700;">Yes</span>
                        @else
                        <span style="color:#9ca3af;font-size:.8rem;">No</span>
                        @endif
                    </td>
                    <td><span class="spill {{ $comp->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $comp->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" title="Edit"
                                onclick="openEdit({{ $comp->id }}, '{{ addslashes($comp->name) }}', '{{ $comp->code }}', '{{ $comp->type }}', '{{ $comp->calculation_type }}', {{ $comp->default_value ?? 0 }}, {{ $comp->is_mandatory ? 1 : 0 }}, {{ $comp->is_taxable ? 1 : 0 }}, {{ $comp->sort_order ?? 0 }}, {{ $comp->is_active ? 1 : 0 }}, '{{ addslashes($comp->description ?? '') }}')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" title="Delete" onclick="deleteComp({{ $comp->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9">
                    <div class="empty-state"><i class="bi bi-cash-stack"></i><p>No salary components defined yet</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($components->hasPages())
    <div class="pagination-wrap">{{ $components->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="scModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="scModalTitle">Add Salary Component</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scForm" method="POST">
                @csrf
                <span id="scMethod"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Name <span class="req">*</span></label>
                            <input type="text" name="name" id="scName" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Code <span class="req">*</span></label>
                            <input type="text" name="code" id="scCode" class="form-control" placeholder="BASIC" required style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;text-transform:uppercase;">
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Sort Order</label>
                            <input type="number" name="sort_order" id="scSort" class="form-control" min="0" value="0" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Type <span class="req">*</span></label>
                            <select name="type" id="scType" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="earning">Earning</option>
                                <option value="deduction">Deduction</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Calculation Type <span class="req">*</span></label>
                            <select name="calculation_type" id="scCalcType" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage_of_basic">% of Basic</option>
                                <option value="percentage_of_gross">% of Gross</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Default Value</label>
                            <input type="number" name="default_value" id="scDefault" class="form-control" min="0" step="0.01" value="0" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Mandatory</label>
                            <select name="is_mandatory" id="scMandatory" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Taxable</label>
                            <select name="is_taxable" id="scTaxable" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status</label>
                            <select name="is_active" id="scStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" id="scDesc" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="scSubmitBtn">Create</button>
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
    document.getElementById('scModalTitle').textContent = 'Add Salary Component';
    document.getElementById('scForm').action = '{{ route("admin.salary-components.store") }}';
    document.getElementById('scMethod').innerHTML = '';
    document.getElementById('scSubmitBtn').textContent = 'Create';
    ['scName','scCode','scDesc'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('scType').value = 'earning';
    document.getElementById('scCalcType').value = 'fixed';
    document.getElementById('scDefault').value = 0;
    document.getElementById('scSort').value = 0;
    document.getElementById('scMandatory').value = 0;
    document.getElementById('scTaxable').value = 0;
    document.getElementById('scStatus').value = 1;
}

function openEdit(id, name, code, type, calcType, defaultVal, isMandatory, isTaxable, sortOrder, isActive, desc) {
    document.getElementById('scModalTitle').textContent = 'Edit Salary Component';
    document.getElementById('scForm').action = `/admin/salary-components/${id}`;
    document.getElementById('scMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('scSubmitBtn').textContent = 'Save Changes';
    document.getElementById('scName').value = name;
    document.getElementById('scCode').value = code;
    document.getElementById('scType').value = type;
    document.getElementById('scCalcType').value = calcType;
    document.getElementById('scDefault').value = defaultVal;
    document.getElementById('scSort').value = sortOrder;
    document.getElementById('scMandatory').value = isMandatory;
    document.getElementById('scTaxable').value = isTaxable;
    document.getElementById('scStatus').value = isActive;
    document.getElementById('scDesc').value = desc;
    new bootstrap.Modal(document.getElementById('scModal')).show();
}

function deleteComp(id) {
    APP.confirm('Delete salary component?', 'Components used in salary structures cannot be deleted.', function() {
        fetch(`/admin/salary-components/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Component deleted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Cannot delete.', 'error');
        });
    });
}
</script>
@endpush
