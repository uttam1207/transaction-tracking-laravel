@extends('layouts.app')
@section('title', 'Sale Item Types')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
    <li class="breadcrumb-item active">Item Types</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Sale Item Types</h4>
            <p>Manage dynamic item types shown in the sales invoice form</p>
        </div>
        <button class="btn btn-primary-grad btn-sm px-4" onclick="openAdd()">
            <i class="bi bi-plus-lg me-1"></i> Add Type
        </button>
    </div>
</div>

<div class="card-glass overflow-hidden">
    <div class="table-responsive">
        <table class="modern-table table mb-0" id="typesTable">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Name</th>
                    <th>Milk Type?</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $t)
                <tr id="row-{{ $t->id }}">
                    <td class="text-muted small">{{ $t->id }}</td>
                    <td class="fw-semibold">{{ $t->name }}</td>
                    <td>
                        @if($t->is_milk_type)
                            <span class="badge bg-info text-white"><i class="bi bi-droplet-fill me-1"></i>Yes — Fat fields</span>
                        @else
                            <span class="badge bg-light text-dark border">No</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $t->sort_order }}</td>
                    <td>
                        <span class="badge {{ $t->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $t->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="act-btn act-edit me-1" onclick="openEdit({{ $t->id }}, '{{ addslashes($t->name) }}', {{ $t->is_milk_type ? 'true' : 'false' }}, {{ $t->sort_order }}, {{ $t->is_active ? 'true' : 'false' }})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="act-btn" style="color:#dc2626;" onclick="deleteType({{ $t->id }}, '{{ addslashes($t->name) }}')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No item types defined.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add / Edit Modal --}}
<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="typeModalTitle">Add Item Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                    <input type="text" id="t_name" class="form-control" placeholder="e.g. Butter Milk Sales" maxlength="100">
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="t_milk" role="switch">
                        <label class="form-check-label" for="t_milk">
                            <strong>Milk Type</strong>
                            <span class="text-muted ms-1" style="font-size:.8rem;">— shows Fat% &amp; Fat Rate fields in invoice</span>
                        </label>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" id="t_sort" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-6 d-flex align-items-end" id="activeToggleWrap">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="t_active" role="switch" checked>
                            <label class="form-check-label" for="t_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-grad btn-sm px-4" onclick="saveType()">Save</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const modal = new bootstrap.Modal(document.getElementById('typeModal'));

function openAdd() {
    document.getElementById('typeModalTitle').textContent = 'Add Item Type';
    document.getElementById('editId').value = '';
    document.getElementById('t_name').value = '';
    document.getElementById('t_milk').checked = false;
    document.getElementById('t_sort').value = 0;
    document.getElementById('t_active').checked = true;
    document.getElementById('activeToggleWrap').classList.add('d-none');
    modal.show();
}

function openEdit(id, name, isMilk, sort, isActive) {
    document.getElementById('typeModalTitle').textContent = 'Edit Item Type';
    document.getElementById('editId').value = id;
    document.getElementById('t_name').value = name;
    document.getElementById('t_milk').checked = isMilk;
    document.getElementById('t_sort').value = sort;
    document.getElementById('t_active').checked = isActive;
    document.getElementById('activeToggleWrap').classList.remove('d-none');
    modal.show();
}

function saveType() {
    const id   = document.getElementById('editId').value;
    const url  = id ? `/admin/sales/item-types/${id}` : '/admin/sales/item-types';
    const method = id ? 'PATCH' : 'POST';
    const body = {
        name:         document.getElementById('t_name').value.trim(),
        is_milk_type: document.getElementById('t_milk').checked,
        sort_order:   parseInt(document.getElementById('t_sort').value) || 0,
        is_active:    document.getElementById('t_active').checked,
    };
    if (!body.name) { APP.toast('Name is required', 'error'); return; }

    fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { modal.hide(); location.reload(); }
        else APP.toast(d.message ?? 'Error saving type', 'error');
    })
    .catch(() => APP.toast('Request failed', 'error'));
}

function deleteType(id, name) {
    if (!confirm(`Delete item type "${name}"?\n\nThis will fail if existing invoices use this type.`)) return;
    fetch(`/admin/sales/item-types/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            document.getElementById('row-' + id)?.remove();
            APP.toast('Deleted', 'success');
        } else {
            APP.toast(d.message ?? 'Cannot delete', 'error');
        }
    });
}
</script>
@endpush

@endsection