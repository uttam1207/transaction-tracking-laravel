@extends('layouts.app')

@section('title', 'Employee Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Employee Management</h4>
        <p class="text-muted mb-0">Manage employee records and profiles</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEmployeeModal">
        <i class="bi bi-plus-circle me-1"></i> Add Employee
    </button>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-primary">{{ $employees->total() }}</div>
            <div class="text-muted">Total Employees</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-success">{{ $employees->where('status', 'active')->count() }}</div>
            <div class="text-muted">Active</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-warning">{{ $departments->count() }}</div>
            <div class="text-muted">Departments</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-info">
                {{ $employees->where('employment_type', 'full_time')->count() }}
            </div>
            <div class="text-muted">Full-Time</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search name, email, ID..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    <option value="on_leave" @selected(request('status') === 'on_leave')>On Leave</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="full_time" @selected(request('type') === 'full_time')>Full-Time</option>
                    <option value="part_time" @selected(request('type') === 'part_time')>Part-Time</option>
                    <option value="contract" @selected(request('type') === 'contract')>Contract</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Employee Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Type</th>
                        <th>Performance</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $employee->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($employee->full_name).'&size=40&background=6366f1&color=fff' }}"
                                    class="rounded-circle" width="40" height="40"
                                    alt="{{ $employee->full_name }}">
                                <div>
                                    <div class="fw-semibold">{{ $employee->full_name }}</div>
                                    <small class="text-muted">{{ $employee->employee_id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $employee->department->name ?? '—' }}</td>
                        <td>{{ $employee->designation ?? '—' }}</td>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ ucfirst(str_replace('_', ' ', $employee->employment_type ?? 'full_time')) }}
                            </span>
                        </td>
                        <td>
                            @php $score = $employee->performance_score ?? 0; @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-fill" style="height: 6px; min-width: 80px;">
                                    <div class="progress-bar bg-{{ $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger') }}"
                                        style="width: {{ $score }}%"></div>
                                </div>
                                <small>{{ $score }}%</small>
                            </div>
                        </td>
                        <td>
                            @php
                                $status = $employee->status ?? 'active';
                                $badgeClass = $status === 'active' ? 'success' : ($status === 'on_leave' ? 'warning' : 'danger');
                            @endphp
                            <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.employees.show', $employee) }}"
                                    class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.employees.edit', $employee) }}"
                                    class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('admin.employees.performance', $employee) }}"
                                    class="btn btn-outline-success" title="Performance">
                                    <i class="bi bi-graph-up"></i>
                                </a>
                                <button class="btn btn-outline-danger"
                                    onclick="deleteEmployee({{ $employee->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                            No employees found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($employees->hasPages())
    <div class="card-footer bg-transparent">
        {{ $employees->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Create Employee Modal --}}
<div class="modal fade" id="createEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.employees.store') }}" method="POST" id="createEmployeeForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employment Type</label>
                            <select name="employment_type" class="form-select">
                                <option value="full_time">Full-Time</option>
                                <option value="part_time">Part-Time</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Salary</label>
                            <input type="number" name="salary" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Work Location</label>
                            <select name="work_location" class="form-select">
                                <option value="office">Office</option>
                                <option value="remote">Remote</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
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
    APP.confirm('Delete this employee record?', function() {
        fetch(`/admin/employees/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                APP.toast('Employee deleted!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                APP.toast(data.message || 'Error', 'danger');
            }
        });
    });
}
</script>
@endpush
