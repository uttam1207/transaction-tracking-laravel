@extends('layouts.app')
@section('title', 'My Profile')

@section('content')

<div class="page-hero">
    <div style="position:relative;z-index:1;">
        <h4>My Profile</h4>
        <p>Manage your personal information and security settings</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:10px;border:none;background:#dcfce7;color:#166534;">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    {{-- Left: Profile Card --}}
    <div class="col-lg-4">
        <div class="info-card text-center" style="padding:28px 20px;">
            <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=100&background=6366f1&color=fff' }}"
                class="rounded-circle mx-auto mb-3"
                style="width:96px;height:96px;border:3px solid #e0e7ff;object-fit:cover;display:block;"
                alt="{{ $user->name }}">
            <div style="font-size:1.1rem;font-weight:800;color:#111827;">{{ $user->name }}</div>
            <div style="font-size:.83rem;color:#6b7280;margin-top:4px;">{{ $user->email }}</div>
            @if($user->employee)
            <div style="font-size:.8rem;color:#9ca3af;margin-top:2px;">{{ $user->employee->designation ?? 'Employee' }}</div>
            <div style="font-size:.8rem;color:#9ca3af;">{{ $user->employee->department->name ?? '' }}</div>
            @endif
            <span style="display:inline-block;background:#ede9fe;color:#7c3aed;padding:4px 14px;border-radius:20px;font-size:.75rem;font-weight:700;margin-top:10px;">
                {{ ucfirst($user->getRoleNames()->first() ?? 'employee') }}
            </span>

            @if($user->employee)
            <div style="border-top:1px solid #f3f4f6;margin-top:20px;padding-top:16px;">
                <dl class="dl" style="text-align:left;">
                    <dt>Employee ID</dt>
                    <dd style="font-family:monospace;font-weight:700;color:#4f46e5;">{{ $user->employee->employee_id }}</dd>
                    <dt>Annual Leave</dt>
                    <dd><span style="color:#16a34a;font-weight:700;">{{ $user->employee->annual_leave_balance ?? 0 }}</span> days</dd>
                    <dt>Sick Leave</dt>
                    <dd><span style="color:#0ea5e9;font-weight:700;">{{ $user->employee->sick_leave_balance ?? 0 }}</span> days</dd>
                </dl>
            </div>
            @endif
        </div>
    </div>

    {{-- Right: Forms --}}
    <div class="col-lg-8">
        {{-- Update Profile --}}
        <div class="form-section mb-4">
            <div class="form-section-hdr"><i class="bi bi-person me-2"></i>Update Profile</div>
            <div class="form-section-body">
                <form action="{{ route('employee.profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Full Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Phone</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="flabel">Profile Avatar</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">JPG, PNG up to 2MB</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-sm btn-primary-grad px-4">
                            <i class="bi bi-save me-1"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="form-section">
            <div class="form-section-hdr"><i class="bi bi-lock me-2"></i>Change Password</div>
            <div class="form-section-body">
                <form action="{{ route('employee.profile') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Current Password <span class="req">*</span></label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror"
                                required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">New Password <span class="req">*</span></label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror"
                                required minlength="8" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Confirm New Password <span class="req">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control"
                                required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-sm px-4"
                            style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;border-radius:9px;font-weight:600;padding:8px 20px;">
                            <i class="bi bi-lock me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
