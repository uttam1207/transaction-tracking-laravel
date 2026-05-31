@extends('layouts.app')
@section('title', 'Create Role')
@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#1e1b4b,#4f46e5);">
    <div class="page-hero-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="page-hero-title"><i class="bi bi-plus-circle me-2"></i>Create New Role</div>
                <div class="page-hero-sub">Define a custom role and assign service access</div>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:9px;">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<form action="{{ route('admin.roles.store') }}" method="POST">
@csrf
<div class="row g-4">

    {{-- Left: Role Details --}}
    <div class="col-lg-7">
        <div class="info-card" style="padding:0;overflow:hidden;">
            <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">
                <i class="bi bi-person-badge" style="color:#4f46e5;margin-right:6px;"></i>Role Details
            </div>
            <div style="padding:24px;">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="flabel">Role Name (Display) <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="display_name" id="displayName" class="form-control" placeholder="e.g. HR Manager, Accountant, Sales Executive" required value="{{ old('display_name') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        <div style="font-size:.72rem;color:#9ca3af;margin-top:4px;">
                            Slug preview: <span id="slugPreview" style="font-family:monospace;color:#6366f1;font-weight:600;">—</span>
                        </div>
                        @error('display_name')<div style="font-size:.75rem;color:#dc2626;margin-top:3px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="flabel">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="What this role can do…" style="border-radius:9px;border:1.5px solid #e5e7eb;">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="flabel">Badge Color <span style="color:#ef4444;">*</span></label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="color" id="colorPicker" value="{{ old('color','#6366f1') }}" style="width:44px;height:40px;border-radius:9px;border:1.5px solid #e5e7eb;padding:3px;cursor:pointer;background:#fff;">
                            <input type="text" id="colorText" value="{{ old('color','#6366f1') }}" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="flabel">Icon <span style="color:#ef4444;">*</span></label>
                        <div class="d-flex gap-2 align-items-center">
                            <div id="iconPreview" style="width:40px;height:40px;border-radius:9px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i id="iconPreviewIcon" class="bi bi-person-badge" style="color:#7c3aed;font-size:1.1rem;"></i>
                            </div>
                            <input type="text" name="icon" id="iconInput" value="{{ old('icon','person-badge') }}" class="form-control" placeholder="Bootstrap icon name e.g. person-badge" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div style="font-size:.7rem;color:#9ca3af;margin-top:4px;">
                            Browse: <a href="https://icons.getbootstrap.com" target="_blank" style="color:#6366f1;">icons.getbootstrap.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Service Permissions --}}
        <div class="info-card mt-4" style="padding:0;overflow:hidden;">
            <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;display:flex;align-items:center;justify-content:space-between;">
                <span><i class="bi bi-grid-3x3-gap" style="color:#4f46e5;margin-right:6px;"></i>Service Access</span>
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="toggleAll(true)" style="font-size:.7rem;padding:3px 10px;border-radius:6px;background:#dcfce7;color:#16a34a;border:none;cursor:pointer;font-weight:600;">Grant All</button>
                    <button type="button" onclick="toggleAll(false)" style="font-size:.7rem;padding:3px 10px;border-radius:6px;background:#fee2e2;color:#dc2626;border:none;cursor:pointer;font-weight:600;">Revoke All</button>
                </div>
            </div>
            <div style="padding:20px;">
                <div class="row g-2">
                    @foreach($services as $svc)
                    <div class="col-md-6">
                        <label style="display:flex;align-items:center;gap-10px;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;cursor:pointer;transition:border-color .2s,background .2s;gap:10px;"
                               class="svc-label" data-id="{{ $svc->id }}">
                            <input type="checkbox" name="service_permissions[]" value="{{ $svc->id }}"
                                   class="svc-check" style="width:16px;height:16px;accent-color:#4f46e5;flex-shrink:0;cursor:pointer;">
                            <div style="width:28px;height:28px;border-radius:7px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-{{ $svc->icon }}" style="color:#7c3aed;font-size:.8rem;"></i>
                            </div>
                            <div>
                                <div style="font-size:.82rem;font-weight:600;color:#111827;">{{ $svc->service_name }}</div>
                                @if($svc->description)
                                <div style="font-size:.7rem;color:#9ca3af;">{{ $svc->description }}</div>
                                @endif
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Preview + Actions --}}
    <div class="col-lg-5">
        {{-- Live Preview --}}
        <div class="info-card" style="padding:0;overflow:hidden;margin-bottom:20px;">
            <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">
                <i class="bi bi-eye" style="color:#4f46e5;margin-right:6px;"></i>Live Preview
            </div>
            <div style="padding:24px;text-align:center;">
                <div id="previewCard" style="display:inline-flex;flex-direction:column;align-items:center;gap:10px;">
                    <div id="previewIcon" style="width:60px;height:60px;border-radius:16px;background:#6366f11a;display:flex;align-items:center;justify-content:center;">
                        <i id="previewIconEl" class="bi bi-person-badge" style="font-size:1.6rem;color:#6366f1;"></i>
                    </div>
                    <div id="previewName" style="font-weight:700;font-size:1rem;color:#111827;">New Role</div>
                    <span id="previewBadge" style="font-size:.72rem;padding:3px 12px;border-radius:20px;background:#6366f11a;color:#6366f1;font-weight:700;">NEW ROLE</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex flex-column gap-2">
            <button type="submit" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:10px;height:44px;font-size:.9rem;font-weight:700;width:100%;">
                <i class="bi bi-plus-circle me-2"></i>Create Role
            </button>
            <a href="{{ route('admin.roles.index') }}" style="background:#fff;color:#6b7280;border:1.5px solid #e5e7eb;border-radius:10px;height:44px;font-size:.9rem;font-weight:600;width:100%;text-decoration:none;display:flex;align-items:center;justify-content:center;">
                Cancel
            </a>
        </div>
    </div>

</div>
</form>

@endsection
@push('scripts')
<script>
// Slug preview
document.getElementById('displayName').addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'');
    document.getElementById('slugPreview').textContent = slug || '—';
    document.getElementById('previewName').textContent = this.value || 'New Role';
    document.getElementById('previewBadge').textContent = (this.value || 'NEW ROLE').toUpperCase();
});

// Color picker sync
const colorPicker = document.getElementById('colorPicker');
const colorText   = document.getElementById('colorText');
colorPicker.addEventListener('input', function() {
    colorText.value = this.value;
    updatePreviewColor(this.value);
});
function updatePreviewColor(c) {
    document.getElementById('previewIcon').style.background = c + '1a';
    document.getElementById('previewIconEl').style.color = c;
    document.getElementById('previewBadge').style.background = c + '1a';
    document.getElementById('previewBadge').style.color = c;
}
updatePreviewColor(colorPicker.value);

// Icon preview
document.getElementById('iconInput').addEventListener('input', function() {
    const iconClass = 'bi bi-' + (this.value || 'person-badge');
    document.getElementById('iconPreviewIcon').className = iconClass;
    document.getElementById('previewIconEl').className = iconClass + ' ' + document.getElementById('previewIconEl').className.split(' ').filter(c=>!c.startsWith('bi')).join(' ');
    document.getElementById('previewIconEl').className = iconClass;
});

// Service permission toggle styling
document.querySelectorAll('.svc-check').forEach(cb => {
    cb.addEventListener('change', function() {
        const label = this.closest('.svc-label');
        label.style.borderColor = this.checked ? '#4f46e5' : '#e5e7eb';
        label.style.background  = this.checked ? '#eef2ff' : '';
    });
});

function toggleAll(checked) {
    document.querySelectorAll('.svc-check').forEach(cb => {
        cb.checked = checked;
        cb.dispatchEvent(new Event('change'));
    });
}
</script>
@endpush
