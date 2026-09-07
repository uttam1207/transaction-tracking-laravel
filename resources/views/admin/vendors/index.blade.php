@extends('layouts.app')
@section('title', 'Vendor Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item active">Vendors</li>
@endsection

@push('styles')
<style>
/* Category checkbox pills */
.cat-cb-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 50px;
    border: 1.5px solid var(--bs-border-color);
    cursor: pointer;
    font-size: .78rem;
    font-weight: 500;
    transition: all .15s;
    user-select: none;
}
.cat-cb-pill:hover { border-color: #0d9488; color: #0d9488; }
.cat-cb-pill input[type="checkbox"] { display: none; }
.cat-cb-pill.checked {
    background: #0d9488;
    border-color: #0d9488;
    color: #fff;
}
</style>
@endpush

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Vendor Management</h4>
            <p>Manage suppliers and vendors used in purchase orders</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary px-4" data-bs-toggle="modal" data-bs-target="#manageCatsModal">
                <i class="bi bi-tags me-1"></i>Manage Categories
            </button>
            <a href="{{ route('admin.procurement.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back to Procurement
            </a>
        </div>
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
                    <label class="form-label fw-semibold">Categories <span class="text-muted fw-normal small">(select all that apply)</span></label>
                    <div class="d-flex flex-wrap gap-2" id="createCatWrap">
                        @foreach($categories as $cat)
                        <label class="cat-cb-pill {{ in_array($cat, old('category', [])) ? 'checked' : '' }}">
                            <input type="checkbox" name="category[]" value="{{ $cat }}"
                                {{ in_array($cat, old('category', [])) ? 'checked' : '' }}>
                            {{ $cat }}
                        </label>
                        @endforeach
                    </div>
                    @error('category')<div class="text-danger mt-1" style="font-size:.8rem;">{{ $message }}</div>@enderror
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
                        <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="e.g. 9876543210"
                            value="{{ old('phone') }}" pattern="[6-9][0-9]{9}" maxlength="10" required>
                        <div class="form-text">10 digits, starting with 6–9</div>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="vendor@email.com"
                            value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                        rows="2" placeholder="Full address…" required>{{ old('address') }}</textarea>
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
                @foreach($categoryStats as $cat => $count)
                @if($count > 0)
                    <div class="d-flex justify-content-between align-items-center py-1" style="border-bottom:1px solid #f1f5f9;">
                        <span style="font-size:.78rem;color:#374151;">{{ $cat }}</span>
                        <span class="badge" style="background:#e0f2fe;color:#0284c7;font-size:.7rem;">{{ $count }}</span>
                    </div>
                @endif
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
                            <th>Categories</th>
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
                                @if(!empty($vendor->category))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($vendor->category as $cat)
                                            <span class="spill spill-primary" style="font-size:.68rem;">{{ $cat }}</span>
                                        @endforeach
                                    </div>
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
                                            {{ json_encode($vendor->category ?? []) }},
                                            '{{ addslashes($vendor->contact_person ?? '') }}',
                                            '{{ addslashes($vendor->phone ?? '') }}',
                                            '{{ addslashes($vendor->email ?? '') }}',
                                            '{{ addslashes($vendor->address ?? '') }}'
                                        )" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($vendor->purchase_orders_count == 0)
                                    <form id="deleteForm{{ $vendor->id }}" action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="act-btn" style="color:#ef4444;" title="Delete"
                                            onclick="deleteVendor({{ $vendor->id }}, '{{ addslashes($vendor->name) }}')">
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

{{-- Manage Categories Modal --}}
<div class="modal fade" id="manageCatsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Manage Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-2">
                <p class="text-muted mb-3" style="font-size:.8rem;">Add or remove vendor categories. Changes apply to all vendor forms immediately.</p>
                {{-- Add form --}}
                <div class="d-flex gap-2 mb-4">
                    <input type="text" id="newCatInput" class="form-control" placeholder="New category name…" style="border-radius:9px;">
                    <button class="btn btn-primary-grad px-3" id="addCatBtn" style="border-radius:9px;white-space:nowrap;">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                {{-- Category list --}}
                <div id="catList" style="max-height:300px;overflow-y:auto;">
                    <div class="text-center text-muted py-3" style="font-size:.85rem;">Loading…</div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-2">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" style="border-radius:9px;">Done</button>
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
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Categories <span class="text-muted fw-normal small">(select all that apply)</span></label>
                            <div class="d-flex flex-wrap gap-2" id="editCatWrap">
                                @foreach($categories as $cat)
                                <label class="cat-cb-pill">
                                    <input type="checkbox" name="category[]" value="{{ $cat }}" class="edit-cat-cb">
                                    {{ $cat }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Contact Person</label>
                            <input type="text" name="contact_person" id="editContactPerson" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="editPhone" class="form-control" pattern="[6-9][0-9]{9}" maxlength="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                            <textarea name="address" id="editAddress" class="form-control" rows="2" required></textarea>
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
// ── Pill toggle helpers ──────────────────────────────────────────────────────
function bindPillToggle(wrap) {
    wrap.querySelectorAll('.cat-cb-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            pill.classList.toggle('checked', pill.querySelector('input').checked);
        });
    });
}
bindPillToggle(document.getElementById('createCatWrap'));
bindPillToggle(document.getElementById('editCatWrap'));

// ── Edit modal ───────────────────────────────────────────────────────────────
function openEditModal(id, name, categories, contactPerson, phone, email, address) {
    document.getElementById('editForm').action = '/admin/vendors/' + id;
    document.getElementById('editName').value          = name;
    document.getElementById('editContactPerson').value = contactPerson;
    document.getElementById('editPhone').value         = phone;
    document.getElementById('editEmail').value         = email;
    document.getElementById('editAddress').value       = address;

    document.querySelectorAll('#editCatWrap .cat-cb-pill').forEach(pill => {
        const cb = pill.querySelector('input');
        const checked = Array.isArray(categories) && categories.includes(cb.value);
        cb.checked = checked;
        pill.classList.toggle('checked', checked);
    });

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

// ── Vendor delete with Swal confirm ─────────────────────────────────────────
function deleteVendor(id, name) {
    APP.confirm('Delete Vendor', `Delete vendor "<strong>${name}</strong>"? This cannot be undone.`, () => {
        document.getElementById('deleteForm' + id).submit();
    });
}

// ── Manage Categories modal ──────────────────────────────────────────────────
const manageCatsModal = document.getElementById('manageCatsModal');
const catList         = document.getElementById('catList');
const newCatInput     = document.getElementById('newCatInput');
const CSRF            = '{{ csrf_token() }}';

function buildPillHtml(cat) {
    return `<label class="cat-cb-pill"><input type="checkbox" name="category[]" value="${cat}">${cat}</label>`;
}

function rebuildPills(categories) {
    ['createCatWrap', 'editCatWrap'].forEach(wrapId => {
        const wrap = document.getElementById(wrapId);
        wrap.innerHTML = categories.map(c => buildPillHtml(c)).join('');
        bindPillToggle(wrap);
    });
}

function loadCategories() {
    catList.innerHTML = '<div class="text-center text-muted py-3" style="font-size:.85rem;">Loading…</div>';
    fetch('/admin/vendor-categories', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                catList.innerHTML = '<div class="text-center text-muted py-3" style="font-size:.85rem;">No categories yet.</div>';
                return;
            }
            catList.innerHTML = data.map(c => `
                <div class="d-flex align-items-center justify-content-between py-2 px-1" style="border-bottom:1px solid #f1f5f9;" id="catRow${c.id}">
                    <span style="font-size:.87rem;">${c.name}</span>
                    <button class="btn btn-link p-0 text-danger" style="font-size:.8rem;" onclick="deleteCategory(${c.id},'${c.name.replace(/'/g,"\\'")}')">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>`).join('');
            rebuildPills(data.map(c => c.name));
        })
        .catch(() => {
            catList.innerHTML = '<div class="text-center text-danger py-3" style="font-size:.85rem;">Failed to load.</div>';
        });
}

manageCatsModal.addEventListener('show.bs.modal', loadCategories);

document.getElementById('addCatBtn').addEventListener('click', function () {
    const name = newCatInput.value.trim();
    if (!name) { APP.toast('Please enter a category name.', 'error'); return; }

    fetch('/admin/vendor-categories', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ name })
    }).then(r => r.json()).then(d => {
        if (d.success || d.id) {
            newCatInput.value = '';
            APP.toast(d.message ?? 'Category added.', 'success');
            loadCategories();
        } else {
            APP.toast(d.message ?? 'Failed to add category.', 'error');
        }
    }).catch(() => APP.toast('Request failed.', 'error'));
});

newCatInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('addCatBtn').click(); }
});

function deleteCategory(id, name) {
    APP.confirm('Delete Category', `Remove category "<strong>${name}</strong>"? Vendors using it will lose this tag.`, () => {
        fetch('/admin/vendor-categories/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => {
            if (d.success) {
                APP.toast(d.message ?? 'Category deleted.', 'success');
                loadCategories();
            } else {
                APP.toast(d.message ?? 'Failed to delete.', 'error');
            }
        }).catch(() => APP.toast('Request failed.', 'error'));
    });
}
</script>
@endpush
