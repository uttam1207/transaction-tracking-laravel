@extends('layouts.app')
@section('title', 'Role Management')
@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#1e1b4b,#4f46e5);">
    <div class="page-hero-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="page-hero-title"><i class="bi bi-shield-lock me-2"></i>Role Management</div>
                <div class="page-hero-sub">Create and manage roles — Super Admin only</div>
            </div>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:9px;">
                <i class="bi bi-plus-circle me-1"></i>New Role
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;font-size:.85rem;" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px;font-size:.85rem;" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #4f46e5;padding:20px;">
            <div style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Total Roles</div>
            <div style="font-size:2rem;font-weight:800;color:#4f46e5;">{{ $roles->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #16a34a;padding:20px;">
            <div style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">System Roles</div>
            <div style="font-size:2rem;font-weight:800;color:#16a34a;">{{ $roles->where('is_system', true)->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #f59e0b;padding:20px;">
            <div style="font-size:.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Custom Roles</div>
            <div style="font-size:2rem;font-weight:800;color:#f59e0b;">{{ $roles->where('is_system', false)->count() }}</div>
        </div>
    </div>
</div>

{{-- Roles Grid --}}
<div class="row g-3">
    @foreach($roles as $role)
    <div class="col-md-6 col-xl-4">
        <div class="info-card" style="border-top:4px solid {{ $role->color }};padding:0;overflow:hidden;">
            <div style="padding:20px 20px 14px;">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;border-radius:12px;background:{{ $role->color }}1a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-{{ $role->icon }}" style="font-size:1.2rem;color:{{ $role->color }};"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:.95rem;color:#111827;">{{ $role->display_name ?: ucwords(str_replace('_',' ',$role->name)) }}</div>
                            <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">{{ $role->name }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        @if($role->is_system)
                        <span style="font-size:.65rem;background:#ede9fe;color:#7c3aed;padding:3px 8px;border-radius:6px;font-weight:700;">SYSTEM</span>
                        @else
                        <span style="font-size:.65rem;background:#dcfce7;color:#16a34a;padding:3px 8px;border-radius:6px;font-weight:700;">CUSTOM</span>
                        @endif
                        @if(!$role->is_active)
                        <span style="font-size:.65rem;background:#fee2e2;color:#dc2626;padding:3px 8px;border-radius:6px;font-weight:700;">INACTIVE</span>
                        @endif
                    </div>
                </div>

                @if($role->description)
                <div style="font-size:.78rem;color:#6b7280;margin-top:12px;line-height:1.5;">{{ $role->description }}</div>
                @endif

                <div style="margin-top:14px;padding-top:12px;border-top:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <i class="bi bi-people-fill" style="color:#9ca3af;font-size:.85rem;"></i>
                        <span style="font-size:.8rem;color:#374151;font-weight:600;">{{ $role->users_count }} user{{ $role->users_count != 1 ? 's' : '' }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.roles.edit', $role->id) }}"
                           style="font-size:.75rem;padding:5px 12px;background:#f3f4f6;color:#374151;border-radius:7px;text-decoration:none;font-weight:600;border:1px solid #e5e7eb;">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        @if(!$role->is_system)
                        <button onclick="confirmDelete({{ $role->id }}, '{{ addslashes($role->display_name) }}', {{ $role->users_count }})"
                                style="font-size:.75rem;padding:5px 12px;background:#fee2e2;color:#dc2626;border-radius:7px;border:1px solid #fca5a5;font-weight:600;cursor:pointer;">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Delete Confirm Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content">
            <div class="modal-header" style="background:#fee2e2;border-radius:12px 12px 0 0;">
                <h6 class="modal-title fw-bold" style="color:#dc2626;"><i class="bi bi-exclamation-triangle me-2"></i>Delete Role</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:22px;">
                <p style="font-size:.88rem;color:#374151;" id="deleteMsg">Are you sure you want to delete this role?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" style="border-radius:8px;">
                        <i class="bi bi-trash me-1"></i>Delete Role
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
function confirmDelete(id, name, usersCount) {
    const msg = usersCount > 0
        ? `Cannot delete "<strong>${name}</strong>" — ${usersCount} user(s) are still assigned to it.`
        : `Are you sure you want to delete the role "<strong>${name}</strong>"? This cannot be undone.`;
    document.getElementById('deleteMsg').innerHTML = msg;
    document.getElementById('deleteForm').action = `/admin/roles/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
