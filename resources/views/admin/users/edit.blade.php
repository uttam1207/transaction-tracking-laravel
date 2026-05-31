@extends('layouts.app')
@section('title', 'Edit User — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<a href="{{ route('admin.users.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Users</a>

<div class="page-hero">
    <div style="position:relative;z-index:1;">
        <h4>Edit User</h4>
        <p>Update profile and access settings for {{ $user->name }}</p>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" style="border-radius:10px;border:none;background:#fef2f2;color:#991b1b;">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
            <li style="font-size:.85rem;">{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Left: Avatar & Meta --}}
        <div class="col-lg-4">
            <div class="info-card text-center" style="padding:24px 20px;">
                <img src="{{ $user->avatar_url }}" class="rounded-circle mx-auto mb-3"
                    id="avatarPreview"
                    style="width:88px;height:88px;border:3px solid #e0e7ff;object-fit:cover;display:block;" alt="">
                <label class="flabel" style="display:block;text-align:left;margin-bottom:6px;">Profile Photo</label>
                <input type="file" name="avatar" id="avatarInput" class="form-control"
                    accept="image/*"
                    style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.83rem;">
                <div style="font-size:.74rem;color:#9ca3af;margin-top:6px;">JPG, PNG or GIF — max 2MB</div>
            </div>

            <div class="info-card mt-3">
                <div class="info-card-hdr"><i class="bi bi-person-badge me-2"></i>Account Info</div>
                <div class="info-card-body">
                    <dl class="dl">
                        <dt>Username</dt>
                        <dd style="font-family:monospace;font-weight:700;color:#4f46e5;">{{ $user->username }}</dd>
                        <dt>Joined</dt>
                        <dd>{{ $user->created_at->format('d M Y') }}</dd>
                        <dt>Last Login</dt>
                        <dd>{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Right: Form --}}
        <div class="col-lg-8">
            <div class="form-section mb-3">
                <div class="form-section-hdr"><i class="bi bi-person me-2"></i>Account Details</div>
                <div class="form-section-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Full Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Email Address <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Phone</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Role <span class="req">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach(\App\Models\Role::where('is_active', true)->orderBy('sort_order')->get() as $r)
                                    <option value="{{ $r->name }}" {{ old('role', $user->role) === $r->name ? 'selected' : '' }}>
                                        {{ $r->display_name ?: ucwords(str_replace('_', ' ', $r->name)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Department</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">— None —</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" {{ old('department_id', $user->department_id) == $d->id ? 'selected' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Status <span class="req">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
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

            <div class="form-section mb-3">
                <div class="form-section-hdr d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-lock me-2"></i>Change Password</span>
                    <span style="font-size:.75rem;color:#9ca3af;font-weight:400;">Leave blank to keep current</span>
                </div>
                <div class="form-section-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="Min. 8 characters" autocomplete="new-password"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control"
                                placeholder="Repeat new password" autocomplete="new-password"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary-grad px-4">
                    <i class="bi bi-check-lg me-1"></i>Save Changes
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary px-4" style="border-radius:9px;">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
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
