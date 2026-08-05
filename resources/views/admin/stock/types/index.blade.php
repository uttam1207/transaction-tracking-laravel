@extends('layouts.app')
@section('title', 'Stock Item Types')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Stock</a></li>
    <li class="breadcrumb-item active">Item Types</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Stock Item Types</h4>
            <p>Add, rename and delete item types used for sub-classifying stock items (e.g. Green Fodder, Concentrate)</p>
        </div>
        <a href="{{ route('admin.stock.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Stock
        </a>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
            <i class="bi bi-layers-fill kpi-icon"></i>
            <div class="kpi-value">{{ $types->count() }}</div>
            <div class="kpi-label">Total Types</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-check-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $types->where('is_active', true)->count() }}</div>
            <div class="kpi-label">Active</div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Add Type Form --}}
    <div class="col-md-4">
        <div class="card-glass p-4">
            <h6 class="form-section-label"><i class="bi bi-plus-circle me-1"></i>Add New Item Type</h6>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2">
                    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2">
                    {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.stock-types.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="e.g. Silage, Antibiotic, Colostrum"
                        value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary-grad w-100">
                    <i class="bi bi-save me-1"></i>Save Item Type
                </button>
            </form>

            <div class="mt-4 p-3" style="background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb;">
                <div style="font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
                    <i class="bi bi-info-circle me-1"></i>Note
                </div>
                <div style="font-size:.78rem;color:#6b7280;line-height:1.5;">
                    Item types are optional sub-classifications. Types with items assigned cannot be deleted.
                </div>
            </div>
        </div>
    </div>

    {{-- Types Table --}}
    <div class="col-md-8">
        <div class="card-glass">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-layers me-2 text-primary"></i>All Item Types</h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $types->count() }} types</span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type Name</th>
                            <th class="text-center">Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr>
                                <td style="color:#9ca3af;font-size:.8rem;">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $type->name }}</td>
                                <td class="text-center">
                                    @if($type->is_active)
                                        <span class="spill spill-success" style="font-size:.72rem;">Active</span>
                                    @else
                                        <span class="spill spill-secondary" style="font-size:.72rem;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="act-btn act-edit"
                                        onclick="openEditModal({{ $type->id }}, '{{ addslashes($type->name) }}')"
                                        title="Rename">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.stock-types.destroy', $type) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete type \'{{ addslashes($type->name) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="act-btn act-delete" title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="bi bi-layers"></i>
                                    <p>No item types found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:1px solid #e5e7eb;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Rename Item Type</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTypeForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body px-4 pb-0">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editTypeName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-grad px-4">
                        <i class="bi bi-check-lg me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(id, name) {
    document.getElementById('editTypeName').value = name;
    document.getElementById('editTypeForm').action = '/admin/stock-types/' + id;
    new bootstrap.Modal(document.getElementById('editTypeModal')).show();
}
</script>
@endpush