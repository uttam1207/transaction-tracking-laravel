@extends('layouts.app')
@section('title', 'Edit User — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold">Edit User</h5>
        <div class="text-muted small">Update profile and access settings for {{ $user->name }}</div>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Users
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle mb-3 border" width="100" height="100" id="avatarPreview" alt="">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-block">Profile Photo</label>
                        <input type="file" name="avatar" id="avatarInput" class="form-control form-control-sm" accept="image/*">
                    </div>
                    <div class="text-muted small">JPG, PNG or GIF — max 2MB</div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex flex-column gap-1 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Username</span>
                            <span class="fw-semibold">{{ $user->username }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Joined</span>
                            <span>{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Last Login</span>
                            <span>{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-semibold">Account Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Full Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Role *</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                @foreach(['super_admin','admin','manager','employee','auditor','viewer'] as $r)
                                    <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $r)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Department</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">-- None --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" {{ old('department_id', $user->department_id) == $d->id ? 'selected' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status *</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(['active','inactive','pending','suspended'] as $s)
                                    <option value="{{ $s }}" {{ old('status', $user->status) === $s ? 'selected' : '' }}>
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card mt-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Change Password</h6>
                    <small class="text-muted">Leave blank to keep current password</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 characters" autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                   placeholder="Repeat new password" autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Avatar preview
document.getElementById('avatarInput')?.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
