@extends('layouts.app')

@section('title', 'Department Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Department Management</h4>
        <p class="text-muted mb-0">Manage organizational departments</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deptModal" onclick="openCreate()">
        <i class="bi bi-plus-circle me-1"></i>Add Department
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Department</th>
                        <th>Code</th>
                        <th>Manager</th>
                        <th>Employees</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                    <tr>
                        <td class="fw-semibold">{{ $dept->name }}</td>
                        <td><span class="badge bg-light text-dark">{{ $dept->code ?? '—' }}</span></td>
                        <td>{{ $dept->manager->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-primary rounded-pill">{{ $dept->employees_count }}</span>
                        </td>
                        <td class="text-muted small">{{ Str::limit($dept->description, 50) ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $dept->is_active ? 'success' : 'secondary' }}">
                                {{ $dept->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary"
                                    onclick="openEdit({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ $dept->code }}', '{{ addslashes($dept->description) }}', {{ $dept->manager_id ?? 'null' }}, {{ $dept->is_active ? 1 : 0 }})"
                                    title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger"
                                    onclick="deleteDept({{ $dept->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-building fs-1 d-block mb-2"></i>
                            No departments found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($departments->hasPages())
    <div class="card-footer bg-transparent">{{ $departments->links() }}</div>
    @endif
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="deptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deptModalTitle">Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deptForm" method="POST">
                @csrf
                <span id="methodField"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="deptName" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" id="deptCode" class="form-control" placeholder="e.g. ENG">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="deptDesc" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Manager</label>
                            <select name="manager_id" id="deptManager" class="form-select">
                                <option value="">No Manager</option>
                                @foreach(\App\Models\User::active()->orderBy('name')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="deptStatus" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="deptSubmitBtn">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

function openCreate() {
    document.getElementById('deptModalTitle').textContent = 'Add Department';
    document.getElementById('deptForm').action = '{{ route("admin.departments.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('deptSubmitBtn').textContent = 'Create';
    ['deptName','deptCode','deptDesc'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('deptManager').value = '';
    document.getElementById('deptStatus').value = '1';
}

function openEdit(id, name, code, desc, managerId, isActive) {
    document.getElementById('deptModalTitle').textContent = 'Edit Department';
    document.getElementById('deptForm').action = `/admin/departments/${id}`;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('deptSubmitBtn').textContent = 'Save Changes';
    document.getElementById('deptName').value = name;
    document.getElementById('deptCode').value = code || '';
    document.getElementById('deptDesc').value = desc || '';
    document.getElementById('deptManager').value = managerId || '';
    document.getElementById('deptStatus').value = isActive;
    new bootstrap.Modal(document.getElementById('deptModal')).show();
}

function deleteDept(id) {
    APP.confirm('Delete this department?', function() {
        fetch(`/admin/departments/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                APP.toast('Department deleted.', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                APP.toast(data.message, 'danger');
            }
        });
    });
}
</script>
@endpush
