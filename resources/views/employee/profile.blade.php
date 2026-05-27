@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">My Profile</h4>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=100&background=6366f1&color=fff' }}"
                class="rounded-circle mx-auto mb-3" width="100" height="100">
            <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
            <p class="text-muted mb-1">{{ $user->email }}</p>
            @if($user->employee)
            <p class="text-muted small">{{ $user->employee->designation ?? 'Employee' }}</p>
            <p class="text-muted small">{{ $user->employee->department->name ?? '' }}</p>
            @endif
            <span class="badge bg-primary px-3">{{ ucfirst($user->getRoleNames()->first() ?? 'employee') }}</span>

            @if($user->employee)
            <hr>
            <div class="text-start">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Employee ID</span>
                    <strong>{{ $user->employee->employee_id }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Annual Leave</span>
                    <strong>{{ $user->employee->annual_leave_balance ?? 0 }} days</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Sick Leave</span>
                    <strong>{{ $user->employee->sick_leave_balance ?? 0 }} days</strong>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Update Profile</div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('employee.profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Profile Avatar</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Change Password</div>
            <div class="card-body">
                <form action="{{ route('employee.profile') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-lock me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
