@extends('layouts.app')
@section('title', 'Salary Structures')
@section('breadcrumb')
    <li class="breadcrumb-item active">Salary Structures</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Salary Structures</h4>
            <p>Define how salaries are calculated with component-based structures</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#ssModal" onclick="openCreate()">
            <i class="bi bi-plus-lg me-1"></i>New Structure
        </button>
    </div>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">All Salary Structures</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $structures->total() }} structures</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Scope</th>
                    <th>Components</th>
                    <th>Effective From</th>
                    <th>Default</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($structures as $ss)
                <tr>
                    <td style="font-weight:700;font-size:.88rem;color:#111827;">{{ $ss->name }}</td>
                    <td>
                        <span style="background:#f0f4ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:monospace;">{{ $ss->code }}</span>
                    </td>
                    <td style="font-size:.82rem;">
                        @if($ss->employee_id)
                            <span class="badge bg-info bg-opacity-15 text-info" style="font-size:.72rem;border-radius:20px;">Employee</span>
                            <span style="color:#6b7280;font-size:.78rem;">{{ $ss->employee->user->name ?? '' }}</span>
                        @elseif($ss->department_id)
                            <span class="badge bg-warning bg-opacity-15 text-warning" style="font-size:.72rem;border-radius:20px;">Department</span>
                            <span style="color:#6b7280;font-size:.78rem;">{{ $ss->department->name ?? '' }}</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-15 text-secondary" style="font-size:.72rem;border-radius:20px;">Global</span>
                        @endif
                    </td>
                    <td>
                        <span style="background:#ede9fe;color:#7c3aed;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">
                            {{ $ss->items->count() }} components
                        </span>
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $ss->effective_from ? \Carbon\Carbon::parse($ss->effective_from)->format('d M Y') : '—' }}</td>
                    <td>
                        @if($ss->is_default)
                        <span class="badge bg-success bg-opacity-15 text-success" style="font-size:.72rem;border-radius:20px;font-weight:700;">Default</span>
                        @else
                        <span style="color:#9ca3af;font-size:.8rem;">—</span>
                        @endif
                    </td>
                    <td><span class="spill {{ $ss->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $ss->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" title="Edit" onclick="openEditStructure({{ $ss->id }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" title="Delete" onclick="deleteSS({{ $ss->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8">
                    <div class="empty-state"><i class="bi bi-diagram-3"></i><p>No salary structures defined yet</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($structures->hasPages())
    <div class="pagination-wrap">{{ $structures->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="ssModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="ssModalTitle">New Salary Structure</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="ssForm" method="POST">
                @csrf
                <span id="ssMethod"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="flabel">Structure Name <span class="req">*</span></label>
                            <input type="text" name="name" id="ssName" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Code <span class="req">*</span></label>
                            <input type="text" name="code" id="ssCode" class="form-control" placeholder="STD-001" required style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Effective From</label>
                            <input type="date" name="effective_from" id="ssEffFrom" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Scope</label>
                            <select name="scope" id="ssScope" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;" onchange="toggleScopeField()">
                                <option value="global">Global Default</option>
                                <option value="department">By Department</option>
                                <option value="employee">By Employee</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="scopeDeptWrap" style="display:none;">
                            <label class="flabel">Department</label>
                            <select name="department_id" id="ssDept" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">— Select —</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4" id="scopeEmpWrap" style="display:none;">
                            <label class="flabel">Employee</label>
                            <select name="employee_id" id="ssEmp" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">— Select —</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name ?? '' }} ({{ $emp->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Mark as Default</label>
                            <select name="is_default" id="ssDefault" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="0">No</option>
                                <option value="1">Yes (Global Fallback)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status</label>
                            <select name="is_active" id="ssStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        {{-- Component Rows --}}
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="flabel mb-0">Salary Components</label>
                                <button type="button" class="btn btn-sm btn-outline-primary px-3" onclick="addComponentRow()">
                                    <i class="bi bi-plus-lg me-1"></i>Add Row
                                </button>
                            </div>
                            <div id="componentRows" style="border:1.5px solid #e5e7eb;border-radius:9px;overflow:hidden;">
                                <table class="table mb-0" style="font-size:.84rem;">
                                    <thead style="background:#f9fafb;">
                                        <tr>
                                            <th style="width:55%;">Component</th>
                                            <th style="width:35%;">Value (₹ or %)</th>
                                            <th style="width:10%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="compRowBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="ssSubmitBtn">Create Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Structures JSON for edit --}}
<script>
const allComponents = @json($components);
const structures    = @json($structures->items());
</script>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;
let rowIndex = 0;

function toggleScopeField() {
    const scope = document.getElementById('ssScope').value;
    document.getElementById('scopeDeptWrap').style.display = scope === 'department' ? '' : 'none';
    document.getElementById('scopeEmpWrap').style.display  = scope === 'employee'   ? '' : 'none';
}

function addComponentRow(compId = '', value = '') {
    const options = allComponents.map(c =>
        `<option value="${c.id}" ${compId == c.id ? 'selected' : ''}>${c.name} (${c.code})</option>`
    ).join('');
    const row = `
    <tr id="compRow_${rowIndex}">
        <td>
            <select name="components[${rowIndex}][salary_component_id]" class="form-select form-select-sm" required style="border-radius:7px;">
                <option value="">— Select Component —</option>
                ${options}
            </select>
        </td>
        <td>
            <input type="number" name="components[${rowIndex}][value]" class="form-control form-control-sm" min="0" step="0.01" value="${value}" required style="border-radius:7px;">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="removeRow(${rowIndex})">
                <i class="bi bi-x-lg"></i>
            </button>
        </td>
    </tr>`;
    document.getElementById('compRowBody').insertAdjacentHTML('beforeend', row);
    rowIndex++;
}

function removeRow(idx) {
    const row = document.getElementById(`compRow_${idx}`);
    if (row) row.remove();
}

function openCreate() {
    document.getElementById('ssModalTitle').textContent = 'New Salary Structure';
    document.getElementById('ssForm').action = '{{ route("admin.salary-structures.store") }}';
    document.getElementById('ssMethod').innerHTML = '';
    document.getElementById('ssSubmitBtn').textContent = 'Create Structure';
    ['ssName','ssCode'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('ssEffFrom').value = '';
    document.getElementById('ssScope').value    = 'global';
    document.getElementById('ssDefault').value  = '0';
    document.getElementById('ssStatus').value   = '1';
    document.getElementById('compRowBody').innerHTML = '';
    rowIndex = 0;
    toggleScopeField();
    addComponentRow();
}

function openEditStructure(id) {
    const ss = structures.find(s => s.id === id);
    if (!ss) return;
    document.getElementById('ssModalTitle').textContent = 'Edit Salary Structure';
    document.getElementById('ssForm').action = `/admin/salary-structures/${id}`;
    document.getElementById('ssMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('ssSubmitBtn').textContent = 'Save Changes';
    document.getElementById('ssName').value    = ss.name;
    document.getElementById('ssCode').value    = ss.code;
    document.getElementById('ssEffFrom').value = ss.effective_from || '';
    document.getElementById('ssDefault').value = ss.is_default ? '1' : '0';
    document.getElementById('ssStatus').value  = ss.is_active ? '1' : '0';

    let scope = 'global';
    if (ss.employee_id) scope = 'employee';
    else if (ss.department_id) scope = 'department';
    document.getElementById('ssScope').value = scope;
    toggleScopeField();
    if (ss.department_id) document.getElementById('ssDept').value = ss.department_id;
    if (ss.employee_id)   document.getElementById('ssEmp').value  = ss.employee_id;

    document.getElementById('compRowBody').innerHTML = '';
    rowIndex = 0;
    (ss.items || []).forEach(item => addComponentRow(item.salary_component_id, item.value));
    if (!ss.items || ss.items.length === 0) addComponentRow();

    new bootstrap.Modal(document.getElementById('ssModal')).show();
}

function deleteSS(id) {
    APP.confirm('Delete salary structure?', 'This action cannot be undone.', function() {
        fetch(`/admin/salary-structures/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Structure deleted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Cannot delete.', 'error');
        });
    });
}
</script>
@endpush
