@extends('layouts.app')
@section('title', 'Service Permissions')
@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#0f172a,#1e293b);">
    <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h4 style="margin:0;font-weight:800;color:#fff;"><i class="bi bi-shield-lock me-2"></i>Service Permission Management</h4>
            <p style="opacity:.8;margin:4px 0 0;font-size:.85rem;color:#fff;">Control which roles can access each module — Super Admin only</p>
        </div>
        <button class="btn btn-primary-grad btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-lg me-1"></i>Add Service
        </button>
    </div>
</div>

<div class="mb-2" style="border-left:4px solid #f59e0b;background:#fffbeb;border-radius:10px;padding:12px 18px;font-size:.85rem;color:#92400e;">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Super Admin</strong> always has access to all services and cannot be restricted.
    Changes take effect immediately for new page loads.
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title"><i class="bi bi-grid-3x3-gap me-2"></i>Access Matrix</span>
        <div style="font-size:.8rem;color:#6b7280;">Toggle checkboxes to grant or revoke role access</div>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0" id="permMatrix">
            <thead>
                <tr>
                    <th style="min-width:220px;">Service</th>
                    <th class="text-center" style="width:90px;">
                        <div style="font-size:.75rem;color:#7c3aed;font-weight:700;">SUPER ADMIN</div>
                        <i class="bi bi-lock-fill text-muted" style="font-size:.7rem;"></i>
                    </th>
                    @foreach($roles as $role)
                    <th class="text-center" style="width:90px;">
                        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">{{ $role->display_label }}</div>
                    </th>
                    @endforeach
                    <th style="width:90px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $svc)
                <tr data-svc-id="{{ $svc->id }}" data-svc-key="{{ $svc->service_key }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#818cf8);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.85rem;flex-shrink:0;">
                                <i class="bi bi-{{ $svc->icon }}"></i>
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:.88rem;">
                                    {{ $svc->service_name }}
                                    @if(!$svc->is_active)
                                        <span class="badge bg-warning bg-opacity-20 text-warning ms-1" style="font-size:.65rem;">Inactive</span>
                                    @endif
                                </div>
                                @if($svc->description)
                                <div style="font-size:.74rem;color:#9ca3af;">{{ $svc->description }}</div>
                                @endif
                                <div style="font-size:.7rem;color:#d1d5db;font-family:monospace;">{{ $svc->service_key }}</div>
                            </div>
                        </div>
                    </td>
                    {{-- Super admin always locked/checked --}}
                    <td class="text-center">
                        <input type="checkbox" checked disabled
                            style="width:16px;height:16px;accent-color:#7c3aed;cursor:not-allowed;opacity:.5;">
                    </td>
                    @foreach($roles as $role)
                    <td class="text-center">
                        <input type="checkbox"
                            class="perm-toggle"
                            data-svc-id="{{ $svc->id }}"
                            data-role="{{ $role->name }}"
                            style="width:16px;height:16px;accent-color:#6366f1;cursor:pointer;"
                            {{ in_array($role->name, $svc->allowed_roles ?? []) ? 'checked' : '' }}>
                    </td>
                    @endforeach
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <button class="btn btn-sm btn-outline-secondary edit-svc-btn" style="padding:3px 8px;font-size:.75rem;"
                                data-id="{{ $svc->id }}"
                                data-name="{{ $svc->service_name }}"
                                data-key="{{ $svc->service_key }}"
                                data-desc="{{ $svc->description }}"
                                data-icon="{{ $svc->icon }}"
                                data-sort="{{ $svc->sort_order }}"
                                data-active="{{ $svc->is_active ? '1' : '0' }}"
                                data-bs-toggle="modal" data-bs-target="#editServiceModal"
                                title="Edit service">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-svc-btn" style="padding:3px 8px;font-size:.75rem;"
                                data-id="{{ $svc->id }}"
                                data-name="{{ $svc->service_name }}"
                                title="Delete service">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($services->isEmpty())
                <tr>
                    <td colspan="{{ 3 + $roles->count() }}" class="text-center py-4 text-muted" style="font-size:.85rem;">
                        No services configured yet. Click <strong>Add Service</strong> to get started.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div id="saveToast" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;">
    <div style="background:#111827;color:#fff;padding:10px 18px;border-radius:10px;font-size:.85rem;display:flex;align-items:center;gap:8px;box-shadow:0 4px 20px rgba(0,0,0,.3);">
        <div class="spinner-border spinner-border-sm text-light" role="status" id="saveSpinner"></div>
        <span id="saveToastMsg">Saving...</span>
    </div>
</div>

{{-- Add Service Modal --}}
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px 16px;">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
                    <input type="text" id="addName" class="form-control" placeholder="e.g. Milk Production" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Service Key <span class="text-danger">*</span>
                        <span style="font-size:.72rem;color:#9ca3af;font-weight:400;"> (lowercase, underscores only)</span>
                    </label>
                    <input type="text" id="addKey" class="form-control" placeholder="e.g. milk_production" required style="font-family:monospace;">
                    <div class="form-text">Used in code — cannot be changed later. Example: <code>milk_production</code></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <input type="text" id="addDesc" class="form-control" placeholder="Short description">
                </div>
                <div class="row g-3">
                    <div class="col-8">
                        <label class="form-label fw-semibold">Bootstrap Icon</label>
                        <input type="text" id="addIcon" class="form-control" value="grid" placeholder="e.g. grid, bar-chart, people">
                        <div class="form-text">Bootstrap icon name without "bi-"</div>
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" id="addSort" class="form-control" value="99" min="0">
                    </div>
                </div>
                <div id="addError" class="alert alert-danger mt-3 d-none" style="font-size:.82rem;"></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-grad px-4" id="addServiceBtn">
                    <span id="addBtnText">Add Service</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Service Modal --}}
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px 16px;">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2 text-secondary"></i>Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <input type="hidden" id="editId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
                    <input type="text" id="editName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Service Key</label>
                    <input type="text" id="editKeyDisplay" class="form-control" disabled style="font-family:monospace;background:#f9fafb;">
                    <div class="form-text text-muted">Service key cannot be changed after creation.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <input type="text" id="editDesc" class="form-control">
                </div>
                <div class="row g-3">
                    <div class="col-8">
                        <label class="form-label fw-semibold">Bootstrap Icon</label>
                        <input type="text" id="editIcon" class="form-control">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" id="editSort" class="form-control" min="0">
                    </div>
                </div>
                <div class="mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="editActive" role="switch">
                        <label class="form-check-label fw-semibold" for="editActive">Active</label>
                    </div>
                </div>
                <div id="editError" class="alert alert-danger mt-3 d-none" style="font-size:.82rem;"></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-grad px-4" id="editServiceBtn">Update Service</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// ── Role checkbox toggles ──────────────────────────────────────────────────
document.querySelectorAll('.perm-toggle').forEach(cb => {
    cb.addEventListener('change', function() {
        const checkbox = this;
        const svcId    = this.dataset.svcId;
        const row      = document.querySelector(`tr[data-svc-id="${svcId}"]`);
        const allChecked = [...row.querySelectorAll('.perm-toggle:checked')].map(el => el.dataset.role);

        showSaving();

        fetch(`/admin/permissions/${svcId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ allowed_roles: allChecked })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSaved('Saved!');
            } else {
                checkbox.checked = !checkbox.checked; // revert on server error
                showSaved(data.message || 'Error saving', true);
            }
        })
        .catch(() => {
            checkbox.checked = !checkbox.checked; // revert on network/parse error
            showSaved('Network error', true);
        });
    });
});

// ── Auto-generate service key from name ───────────────────────────────────
document.getElementById('addName').addEventListener('input', function() {
    const key = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    document.getElementById('addKey').value = key;
});

// ── Add Service ───────────────────────────────────────────────────────────
document.getElementById('addServiceBtn').addEventListener('click', function() {
    const btn     = this;
    const errEl   = document.getElementById('addError');
    errEl.classList.add('d-none');

    const name = document.getElementById('addName').value.trim();
    const key  = document.getElementById('addKey').value.trim();
    if (!name || !key) { errEl.textContent = 'Service Name and Key are required.'; errEl.classList.remove('d-none'); return; }
    if (!/^[a-z0-9_]+$/.test(key)) { errEl.textContent = 'Service Key must be lowercase letters, numbers, and underscores only.'; errEl.classList.remove('d-none'); return; }

    btn.disabled = true;
    document.getElementById('addBtnText').textContent = 'Saving…';

    fetch('/admin/permissions', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            service_name: name,
            service_key:  key,
            description:  document.getElementById('addDesc').value.trim(),
            icon:         document.getElementById('addIcon').value.trim() || 'grid',
            sort_order:   parseInt(document.getElementById('addSort').value) || 99,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            errEl.textContent = data.message || 'Failed to add service.';
            errEl.classList.remove('d-none');
            btn.disabled = false;
            document.getElementById('addBtnText').textContent = 'Add Service';
        }
    })
    .catch(() => {
        errEl.textContent = 'Network error. Please try again.';
        errEl.classList.remove('d-none');
        btn.disabled = false;
        document.getElementById('addBtnText').textContent = 'Add Service';
    });
});

// ── Edit Service — populate modal ─────────────────────────────────────────
document.querySelectorAll('.edit-svc-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editId').value           = this.dataset.id;
        document.getElementById('editName').value         = this.dataset.name;
        document.getElementById('editKeyDisplay').value   = this.dataset.key;
        document.getElementById('editDesc').value         = this.dataset.desc;
        document.getElementById('editIcon').value         = this.dataset.icon;
        document.getElementById('editSort').value         = this.dataset.sort;
        document.getElementById('editActive').checked     = this.dataset.active === '1';
        document.getElementById('editError').classList.add('d-none');
    });
});

document.getElementById('editServiceBtn').addEventListener('click', function() {
    const btn   = this;
    const errEl = document.getElementById('editError');
    errEl.classList.add('d-none');
    const id = document.getElementById('editId').value;

    btn.disabled = true;
    btn.textContent = 'Saving…';

    fetch(`/admin/permissions/${id}/meta`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            service_name: document.getElementById('editName').value.trim(),
            description:  document.getElementById('editDesc').value.trim(),
            icon:         document.getElementById('editIcon').value.trim() || 'grid',
            sort_order:   parseInt(document.getElementById('editSort').value) || 0,
            is_active:    document.getElementById('editActive').checked,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            errEl.textContent = data.message || 'Failed to update service.';
            errEl.classList.remove('d-none');
            btn.disabled = false;
            btn.textContent = 'Update Service';
        }
    })
    .catch(() => {
        errEl.textContent = 'Network error. Please try again.';
        errEl.classList.remove('d-none');
        btn.disabled = false;
        btn.textContent = 'Update Service';
    });
});

// ── Delete Service ────────────────────────────────────────────────────────
document.querySelectorAll('.delete-svc-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id   = this.dataset.id;
        const name = this.dataset.name;
        if (!confirm(`Delete service "${name}"?\n\nThis will remove all role permissions for this service.`)) return;

        fetch(`/admin/permissions/${id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Failed to delete.');
        })
        .catch(() => alert('Network error.'));
    });
});

// ── Toast helpers ─────────────────────────────────────────────────────────
function showSaving() {
    const toast = document.getElementById('saveToast');
    document.getElementById('saveSpinner').style.display = '';
    document.getElementById('saveToastMsg').textContent = 'Saving...';
    toast.style.display = 'block';
}

function showSaved(msg, isError = false) {
    document.getElementById('saveSpinner').style.display = 'none';
    document.getElementById('saveToastMsg').textContent = msg;
    if (isError) document.getElementById('saveToast').firstElementChild.style.background = '#dc2626';
    setTimeout(() => {
        document.getElementById('saveToast').style.display = 'none';
        document.getElementById('saveToast').firstElementChild.style.background = '#111827';
    }, 1800);
}
</script>
@endpush
