@extends('layouts.app')
@section('title', 'Users')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>User Management</h4>
            <p>Manage all system users, roles and permissions</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="page-hero-stat">
                <div class="v">{{ $users->total() }}</div>
                <div class="l">Total</div>
            </div>
            <div class="hero-vr"></div>
            <button class="btn btn-sm btn-primary-grad px-4" style="border-radius:9px;" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus me-1"></i>Add User
            </button>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <div class="position-relative">
                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.8rem;pointer-events:none;"></i>
                <input type="text" name="search" class="form-control ps-4" placeholder="Name, email, username…" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                @foreach(['super_admin','admin','manager','employee','auditor','viewer'] as $r)
                    <option value="{{ $r }}" {{ request('role')==$r?'selected':'' }}>{{ ucwords(str_replace('_',' ',$r)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['active','inactive','pending','suspended'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="department_id" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-filter btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-filter btn-outline-secondary px-3"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="card-header">
        <span class="card-title">All Users
            <span style="margin-left:8px;background:#ede9fe;color:#7c3aed;font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:20px;">{{ $users->total() }}</span>
        </span>
        <div class="d-flex gap-2 align-items-center">
            <select id="bulkAction" class="form-select form-select-sm" style="width:auto;border-radius:8px;font-size:.8rem;">
                <option value="">Bulk Actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.8rem;" onclick="executeBulk()">Apply</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th class="ps-3" style="width:36px;"><input type="checkbox" id="selectAll" style="border-radius:4px;"></th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="ps-3"><input type="checkbox" value="{{ $user->id }}" class="user-check" style="border-radius:4px;"></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $user->avatar_url }}" class="rounded-circle" width="36" height="36" alt="">
                            <div>
                                <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ $user->name }}</div>
                                <div style="font-size:.74rem;color:#9ca3af;">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="background:#ede9fe;color:#7c3aed;padding:3px 9px;border-radius:6px;font-size:.72rem;font-weight:700;">
                            {{ ucwords(str_replace('_',' ',$user->role)) }}
                        </span>
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $user->department?->name ?? '—' }}</td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox"
                                   {{ $user->status==='active'?'checked':'' }}
                                   onchange="toggleStatus({{ $user->id }}, this)"
                                   {{ $user->id===auth()->id()?'disabled':'' }}>
                        </div>
                    </td>
                    <td style="font-size:.78rem;color:#9ca3af;">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.users.show', $user) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                            @if($user->id !== auth()->id())
                            <button class="act-btn act-delete" onclick="deleteUser({{ $user->id }})" title="Delete"><i class="bi bi-trash"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-people"></i><p>No users found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="pagination-wrap">
        <span class="pagination-info">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ number_format($users->total()) }}</span>
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Create User Modal --}}
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Create New User</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createUserForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Full Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Email <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Phone</label>
                            <input type="tel" name="phone" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Role <span class="req">*</span></label>
                            <select name="role" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach(['admin','manager','employee','auditor','viewer'] as $r)
                                    <option value="{{ $r }}">{{ ucwords(str_replace('_',' ',$r)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Department</label>
                            <select name="department_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">— Select —</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Password <span class="req">*</span></label>
                            <input type="password" name="password" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Confirm Password <span class="req">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Status</label>
                            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.user-check').forEach(c => c.checked = this.checked);
});
function toggleStatus(userId, checkbox) {
    APP.ajax(`/admin/users/${userId}/toggle-status`, 'POST')
        .done(res => { if (res.success) APP.toast('Status: ' + res.status); else { checkbox.checked = !checkbox.checked; APP.toast('Failed','error'); } });
}
function deleteUser(userId) {
    APP.confirm('Delete User', 'This cannot be undone.', () => {
        APP.ajax(`/admin/users/${userId}`, 'DELETE').done(res => { if (res.success) { APP.toast('User deleted'); location.reload(); } });
    });
}
function executeBulk() {
    const action = document.getElementById('bulkAction').value;
    const ids = [...document.querySelectorAll('.user-check:checked')].map(c => c.value);
    if (!action || !ids.length) { APP.toast('Select an action and users','warning'); return; }
    APP.confirm('Bulk Action', `Apply "${action}" to ${ids.length} user(s)?`, () => {
        APP.ajax('/admin/users/bulk-action', 'POST', { action, ids })
            .done(res => { if (res.success) { APP.toast(res.message); location.reload(); } });
    });
}
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));
    APP.ajax('{{ route("admin.users.store") }}', 'POST', data)
        .done(res => {
            if (res.success) { APP.toast('User created!'); bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide(); setTimeout(() => location.reload(), 1000); }
        })
        .fail(err => APP.toast(Object.values(err.responseJSON?.errors ?? {msg:['Failed']})[0][0], 'error'));
});
</script>
@endpush
