@extends('layouts.app')
@section('title', 'Service Permissions')
@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#0f172a,#1e293b);">
    <div class="page-hero-body">
        <div class="page-hero-title"><i class="bi bi-shield-lock me-2"></i>Service Permission Management</div>
        <div class="page-hero-sub">Control which roles can access each module — Super Admin only</div>
    </div>
</div>

<div class="info-card mb-2" style="border-left:4px solid #f59e0b;background:#fffbeb;border-radius:10px;padding:12px 18px;font-size:.85rem;color:#92400e;">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Super Admin</strong> always has access to all services and cannot be restricted.
    Changes take effect immediately for new page loads.
</div>

<div class="table-card">
    <div class="table-card-hdr">
        <div class="table-card-title"><i class="bi bi-grid-3x3-gap me-2"></i>Access Matrix</div>
        <div style="font-size:.8rem;color:#6b7280;">Toggle checkboxes to grant or revoke role access</div>
    </div>
    <div class="table-responsive">
        <table class="modern-table" id="permMatrix">
            <thead>
                <tr>
                    <th style="min-width:200px;">Service</th>
                    <th class="text-center" style="width:90px;">
                        <div style="font-size:.75rem;color:#7c3aed;font-weight:700;">SUPER ADMIN</div>
                        <i class="bi bi-lock-fill text-muted" style="font-size:.7rem;"></i>
                    </th>
                    @foreach($roles as $role)
                    <th class="text-center" style="width:90px;">
                        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">{{ str_replace('_',' ',$role) }}</div>
                    </th>
                    @endforeach
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
                                <div style="font-weight:600;font-size:.88rem;">{{ $svc->service_name }}</div>
                                @if($svc->description)
                                <div style="font-size:.74rem;color:#9ca3af;">{{ $svc->description }}</div>
                                @endif
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
                            data-role="{{ $role }}"
                            style="width:16px;height:16px;accent-color:#6366f1;cursor:pointer;"
                            {{ in_array($role, $svc->allowed_roles ?? []) ? 'checked' : '' }}>
                    </td>
                    @endforeach
                </tr>
                @endforeach
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

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

document.querySelectorAll('.perm-toggle').forEach(cb => {
    cb.addEventListener('change', function() {
        const svcId   = this.dataset.svcId;
        const role    = this.dataset.role;
        const row     = document.querySelector(`tr[data-svc-id="${svcId}"]`);

        // Collect all checked roles for this service row
        const allChecked = [...row.querySelectorAll('.perm-toggle:checked')].map(el => el.dataset.role);

        showSaving();

        fetch(`/admin/permissions/${svcId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ allowed_roles: allChecked })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSaved('Saved!');
            } else {
                showSaved('Error saving', true);
            }
        })
        .catch(() => showSaved('Network error', true));
    });
});

function showSaving() {
    const toast = document.getElementById('saveToast');
    document.getElementById('saveSpinner').style.display = '';
    document.getElementById('saveToastMsg').textContent = 'Saving...';
    toast.style.display = 'block';
}

function showSaved(msg, isError = false) {
    document.getElementById('saveSpinner').style.display = 'none';
    document.getElementById('saveToastMsg').textContent = msg;
    if (isError) {
        document.getElementById('saveToast').firstElementChild.style.background = '#dc2626';
    }
    setTimeout(() => {
        document.getElementById('saveToast').style.display = 'none';
        document.getElementById('saveToast').firstElementChild.style.background = '#111827';
    }, 1800);
}
</script>
@endpush
