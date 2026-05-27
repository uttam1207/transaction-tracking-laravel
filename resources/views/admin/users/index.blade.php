@extends('layouts.app')
@section('title', 'User Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold">User Management</h5>
        <div class="text-muted small">Manage all system users and their roles</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-person-plus me-1"></i>Add User
    </button>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, username..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    @foreach(['super_admin','admin','manager','employee','auditor','viewer'] as $r)
                        <option value="{{ $r }}" {{ request('role') == $r ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$r)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['active','inactive','pending','suspended'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-search"></i></button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">All Users <span class="badge bg-primary-subtle text-primary">{{ $users->total() }}</span></h6>
        <div class="d-flex gap-2">
            <select id="bulkAction" class="form-select form-select-sm" style="width: auto;">
                <option value="">Bulk Actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" onclick="executeBulk()">Apply</button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><input type="checkbox" id="selectAll"></th>
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
                        <td class="ps-3"><input type="checkbox" value="{{ $user->id }}" class="user-check"></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $user->avatar_url }}" class="rounded-circle" width="36" height="36" alt="">
                                <div>
                                    <div class="small fw-semibold">{{ $user->name }}</div>
                                    <div class="smaller text-muted">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">
                                {{ ucwords(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                        <td><span class="small">{{ $user->department?->name ?? 'N/A' }}</span></td>
                        <td>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" {{ $user->status === 'active' ? 'checked' : '' }}
                                       onchange="toggleStatus({{ $user->id }}, this)"
                                       {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            </div>
                        </td>
                        <td><small class="text-muted">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info py-0 px-2"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="bi bi-pencil"></i></a>
                                @if($user->id !== auth()->id())
                                <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="deleteUser({{ $user->id }})"><i class="bi bi-trash"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">No users found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <span class="small text-muted">Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }}</span>
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Create New User</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createUserForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Full Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Role *</label>
                            <select name="role" class="form-select" required>
                                @foreach(['admin','manager','employee','auditor','viewer'] as $r)
                                    <option value="{{ $r }}">{{ ucwords(str_replace('_',' ',$r)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">-- Select --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Confirm Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select All
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.user-check').forEach(c => c.checked = this.checked);
});

function toggleStatus(userId, checkbox) {
    APP.ajax(`/admin/users/${userId}/toggle-status`, 'POST')
        .done(res => {
            if (res.success) APP.toast('Status updated: ' + res.status);
            else { checkbox.checked = !checkbox.checked; APP.toast('Failed', 'error'); }
        });
}

function deleteUser(userId) {
    APP.confirm('Delete User', 'This action cannot be undone.', () => {
        APP.ajax(`/admin/users/${userId}`, 'DELETE')
            .done(res => { if (res.success) { APP.toast('User deleted'); location.reload(); } });
    });
}

function executeBulk() {
    const action = document.getElementById('bulkAction').value;
    const ids = [...document.querySelectorAll('.user-check:checked')].map(c => c.value);
    if (!action || ids.length === 0) { APP.toast('Select an action and at least one user', 'warning'); return; }

    APP.confirm('Bulk Action', `Apply "${action}" to ${ids.length} user(s)?`, () => {
        APP.ajax('/admin/users/bulk-action', 'POST', { action, ids })
            .done(res => { if (res.success) { APP.toast(res.message); location.reload(); } });
    });
}

// Create User Form
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));
    APP.ajax('{{ route("admin.users.store") }}', 'POST', data)
        .done(res => {
            if (res.success) { APP.toast('User created!'); bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide(); setTimeout(() => location.reload(), 1000); }
        })
        .fail(err => { APP.toast(Object.values(err.responseJSON?.errors ?? { msg: ['Failed'] })[0][0], 'error'); });
});
</script>
@endpush
