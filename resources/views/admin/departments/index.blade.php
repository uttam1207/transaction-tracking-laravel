@extends('layouts.app')
@section('title', 'Departments')

@section('breadcrumb')
    <li class="breadcrumb-item active">Departments</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Departments</h4>
            <p>Manage organisational structure and department heads</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" style="border-radius:9px;"
                data-bs-toggle="modal" data-bs-target="#deptModal" onclick="openCreate()">
            <i class="bi bi-plus-lg me-1"></i>Add Department
        </button>
    </div>
</div>

<div class="table-card">
    <div class="card-header">
        <span class="card-title">All Departments</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Code</th>
                    <th>Manager</th>
                    <th>Employees</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                <tr>
                    <td style="font-weight:700;font-size:.88rem;color:#111827;">{{ $dept->name }}</td>
                    <td>
                        @if($dept->code)
                        <span style="background:#f0f4ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:monospace;">{{ $dept->code }}</span>
                        @else <span style="color:#9ca3af;">—</span>@endif
                    </td>
                    <td style="font-size:.84rem;color:#374151;">{{ $dept->manager->name ?? '—' }}</td>
                    <td>
                        <span style="background:#ede9fe;color:#7c3aed;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">{{ $dept->employees_count }}</span>
                    </td>
                    <td style="font-size:.82rem;color:#6b7280;max-width:220px;">{{ Str::limit($dept->description,60) ?? '—' }}</td>
                    <td><span class="spill {{ $dept->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $dept->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" title="Edit"
                                onclick="openEdit({{ $dept->id }}, '{{ addslashes($dept->name) }}', '{{ $dept->code }}', '{{ addslashes($dept->description) }}', {{ $dept->manager_id ?? 'null' }}, {{ $dept->is_active ? 1 : 0 }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" title="Delete" onclick="deleteDept({{ $dept->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-building"></i><p>No departments found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($departments->hasPages())
    <div class="pagination-wrap">{{ $departments->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="deptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="deptModalTitle">Add Department</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deptForm" method="POST">
                @csrf
                <span id="methodField"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="flabel">Department Name <span class="req">*</span></label>
                            <input type="text" name="name" id="deptName" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Code</label>
                            <input type="text" name="code" id="deptCode" class="form-control" placeholder="ENG" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" id="deptDesc" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="flabel">Manager</label>
                            <select name="manager_id" id="deptManager" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">No Manager</option>
                                @foreach(\App\Models\User::active()->orderBy('name')->get() as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status</label>
                            <select name="is_active" id="deptStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="deptSubmitBtn">Create</button>
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
    document.getElementById('deptName').value  = name;
    document.getElementById('deptCode').value  = code || '';
    document.getElementById('deptDesc').value  = desc || '';
    document.getElementById('deptManager').value = managerId || '';
    document.getElementById('deptStatus').value  = isActive;
    new bootstrap.Modal(document.getElementById('deptModal')).show();
}
function deleteDept(id) {
    APP.confirm('Delete department?', 'This action cannot be undone.', function() {
        fetch(`/admin/departments/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Department deleted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message, 'error');
        });
    });
}
</script>
@endpush
