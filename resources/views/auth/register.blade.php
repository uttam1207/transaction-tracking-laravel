@extends('layouts.auth')
@section('title', 'Create account')
@section('subtitle', 'Join AS Dairy Dashboard — it only takes a minute')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    {{-- Full Name --}}
    <div class="auth-field">
        <label for="name">Full Name</label>
        <div class="auth-input-wrap">
            <i class="bi bi-person input-icon"></i>
            <input type="text" name="name" id="name"
                   value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="John Doe" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Email --}}
    <div class="auth-field">
        <label for="email">Email Address</label>
        <div class="auth-input-wrap">
            <i class="bi bi-envelope input-icon"></i>
            <input type="email" name="email" id="email"
                   value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@company.com" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Phone --}}
    <div class="auth-field">
        <label for="phone">
            Phone
            <span class="fw-normal ms-1" style="color:#9ca3af;">(optional)</span>
        </label>
        <div class="auth-input-wrap">
            <i class="bi bi-phone input-icon"></i>
            <input type="tel" name="phone" id="phone"
                   value="{{ old('phone') }}"
                   class="form-control"
                   placeholder="+1 234 567 8900">
        </div>
    </div>

    {{-- Password --}}
    <div class="auth-field">
        <label for="password">Password</label>
        <div class="auth-input-wrap">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" name="password" id="password"
                   class="form-control pe-5 @error('password') is-invalid @enderror"
                   placeholder="Min. 8 chars with upper, lower & number" required>
            <button type="button" class="password-toggle" data-target="password">
                <i class="bi bi-eye"></i>
            </button>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Password strength bar --}}
        <div class="mt-2">
            <div style="height:4px; border-radius:4px; background:#e5e7eb; overflow:hidden;">
                <div id="strengthBar" style="height:100%; width:0%; border-radius:4px; transition:width .3s, background .3s;"></div>
            </div>
            <div id="strengthText" class="mt-1" style="font-size:.72rem; color:#9ca3af;"></div>
        </div>
    </div>

    {{-- Confirm Password --}}
    <div class="auth-field">
        <label for="password_confirmation">Confirm Password</label>
        <div class="auth-input-wrap">
            <i class="bi bi-lock-fill input-icon"></i>
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="form-control pe-5"
                   placeholder="Repeat your password" required>
            <button type="button" class="password-toggle" data-target="password_confirmation">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-auth mt-2">
        <i class="bi bi-person-plus me-2"></i>Create Account
    </button>
</form>

<div class="text-center mt-4" style="font-size:.875rem; color:#6b7280;">
    Already have an account?
    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none ms-1" style="color:#4f46e5;">
        Sign in
    </a>
</div>

@push('scripts')
<script>
document.getElementById('password')?.addEventListener('input', function () {
    const val = this.value;
    const bar = document.getElementById('strengthBar');
    const txt = document.getElementById('strengthText');
    let score = 0;
    if (val.length >= 8)             score++;
    if (/[A-Z]/.test(val))           score++;
    if (/[a-z]/.test(val))           score++;
    if (/[0-9]/.test(val))           score++;
    if (/[^A-Za-z0-9]/.test(val))    score++;

    const levels = [
        { w: '0%',   bg: '#e5e7eb', label: '' },
        { w: '25%',  bg: '#ef4444', label: 'Weak' },
        { w: '50%',  bg: '#f59e0b', label: 'Fair' },
        { w: '75%',  bg: '#3b82f6', label: 'Good' },
        { w: '100%', bg: '#10b981', label: 'Strong' },
    ];
    const lvl = levels[Math.min(score, 4)];
    bar.style.width      = lvl.w;
    bar.style.background = lvl.bg;
    txt.textContent      = lvl.label;
    txt.style.color      = lvl.bg;
});
</script>
@endpush
@endsection
