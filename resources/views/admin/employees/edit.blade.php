@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.employees.show', $employee) }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Profile
        </a>
        <h4 class="mb-0 fw-bold mt-1">Edit Employee</h4>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                        value="{{ old('first_name', $employee->user->name ? explode(' ', $employee->user->name)[0] : '') }}" required>
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                        value="{{ old('last_name', $employee->user->name ? (explode(' ', $employee->user->name)[1] ?? '') : '') }}">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $employee->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control"
                        value="{{ old('phone', $employee->user->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id', $employee->department_id) == $dept->id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control"
                        value="{{ old('designation', $employee->designation) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Employment Type</label>
                    <select name="employment_type" class="form-select">
                        @foreach(['full_time' => 'Full-Time', 'part_time' => 'Part-Time', 'contract' => 'Contract', 'intern' => 'Intern'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('employment_type', $employee->employment_type) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Work Location</label>
                    <select name="work_location" class="form-select">
                        @foreach(['office' => 'Office', 'remote' => 'Remote', 'hybrid' => 'Hybrid'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('work_location', $employee->work_location) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Salary</label>
                    <input type="number" name="salary" class="form-control" step="0.01"
                        value="{{ old('salary', $employee->salary) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Annual Leave Balance</label>
                    <input type="number" name="annual_leave_balance" class="form-control"
                        value="{{ old('annual_leave_balance', $employee->annual_leave_balance ?? 21) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sick Leave Balance</label>
                    <input type="number" name="sick_leave_balance" class="form-control"
                        value="{{ old('sick_leave_balance', $employee->sick_leave_balance ?? 10) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On Leave'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', $employee->status) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Performance Score (0-100)</label>
                    <input type="number" name="performance_score" class="form-control" min="0" max="100"
                        value="{{ old('performance_score', $employee->performance_score ?? 0) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                    <input type="password" name="password" class="form-control" minlength="8">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2 me-1"></i>Save Changes
                </button>
                <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
