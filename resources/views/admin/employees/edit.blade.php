@extends('layouts.app')
@section('title', 'Edit Employee')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.show', $employee) }}">{{ $employee->full_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<a href="{{ route('admin.employees.show', $employee) }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Profile</a>

<div class="page-hero" style="margin-bottom:24px;">
    <div style="position:relative;z-index:1;">
        <h4>Edit Employee</h4>
        <p>{{ $employee->full_name }} &bull; {{ $employee->employee_id }}</p>
    </div>
</div>

<form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="row g-4">
    <div class="col-lg-8">

        <div class="form-section">
            <div class="form-section-hdr"><i class="bi bi-person"></i>Personal Information</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="flabel">First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                            value="{{ old('first_name', $employee->user->name ? explode(' ',$employee->user->name)[0] : '') }}"
                            required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="flabel">Last Name</label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                            value="{{ old('last_name', $employee->user->name ? (explode(' ',$employee->user->name)[1] ?? '') : '') }}"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="flabel">Email <span class="req">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $employee->email) }}" required
                            style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="flabel">Phone</label>
                        <input type="text" name="phone" class="form-control"
                            value="{{ old('phone', $employee->user->phone) }}"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-hdr"><i class="bi bi-briefcase"></i>Employment Details</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="flabel">Department <span class="req">*</span></label>
                        <select name="department_id" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id',$employee->department_id)==$dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="flabel">Designation</label>
                        <input type="text" name="designation" class="form-control"
                            value="{{ old('designation', $employee->designation) }}"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                    <div class="col-md-4">
                        <label class="flabel">Employment Type</label>
                        <select name="employment_type" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @foreach(['full_time'=>'Full-Time','part_time'=>'Part-Time','contract'=>'Contract','intern'=>'Intern'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old('employment_type',$employee->employment_type)===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="flabel">Work Location</label>
                        <select name="work_location" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @foreach(['office'=>'Office','remote'=>'Remote','hybrid'=>'Hybrid'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old('work_location',$employee->work_location)===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="flabel">Status</label>
                        <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @foreach(['active'=>'Active','inactive'=>'Inactive','on_leave'=>'On Leave','terminated'=>'Terminated'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old('status',$employee->status)===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="flabel">Salary</label>
                        <input type="number" name="salary" class="form-control" step="0.01"
                            value="{{ old('salary', $employee->salary) }}"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                    <div class="col-md-4">
                        <label class="flabel">Annual Leave Balance</label>
                        <input type="number" name="annual_leave_balance" class="form-control"
                            value="{{ old('annual_leave_balance', $employee->annual_leave_balance ?? 21) }}"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                    <div class="col-md-4">
                        <label class="flabel">Sick Leave Balance</label>
                        <input type="number" name="sick_leave_balance" class="form-control"
                            value="{{ old('sick_leave_balance', $employee->sick_leave_balance ?? 10) }}"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                    <div class="col-md-4">
                        <label class="flabel">Performance Score <span style="color:#9ca3af;font-weight:400;">(0-100)</span></label>
                        <input type="number" name="performance_score" class="form-control" min="0" max="100"
                            value="{{ old('performance_score', $employee->performance_score ?? 0) }}"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-lg-4">
        <div class="form-section">
            <div class="form-section-hdr"><i class="bi bi-lock"></i>Security</div>
            <div class="form-section-body">
                <label class="flabel">New Password</label>
                <input type="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current"
                    style="border-radius:9px;border:1.5px solid #e5e7eb;">
                <p style="font-size:.75rem;color:#9ca3af;margin-top:6px;margin-bottom:0;">Leave blank to keep the current password.</p>
            </div>
        </div>

        <div class="d-flex flex-column gap-2">
            <button type="submit" class="btn btn-primary-grad" style="height:42px;border-radius:10px;font-weight:700;">
                <i class="bi bi-check2 me-2"></i>Save Changes
            </button>
            <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-outline-secondary" style="height:42px;border-radius:10px;font-weight:600;display:flex;align-items:center;justify-content:center;">
                Cancel
            </a>
        </div>
    </div>
</div>
</form>
@endsection
