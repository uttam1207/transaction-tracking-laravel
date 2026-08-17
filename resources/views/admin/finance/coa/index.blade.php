@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Chart of Accounts</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Chart of Accounts</li>
            </ol></nav>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAccountModal">
            <i class="bi bi-plus-lg me-1"></i> New Account
        </button>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search code or name…" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach ($types as $t)
                            <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="active" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="1" @selected(request('active') === '1')>Active</option>
                        <option value="0" @selected(request('active') === '0')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-sm btn-outline-primary">Filter</button>
                    <a href="{{ route('admin.finance.coa.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Sub-Type</th>
                            <th>Parent</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                        <tr>
                            <td><span class="fw-mono fw-semibold">{{ $account->code }}</span></td>
                            <td>{{ $account->name }}</td>
                            <td><span class="badge bg-{{ $account->type_color }}-subtle text-{{ $account->type_color }}">{{ ucfirst($account->type) }}</span></td>
                            <td class="text-muted small">{{ $account->sub_type ?? '—' }}</td>
                            <td class="text-muted small">{{ $account->parent?->name ?? '—' }}</td>
                            <td>
                                @if ($account->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" onclick="editAccount({{ $account->id }}, @json($account))">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteAccount({{ $account->id }}, '{{ $account->name }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($accounts->hasPages())
            <div class="px-3 py-2">{{ $accounts->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Add Account Modal --}}
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.finance.coa.store') }}" id="addAccountForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">New Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required maxlength="20" placeholder="e.g. 1001">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="200">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">Select…</option>
                            @foreach ($types as $t)
                                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub-Type</label>
                        <input type="text" name="sub_type" class="form-control" placeholder="e.g. current_asset">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Parent Account</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top Level)</option>
                            @foreach ($parents as $p)
                                <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="allow_direct_posting" value="1" id="allow_direct_posting" checked>
                            <label class="form-check-label" for="allow_direct_posting">Allow Direct Posting</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Account</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editAccountForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" id="edit_code" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="type" id="edit_type" class="form-select" required>
                            @foreach ($types as $t)
                                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub-Type</label>
                        <input type="text" name="sub_type" id="edit_sub_type" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="allow_direct_posting" value="1" id="edit_allow_direct_posting">
                            <label class="form-check-label" for="edit_allow_direct_posting">Allow Direct Posting</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editAccount(id, data) {
    document.getElementById('editAccountForm').action = `/admin/finance/coa/${id}`;
    document.getElementById('edit_code').value = data.code;
    document.getElementById('edit_name').value = data.name;
    document.getElementById('edit_type').value = data.type;
    document.getElementById('edit_sub_type').value = data.sub_type ?? '';
    document.getElementById('edit_description').value = data.description ?? '';
    document.getElementById('edit_is_active').checked = data.is_active;
    document.getElementById('edit_allow_direct_posting').checked = data.allow_direct_posting;
    new bootstrap.Modal(document.getElementById('editAccountModal')).show();
}

function deleteAccount(id, name) {
    if (!confirm(`Delete account "${name}"?`)) return;
    fetch(`/admin/finance/coa/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message);
    });
}

// AJAX form submit for add/edit
['addAccountForm', 'editAccountForm'].forEach(fId => {
    document.getElementById(fId)?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const r = await fetch(this.action, {
            method: this.method === 'get' ? 'POST' : 'POST',
            body: new FormData(this),
            headers: { 'Accept': 'application/json' }
        });
        const d = await r.json();
        if (d.success) location.reload();
        else alert(d.message ?? 'Error');
    });
});
</script>
@endpush
@endsection
