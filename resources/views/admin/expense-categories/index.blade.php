@extends('layouts.app')
@section('title', 'Expense Categories')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Expense Categories</h4>
            <p>Manage expense categories used for recording and reporting farm costs</p>
        </div>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Expenses
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

    {{-- ── LEFT: Add Category Form ── --}}
    <div class="col-lg-4">
        <div class="card-glass p-4">
            <h6 class="fw-bold mb-3" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;color:#4f46e5;">
                <i class="bi bi-plus-circle me-2"></i>Add New Category
            </h6>
            <form action="{{ route('admin.expense-categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        placeholder="e.g. Transport, Insurance"
                        value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-8">
                        <label class="form-label fw-semibold">Bootstrap Icon <small class="text-muted fw-normal">(optional)</small></label>
                        <input type="text" name="icon" class="form-control"
                            placeholder="e.g. bi-truck" value="{{ old('icon') }}"
                            list="icon-list">
                        <datalist id="icon-list">
                            <option value="bi-basket">
                            <option value="bi-capsule">
                            <option value="bi-heart-pulse">
                            <option value="bi-tools">
                            <option value="bi-fuel-pump">
                            <option value="bi-lightning">
                            <option value="bi-droplet">
                            <option value="bi-wrench">
                            <option value="bi-cash-stack">
                            <option value="bi-truck">
                            <option value="bi-building">
                            <option value="bi-three-dots">
                            <option value="bi-shield-check">
                            <option value="bi-tag">
                        </datalist>
                        <div style="font-size:.72rem;color:#9ca3af;margin-top:3px;">Leave blank for default icon</div>
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Color</label>
                        <input type="color" name="color" class="form-control form-control-color"
                            value="{{ old('color', '#6366f1') }}" style="height:38px;">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary-grad w-100">
                    <i class="bi bi-plus-lg me-1"></i>Add Category
                </button>
            </form>
        </div>

        <div class="card-glass p-4 mt-3">
            <h6 class="fw-bold mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Icon Preview</h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach($categories->take(8) as $cat)
                    <div class="d-flex align-items-center gap-1 px-2 py-1 rounded" style="background:{{ $cat->color }}22;border:1px solid {{ $cat->color }}55;font-size:.78rem;">
                        <i class="bi {{ $cat->icon ?? 'bi-tag' }}" style="color:{{ $cat->color }};"></i>
                        <span style="color:{{ $cat->color }};font-weight:600;">{{ $cat->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── RIGHT: Categories Table ── --}}
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <div>
                    <span class="fw-bold" style="font-size:.9rem;">All Categories</span>
                    <span class="ms-2 badge bg-primary bg-opacity-15 text-primary" style="font-size:.72rem;">{{ $categories->count() }}</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="modern-table table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Expenses</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $i => $cat)
                        <tr>
                            <td class="text-muted" style="font-size:.78rem;">{{ $i + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:32px;height:32px;border-radius:8px;background:{{ $cat->color }}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="bi {{ $cat->icon ?? 'bi-tag' }}" style="font-size:.9rem;color:{{ $cat->color }};"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:.85rem;">{{ $cat->name }}</div>
                                        <div style="font-size:.72rem;color:#9ca3af;">{{ $cat->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold" style="font-size:.85rem;">{{ number_format($cat->expense_count ?? 0) }}</span>
                                <div style="font-size:.72rem;color:#9ca3af;">records</div>
                            </td>
                            <td>
                                @if($cat->is_active)
                                    <span class="spill spill-green">Active</span>
                                @else
                                    <span class="spill spill-red">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="act-btn" onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ $cat->icon ?? 'bi-tag' }}', '{{ $cat->color }}')" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.expense-categories.toggle', $cat) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="act-btn" title="{{ $cat->is_active ? 'Deactivate' : 'Activate' }}" style="{{ $cat->is_active ? 'color:#f59e0b;' : 'color:#10b981;' }}">
                                            <i class="bi {{ $cat->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    @if(($cat->expense_count ?? 0) == 0)
                                    <form action="{{ route('admin.expense-categories.destroy', $cat) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete category \'{{ addslashes($cat->name) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button class="act-btn" style="color:#ef4444;" title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                    @else
                                    <button class="act-btn" style="color:#9ca3af;cursor:not-allowed;" title="Cannot delete — has expense records" disabled>
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No categories found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label fw-semibold">Bootstrap Icon</label>
                            <input type="text" name="icon" id="editIcon" class="form-control" placeholder="bi-tag">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Color</label>
                            <input type="color" name="color" id="editColor" class="form-control form-control-color" style="height:38px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-grad px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(id, name, icon, color) {
    document.getElementById('editForm').action = '/admin/expense-categories/' + id;
    document.getElementById('editName').value  = name;
    document.getElementById('editIcon').value  = icon;
    document.getElementById('editColor').value = color;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush
