@extends('layouts.app')
@section('title', 'Vendor Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item active">Vendors</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Vendor Management</h4>
            <p>Manage suppliers and vendors used in purchase orders</p>
        </div>
        <a href="{{ route('admin.procurement.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Procurement
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- ── LEFT: Add Vendor Form ── --}}
    <div class="col-lg-4">
        <div class="card-glass p-4">
            <h6 class="fw-bold mb-3" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;color:#0d9488;">
                <i class="bi bi-plus-circle me-2"></i>Add New Vendor
            </h6>
            <form action="{{ route('admin.vendors.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="e.g. Ramesh Feed Store"
                        value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" class="form-select @error('category') is-invalid @enderror">
                        <option value="">— Select Category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected(old('category')===$cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Contact Person</label>
                    <input type="text" name="contact_person"
                        class="form-control @error('contact_person') is-invalid @enderror"
                        placeholder="e.g. Ramesh Kumar"
                        value="{{ old('contact_person') }}">
                    @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="e.g. 9876543210"
                            value="{{ old('phone') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="vendor@email.com"
                            value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                        rows="2" placeholder="Full address…">{{ old('address') }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary-grad w-100">
                    <i class="bi bi-plus-lg me-1"></i>Add Vendor
                </button>
            </form>
        </div>

        {{-- Quick Stats --}}
        <div class="card-glass p-4 mt-3">
            <h6 class="fw-bold mb-3" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">
                Vendor Stats
            </h6>
            <div class="row g-2">
                <div class="col-6">
                    <div style="background:#f0fdf4;border-radius:10px;padding:12px;text-align:center;">
                        <div class="fw-bold" style="font-size:1.4rem;color:#059669;">{{ $vendors->count() }}</div>
                        <div style="font-size:.72rem;color:#6b7280;">Total Vendors</div>
                    </div>
                </div>
                <div class="col-6">
                    <div style="background:#eff6ff;border-radius:10px;padding:12px;text-align:center;">
                        <div class="fw-bold" style="font-size:1.4rem;color:#2563eb;">{{ $vendors->sum('purchase_orders_count') }}</div>
                        <div style="font-size:.72rem;color:#6b7280;">Total Orders</div>
                    </div>
                </div>
            </div>
            @if($vendors->count() > 0)
            <div class="mt-3">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#9ca3af;letter-spacing:.05em;margin-bottom:6px;">By Category</div>
                @foreach($vendors->groupBy('category') as $cat => $catVendors)
                    <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #f1f5f9;">
                        <span style="font-size:.78rem;color:#374151;">{{ $cat ?: 'Uncategorized' }}</span>
                        <span class="badge" style="background:#e0f2fe;color:#0284c7;font-size:.7rem;">{{ $catVendors->count() }}</span>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── RIGHT: Vendors Table ── --}}
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <div>
                    <span class="fw-bold" style="font-size:.9rem;"><i class="bi bi-shop me-2 text-primary"></i>All Vendors</span>
                    <span class="ms-2 badge bg-primary bg-opacity-15 text-primary" style="font-size:.72rem;">{{ $vendors->count() }}</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="modern-table table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendor</th>
                            <th>Category</th>
                            <th>Contact</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $i => $vendor)
                        <tr>
                            <td class="text-muted" style="font-size:.78rem;">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold" style="font-size:.87rem;color:var(--primary);">{{ $vendor->name }}</div>
                                @if($vendor->address)
                                    <div style="font-size:.72rem;color:#9ca3af;">{{ Str::limit($vendor->address, 45) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($vendor->category)
                                    <span class="spill spill-primary" style="font-size:.72rem;">{{ $vendor->category }}</span>
                                @else
                                    <span style="color:#d1d5db;font-size:.78rem;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($vendor->contact_person)
                                    <div style="font-size:.82rem;font-weight:600;">{{ $vendor->contact_person }}</div>
                                @endif
                                @if($vendor->phone)
                                    <div style="font-size:.75rem;color:#6b7280;"><i class="bi bi-telephone me-1"></i>{{ $vendor->phone }}</div>
                                @endif
                                @if($vendor->email)
                                    <div style="font-size:.75rem;color:#6b7280;"><i class="bi bi-envelope me-1"></i>{{ $vendor->email }}</div>
                                @endif
                                @if(!$vendor->contact_person && !$vendor->phone && !$vendor->email)
                                    <span style="color:#d1d5db;font-size:.78rem;">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="fw-bold" style="font-size:.88rem;color:#374151;">{{ $vendor->purchase_orders_count }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="act-btn"
                                        onclick="openEditModal(
                                            {{ $vendor->id }},
                                            '{{ addslashes($vendor->name) }}',
                                            '{{ addslashes($vendor->category ?? '') }}',
                                            '{{ addslashes($vendor->contact_person ?? '') }}',
                                            '{{ addslashes($vendor->phone ?? '') }}',
                                            '{{ addslashes($vendor->email ?? '') }}',
                                            '{{ addslashes($vendor->address ?? '') }}'
                                        )" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($vendor->purchase_orders_count == 0)
                                    <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete vendor \'{{ addslashes($vendor->name) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button class="act-btn" style="color:#ef4444;" title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                    @else
                                    <button class="act-btn" style="color:#9ca3af;cursor:not-allowed;"
                                        title="Cannot delete — has {{ $vendor->purchase_orders_count }} purchase order(s)" disabled>
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="bi bi-shop"></i>
                                <p>No vendors added yet. Use the form on the left to add your first vendor.</p>
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
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category" id="editCategory" class="form-select">
                                <option value="">— None —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Contact Person</label>
                            <input type="text" name="contact_person" id="editContactPerson" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" id="editPhone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" id="editAddress" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-grad px-4">
                        <i class="bi bi-check-lg me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(id, name, category, contactPerson, phone, email, address) {
    document.getElementById('editForm').action = '/admin/vendors/' + id;
    document.getElementById('editName').value          = name;
    document.getElementById('editContactPerson').value = contactPerson;
    document.getElementById('editPhone').value         = phone;
    document.getElementById('editEmail').value         = email;
    document.getElementById('editAddress').value       = address;

    const catSelect = document.getElementById('editCategory');
    for (let opt of catSelect.options) {
        opt.selected = (opt.value === category);
    }

    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush
