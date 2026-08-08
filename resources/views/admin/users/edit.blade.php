@extends('layouts.app')
@section('title', 'Edit User — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

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
            <div class="card-glass p-4 text-center">
                <img src="{{ $user->avatar_url }}" class="rounded-circle mx-auto mb-3"
                    id="avatarPreview"
                    style="width:88px;height:88px;border:3px solid #e0e7ff;object-fit:cover;display:block;" alt="">
                <label class="form-label fw-semibold d-block text-start mb-1">Profile Photo</label>
                <input type="file" name="avatar" id="avatarInput" class="form-control"
                    accept="image/*"
                    style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.83rem;">
                <div style="font-size:.74rem;color:#9ca3af;margin-top:6px;">JPG, PNG or GIF — max 2MB</div>
            </div>

            <div class="card-glass overflow-hidden mt-3">
                <div style="background:linear-gradient(135deg,#374151,#1f2937);padding:14px 20px;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.06);border-radius:50%;pointer-events:none;"></div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge" style="color:rgba(255,255,255,.85);font-size:1rem;"></i>
                        <span style="font-size:.82rem;font-weight:700;color:#fff;">Account Info</span>
                    </div>
                </div>
                <div class="p-3">
                    <dl class="dl mb-0">
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
            <div class="card-glass overflow-hidden mb-3">
                <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:16px 24px;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person" style="color:rgba(255,255,255,.85);font-size:1rem;"></i>
                        <span style="font-size:.9rem;font-weight:700;color:#fff;">Account Details</span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
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
                            <label class="form-label fw-semibold">Department</label>
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
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
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

            <div class="card-glass overflow-hidden mb-3">
                <div style="background:linear-gradient(135deg,#374151,#1f2937);padding:16px 24px;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-lock" style="color:rgba(255,255,255,.85);font-size:1rem;"></i>
                            <span style="font-size:.9rem;font-weight:700;color:#fff;">Change Password</span>
                        </div>
                        <span style="font-size:.72rem;color:rgba(255,255,255,.6);">Leave blank to keep current</span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="Min. 8 characters" autocomplete="new-password"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password</label>
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
