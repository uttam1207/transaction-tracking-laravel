@extends('layouts.app')
@section('title', 'Edit Role — ' . $role->display_name)
@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#1e1b4b,#4f46e5);">
    <div class="page-hero-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div style="width:46px;height:46px;border-radius:12px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-{{ $role->icon }}" style="font-size:1.3rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="page-hero-title">{{ $role->display_name }}</div>
                    <div class="page-hero-sub" style="font-family:monospace;">{{ $role->name }}
                        @if($role->is_system)<span style="background:rgba(255,255,255,.2);color:#fff;font-size:.65rem;padding:2px 8px;border-radius:5px;margin-left:6px;font-family:sans-serif;">SYSTEM</span>@endif
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:9px;">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
@csrf @method('PUT')
<div class="row g-4">

    {{-- Left --}}
    <div class="col-lg-7">
        <div class="info-card" style="padding:0;overflow:hidden;">
            <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">
                <i class="bi bi-pencil-square" style="color:#4f46e5;margin-right:6px;"></i>Role Details
            </div>
            <div style="padding:24px;">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="flabel">Role Slug (read-only)</label>
                        <input type="text" value="{{ $role->name }}" class="form-control" readonly style="border-radius:9px;border:1.5px solid #e5e7eb;background:#f3f4f6;font-family:monospace;color:#6b7280;">
                        <div style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Role slugs are permanent and cannot be changed.</div>
                    </div>
                    <div class="col-12">
                        <label class="flabel">Display Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $role->display_name) }}" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        @error('display_name')<div style="font-size:.75rem;color:#dc2626;margin-top:3px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="flabel">Description</label>
                        <textarea name="description" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;">{{ old('description', $role->description) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="flabel">Badge Color <span style="color:#ef4444;">*</span></label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="color" id="colorPicker" value="{{ old('color', $role->color) }}" style="width:44px;height:40px;border-radius:9px;border:1.5px solid #e5e7eb;padding:3px;cursor:pointer;background:#fff;">
                            <input type="text" id="colorText" value="{{ old('color', $role->color) }}" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="flabel">Icon <span style="color:#ef4444;">*</span></label>
                        <div class="d-flex gap-2 align-items-center">
                            <div id="iconPreview" style="width:40px;height:40px;border-radius:9px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i id="iconPreviewIcon" class="bi bi-{{ $role->icon }}" style="color:#7c3aed;font-size:1.1rem;"></i>
                            </div>
                            <input type="text" name="icon" id="iconInput" value="{{ old('icon', $role->icon) }}" class="form-control" placeholder="Bootstrap icon name" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="flabel">Status</label>
                        <div class="d-flex gap-3 align-items-center mt-1">
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;font-weight:600;">
                                <input type="radio" name="is_active" value="1" {{ old('is_active', $role->is_active ? '1' : '0') == '1' ? 'checked' : '' }} style="accent-color:#16a34a;width:16px;height:16px;">
                                <span style="color:#16a34a;"><i class="bi bi-check-circle me-1"></i>Active</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.85rem;font-weight:600;">
                                <input type="radio" name="is_active" value="0" {{ old('is_active', $role->is_active ? '1' : '0') == '0' ? 'checked' : '' }} style="accent-color:#dc2626;width:16px;height:16px;">
                                <span style="color:#dc2626;"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                            </label>
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
                @if($role->is_system && $role->name === 'super_admin')
                <div style="font-size:.82rem;background:#ede9fe;color:#5b21b6;padding:12px 16px;border-radius:9px;margin-bottom:16px;">
                    <i class="bi bi-info-circle me-2"></i>Super Admin always has access to all services and cannot be restricted.
                </div>
                @endif
                <div class="row g-2">
                    @foreach($services as $svc)
                    <div class="col-md-6">
                        @php $isGranted = in_array($svc->id, $grantedServiceIds); @endphp
                        <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px solid {{ $isGranted ? '#4f46e5' : '#e5e7eb' }};border-radius:10px;cursor:pointer;background:{{ $isGranted ? '#eef2ff' : '' }};transition:border-color .2s,background .2s;"
                               class="svc-label">
                            <input type="checkbox" name="service_permissions[]" value="{{ $svc->id }}"
                                   class="svc-check" {{ $isGranted ? 'checked' : '' }}
                                   {{ $role->name === 'super_admin' ? 'disabled checked' : '' }}
                                   style="width:16px;height:16px;accent-color:#4f46e5;flex-shrink:0;cursor:pointer;">
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

    {{-- Right: Stats + Actions --}}
    <div class="col-lg-5">

        {{-- Role Stats --}}
        <div class="info-card" style="padding:0;overflow:hidden;margin-bottom:20px;">
            <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">
                <i class="bi bi-bar-chart" style="color:#4f46e5;margin-right:6px;"></i>Role Statistics
            </div>
            <div style="padding:20px;">
                <div style="display:flex;align-items:center;gap:14px;padding:12px;background:#f9fafb;border-radius:10px;">
                    <div style="width:44px;height:44px;border-radius:12px;background:{{ $role->color }}1a;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-people-fill" style="color:{{ $role->color }};font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.4rem;font-weight:800;color:#111827;">
                            {{ \App\Models\User::where('role', $role->name)->count() }}
                        </div>
                        <div style="font-size:.78rem;color:#6b7280;">Users with this role</div>
                    </div>
                </div>
                <div style="margin-top:12px;font-size:.78rem;color:#6b7280;display:flex;gap:6px;align-items:center;">
                    <i class="bi bi-calendar3"></i>
                    Created {{ $role->created_at ? $role->created_at->format('d M Y') : 'N/A' }}
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex flex-column gap-2">
            <button type="submit" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:10px;height:44px;font-size:.9rem;font-weight:700;width:100%;">
                <i class="bi bi-check-circle me-2"></i>Save Changes
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
// Color picker sync
const colorPicker = document.getElementById('colorPicker');
const colorText   = document.getElementById('colorText');
colorPicker.addEventListener('input', function() {
    colorText.value = this.value;
});

// Icon preview
document.getElementById('iconInput').addEventListener('input', function() {
    document.getElementById('iconPreviewIcon').className = 'bi bi-' + (this.value || 'person-badge') + ' text-purple';
    document.getElementById('iconPreviewIcon').style.color = '#7c3aed';
    document.getElementById('iconPreviewIcon').style.fontSize = '1.1rem';
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
    document.querySelectorAll('.svc-check:not([disabled])').forEach(cb => {
        cb.checked = checked;
        cb.dispatchEvent(new Event('change'));
    });
}
</script>
@endpush
