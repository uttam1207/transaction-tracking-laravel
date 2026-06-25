@extends('layouts.app')
@section('title', 'Employees')

@section('breadcrumb')
    <li class="breadcrumb-item active">Employees</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Employee Management</h4>
            <p>Manage employee records, performance and profiles</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat">
                <div class="v">{{ $employees->total() }}</div>
                <div class="l">Total</div>
            </div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat">
                <div class="v" style="color:#86efac;">{{ $employees->where('status','active')->count() }}</div>
                <div class="l">Active</div>
            </div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat">
                <div class="v" style="color:#fde047;">{{ $departments->count() }}</div>
                <div class="l">Depts</div>
            </div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat">
                <div class="v" style="color:#93c5fd;">{{ $employees->where('employment_type','full_time')->count() }}</div>
                <div class="l">Full-Time</div>
            </div>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" style="border-radius:9px;" data-bs-toggle="modal" data-bs-target="#createEmployeeModal">
            <i class="bi bi-plus-lg me-1"></i>Add Employee
        </button>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.employees.index') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <div class="position-relative">
                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.8rem;pointer-events:none;"></i>
                <input type="text" name="search" class="form-control ps-4" placeholder="Search name, email, ID…" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <select name="department" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department')==$dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" @selected(request('status')==='active')>Active</option>
                <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
                <option value="on_leave" @selected(request('status')==='on_leave')>On Leave</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="full_time" @selected(request('type')==='full_time')>Full-Time</option>
                <option value="part_time" @selected(request('type')==='part_time')>Part-Time</option>
                <option value="contract" @selected(request('type')==='contract')>Contract</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-filter btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-filter btn-outline-secondary px-3"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="card-header">
        <span class="card-title">All Employees</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Type</th>
                    <th>Performance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                @php
                    $status = $employee->status ?? 'active';
                    $score  = $employee->performance_score ?? 0;
                    $perf   = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger');
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $employee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($employee->full_name).'&size=40&background=4f46e5&color=fff' }}"
                                class="rounded-circle" width="38" height="38" alt="">
                            <div>
                                <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ $employee->full_name }}</div>
                                <div style="font-size:.73rem;color:#9ca3af;font-family:monospace;">{{ $employee->employee_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.84rem;color:#374151;">{{ $employee->department->name ?? '—' }}</td>
                    <td style="font-size:.84rem;color:#374151;">{{ $employee->designation ?? '—' }}</td>
                    <td>
                        <span style="background:#f3f4f6;color:#374151;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:600;">
                            {{ ucfirst(str_replace('_',' ',$employee->employment_type ?? 'full_time')) }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:5px;border-radius:3px;background:#e5e7eb;overflow:hidden;min-width:70px;">
                                <div style="height:100%;width:{{ $score }}%;background:{{ $perf=='success'?'#22c55e':($perf=='warning'?'#f59e0b':'#ef4444') }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:.75rem;font-weight:700;min-width:30px;color:{{ $perf=='success'?'#16a34a':($perf=='warning'?'#d97706':'#dc2626') }};">{{ $score }}%</span>
                        </div>
                    </td>
                    <td><span class="spill spill-{{ $status }}">{{ ucfirst($status) }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.employees.show', $employee) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.employees.edit', $employee) }}" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="{{ route('admin.employees.performance', $employee) }}" class="act-btn act-green" title="Performance"><i class="bi bi-graph-up"></i></a>
                            <button class="act-btn act-delete" onclick="deleteEmployee({{ $employee->id }})" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-people"></i><p>No employees found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="pagination-wrap">
        <span class="pagination-info">Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ number_format($employees->total()) }}</span>
        {{ $employees->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Add New Employee</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.employees.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">First Name <span class="req">*</span></label>
                            <input type="text" name="first_name" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Last Name <span class="req">*</span></label>
                            <input type="text" name="last_name" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Email <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Phone</label>
                            <input type="text" name="phone" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Department <span class="req">*</span></label>
                            <select name="department_id" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Designation <span class="req">*</span></label>
                            <input type="text" name="designation" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Employment Type</label>
                            <select name="employment_type" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="full_time">Full-Time</option>
                                <option value="part_time">Part-Time</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Salary</label>
                            <input type="number" name="salary" class="form-control" step="0.01" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Work Location</label>
                            <select name="work_location" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="office">Office</option>
                                <option value="remote">Remote</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Password <span class="req">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">
                        <i class="bi bi-check2 me-1"></i>Create Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteEmployee(id) {
    APP.confirm('Delete this employee?', 'This action cannot be undone.', function() {
        fetch(`/admin/employees/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Employee deleted!'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Error', 'error');
        });
    });
}
</script>
@endpush
