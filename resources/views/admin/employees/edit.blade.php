@extends('layouts.app')
@section('title', 'Edit Employee')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.employees.show', $employee) }}">{{ $employee->full_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Employee</h4>
            <p>{{ $employee->full_name }} &bull; {{ $employee->employee_id }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<div class="row g-4">

    {{-- Left Column --}}
    <div class="col-lg-8">

        <div class="card-glass p-4 mb-4">
            <h6 class="form-section-label">A — Personal Information</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                        value="{{ old('first_name', $employee->user->name ? explode(' ',$employee->user->name)[0] : '') }}"
                        required>
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Last Name</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                        value="{{ old('last_name', $employee->user->name ? (explode(' ',$employee->user->name)[1] ?? '') : '') }}">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $employee->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="text" name="phone" class="form-control"
                        value="{{ old('phone', $employee->user->phone) }}">
                </div>
            </div>
        </div>

        <div class="card-glass p-4">
            <h6 class="form-section-label">B — Employment Details</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(old('department_id',$employee->department_id)==$dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Designation</label>
                    <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                        value="{{ old('designation', $employee->designation) }}">
                    @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Employment Type</label>
                    <select name="employment_type" class="form-select">
                        @foreach(['full_time'=>'Full-Time','part_time'=>'Part-Time','contract'=>'Contract','intern'=>'Intern'] as $v=>$l)
                            <option value="{{ $v }}" @selected(old('employment_type',$employee->employment_type)===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Work Location</label>
                    <select name="work_location" class="form-select">
                        @foreach(['office'=>'Office','remote'=>'Remote','hybrid'=>'Hybrid'] as $v=>$l)
                            <option value="{{ $v }}" @selected(old('work_location',$employee->work_location)===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active'=>'Active','inactive'=>'Inactive','on_leave'=>'On Leave','terminated'=>'Terminated'] as $v=>$l)
                            <option value="{{ $v }}" @selected(old('status',$employee->status)===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Salary (&#8377;)</label>
                    <div class="input-group">
                        <span class="input-group-text">&#8377;</span>
                        <input type="number" name="salary" class="form-control" step="0.01"
                            value="{{ old('salary', $employee->salary) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Annual Leave Balance</label>
                    <input type="number" name="annual_leave_balance" class="form-control"
                        value="{{ old('annual_leave_balance', $employee->annual_leave_balance ?? 21) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Sick Leave Balance</label>
                    <input type="number" name="sick_leave_balance" class="form-control"
                        value="{{ old('sick_leave_balance', $employee->sick_leave_balance ?? 10) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Performance Score <span class="text-muted fw-normal">(0–100)</span></label>
                    <input type="number" name="performance_score" class="form-control" min="0" max="100"
                        value="{{ old('performance_score', $employee->performance_score ?? 0) }}">
                </div>
            </div>
        </div>

    </div>

    {{-- Right Sidebar --}}
    <div class="col-lg-4">

        {{-- Profile Photo --}}
        <div class="card-glass p-4 mb-4 text-center">
            <h6 class="form-section-label text-start">Profile Photo</h6>
            <div style="position:relative;display:inline-block;margin-bottom:12px;">
                <img id="avatarPreview"
                    src="{{ $employee->user->avatar_url }}"
                    style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #e5e7eb;box-shadow:0 2px 8px rgba(0,0,0,.10);">
                <label for="avatarInput"
                    style="position:absolute;bottom:2px;right:2px;width:28px;height:28px;border-radius:50%;background:#4f46e5;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 6px rgba(79,70,229,.4);"
                    title="Change photo">
                    <i class="bi bi-camera-fill" style="font-size:.75rem;"></i>
                </label>
            </div>
            <input type="file" id="avatarInput" name="avatar" accept="image/jpg,image/jpeg,image/png,image/gif,image/webp" style="display:none;">
            <div class="text-muted" style="font-size:.73rem;">JPG, PNG, GIF or WebP · Max 2MB</div>
            <div id="avatarFileName" style="font-size:.75rem;color:#4f46e5;margin-top:4px;display:none;"></div>
        </div>

        {{-- Security --}}
        <div class="card-glass p-4 mb-4">
            <h6 class="form-section-label">Security</h6>
            <label class="form-label fw-semibold">New Password</label>
            <input type="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current">
            <p class="text-muted mt-2 mb-0" style="font-size:.75rem;">Leave blank to keep the current password.</p>
        </div>

        {{-- Actions --}}
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

@push('scripts')
<script>
document.getElementById('avatarInput').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        APP.toast('Image must be smaller than 2MB.', 'error');
        this.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);

    const nameEl = document.getElementById('avatarFileName');
    nameEl.textContent = file.name;
    nameEl.style.display = 'block';
});
</script>
@endpush
