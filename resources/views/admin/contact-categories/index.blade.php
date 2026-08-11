@extends('layouts.app')
@section('title', 'Contact Categories')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.contacts.index') }}">Contacts</a></li>
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Contact Categories</h4>
            <p>Manage dynamic categories for organising your contacts directory</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-light px-4">
                <i class="bi bi-arrow-left me-1"></i>Back to Contacts
            </a>
            <button class="btn btn-primary-grad btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-lg me-1"></i>Add Category
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Category Cards --}}
<div class="row g-3">
    @forelse($categories as $cat)
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card-glass h-100" style="border-top:3px solid {{ $cat->color }};">
            <div class="px-4 pt-4 pb-3">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div style="width:46px;height:46px;border-radius:12px;background:{{ $cat->color }}20;display:flex;align-items:center;justify-content:center;">
                        <i class="bi {{ $cat->icon }}" style="font-size:1.3rem;color:{{ $cat->color }};"></i>
                    </div>
                    <span class="badge px-2 py-1 {{ $cat->is_active ? 'bg-success' : 'bg-danger' }} bg-opacity-15 {{ $cat->is_active ? 'text-success' : 'text-danger' }}" style="font-size:.7rem;">
                        {{ $cat->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <h6 class="fw-bold mb-1" style="color:#1f2937;">{{ $cat->name }}</h6>
                @if($cat->description)
                    <p style="font-size:.78rem;color:#6b7280;margin-bottom:10px;line-height:1.5;">{{ $cat->description }}</p>
                @endif
                <div style="font-size:.78rem;color:#9ca3af;">
                    <i class="bi bi-people me-1"></i>
                    <strong style="color:#374151;">{{ $cat->contacts_count }}</strong> contact{{ $cat->contacts_count !== 1 ? 's' : '' }}
                </div>
            </div>
            <div class="px-4 py-3 border-top d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary flex-fill edit-cat-btn"
                    data-id="{{ $cat->id }}"
                    data-name="{{ $cat->name }}"
                    data-icon="{{ $cat->icon }}"
                    data-color="{{ $cat->color }}"
                    data-description="{{ $cat->description }}"
                    data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                    <i class="bi bi-pencil me-1"></i>Edit
                </button>
                <form method="POST" action="{{ route('admin.contact-categories.destroy', $cat) }}"
                      onsubmit="return confirm('Delete category \'{{ addslashes($cat->name) }}\'? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" {{ $cat->contacts_count > 0 ? 'disabled title=\'Has contacts — cannot delete\'' : '' }}>
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
                <a href="{{ route('admin.contacts.index', ['category_id' => $cat->id]) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card-glass text-center py-5">
            <i class="bi bi-tags" style="font-size:2.5rem;color:#d1d5db;"></i>
            <p class="mt-3 text-muted">No categories yet. Add your first category.</p>
        </div>
    </div>
    @endforelse
</div>

{{-- Icon Reference --}}
<div class="card-glass mt-4 px-4 py-3">
    <h6 class="fw-bold mb-3" style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">
        <i class="bi bi-grid me-1"></i>Common Bootstrap Icons for Categories
    </h6>
    <div class="d-flex flex-wrap gap-2">
        @foreach([
            'bi-people','bi-truck','bi-bag-check','bi-cone-striped','bi-heart-pulse','bi-basket',
            'bi-gear','bi-bus-front','bi-bank','bi-shop','bi-building','bi-person-badge',
            'bi-telephone','bi-envelope','bi-star','bi-bookmark','bi-tag','bi-briefcase',
            'bi-droplet','bi-cash-coin','bi-cart-check','bi-clipboard','bi-tools','bi-wrench'
        ] as $icon)
        <span class="badge bg-light text-dark border py-2 px-2" style="font-size:.85rem;cursor:pointer;" title="{{ $icon }}" onclick="copyIcon('{{ $icon }}')">
            <i class="bi {{ $icon }}"></i>
        </span>
        @endforeach
    </div>
    <div class="mt-2" style="font-size:.72rem;color:#9ca3af;">Click an icon to copy its class name. Use in the Icon field when adding/editing a category.</div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <form method="POST" action="{{ route('admin.contact-categories.store') }}">
            @csrf
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px 16px;">
                <h5 class="modal-title fw-bold">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Milk Vendors">
                </div>
                <div class="row g-3">
                    <div class="col-8">
                        <label class="form-label fw-semibold">Icon Class</label>
                        <input type="text" name="icon" class="form-control" value="bi-people" placeholder="e.g. bi-truck" id="addIconInput">
                        <div class="form-text">Bootstrap Icon class. See reference below.</div>
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Color</label>
                        <input type="color" name="color" class="form-control form-control-color w-100" value="#6366f1" style="height:42px;">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Short description of this category…"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary-grad px-4">Save Category</button>
            </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Category Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <form method="POST" id="editCategoryForm" action="">
            @csrf @method('PUT')
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px 16px;">
                <h5 class="modal-title fw-bold">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="row g-3">
                    <div class="col-8">
                        <label class="form-label fw-semibold">Icon Class</label>
                        <input type="text" name="icon" id="editIcon" class="form-control">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Color</label>
                        <input type="color" name="color" id="editColor" class="form-control form-control-color w-100" style="height:42px;">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary-grad px-4">Update Category</button>
            </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.edit-cat-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('editCategoryForm').action = '/admin/contact-categories/' + this.dataset.id;
        document.getElementById('editName').value        = this.dataset.name;
        document.getElementById('editIcon').value        = this.dataset.icon;
        document.getElementById('editColor').value       = this.dataset.color;
        document.getElementById('editDescription').value = this.dataset.description;
    });
});

function copyIcon(icon) {
    navigator.clipboard.writeText(icon).then(() => {
        const addInput = document.getElementById('addIconInput');
        if (addInput) addInput.value = icon;
    });
}
</script>
@endpush