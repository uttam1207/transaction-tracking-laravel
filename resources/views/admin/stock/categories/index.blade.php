@extends('layouts.app')
@section('title', 'Stock Categories')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Stock</a></li>
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Stock Categories</h4>
            <p>Add, rename and delete inventory categories used when classifying stock items</p>
        </div>
        <a href="{{ route('admin.stock.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Stock
        </a>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
            <i class="bi bi-tag-fill kpi-icon"></i>
            <div class="kpi-value">{{ $categories->count() }}</div>
            <div class="kpi-label">Total Categories</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-check-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $categories->where('is_active', true)->count() }}</div>
            <div class="kpi-label">Active</div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Add Category Form --}}
    <div class="col-md-4">
        <div class="card-glass p-4">
            <h6 class="form-section-label"><i class="bi bi-plus-circle me-1"></i>Add New Category</h6>

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

            <form action="{{ route('admin.stock-categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="e.g. Fertiliser, Supplements"
                        value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary-grad w-100">
                    <i class="bi bi-save me-1"></i>Save Category
                </button>
            </form>

            <div class="mt-4 p-3" style="background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb;">
                <div style="font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
                    <i class="bi bi-info-circle me-1"></i>Note
                </div>
                <div style="font-size:.78rem;color:#6b7280;line-height:1.5;">
                    Categories with items assigned cannot be deleted. Rename them freely at any time.
                </div>
            </div>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="col-md-8">
        <div class="card-glass">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-tag me-2 text-primary"></i>All Categories</h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $categories->count() }} categories</span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category Name</th>
                            <th class="text-center">Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                            <tr>
                                <td style="color:#9ca3af;font-size:.8rem;">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $cat->name }}</td>
                                <td class="text-center">
                                    @if($cat->is_active)
                                        <span class="spill spill-success" style="font-size:.72rem;">Active</span>
                                    @else
                                        <span class="spill spill-secondary" style="font-size:.72rem;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="act-btn act-edit"
                                        onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                                        title="Rename">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.stock-categories.destroy', $cat) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete category \'{{ addslashes($cat->name) }}\'?')">
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
                                    <i class="bi bi-tag"></i>
                                    <p>No categories found</p>
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
<div class="modal fade" id="editCatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:1px solid #e5e7eb;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Rename Category</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCatForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body px-4 pb-0">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editCatName" class="form-control" required>
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
    document.getElementById('editCatName').value = name;
    document.getElementById('editCatForm').action = '/admin/stock-categories/' + id;
    new bootstrap.Modal(document.getElementById('editCatModal')).show();
}
</script>
@endpush