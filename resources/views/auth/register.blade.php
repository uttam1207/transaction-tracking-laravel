@extends('layouts.auth')
@section('title', 'Create Account')
@section('subtitle', 'Join AS Dairy Dashboard — get started in seconds')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    {{-- Full Name --}}
    <div class="auth-field">
        <label>Full Name</label>
        <div class="auth-input-wrap">
            <input type="text" name="name" id="name"
                   value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="John Doe" required>
            <i class="bi bi-person input-icon"></i>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Email --}}
    <div class="auth-field">
        <label>Email Address</label>
        <div class="auth-input-wrap">
            <input type="email" name="email" id="email"
                   value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@company.com" required>
            <i class="bi bi-envelope input-icon"></i>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Phone --}}
    <div class="auth-field">
        <label>Phone <span class="label-optional">(optional)</span></label>
        <div class="auth-input-wrap">
            <input type="tel" name="phone" id="phone"
                   value="{{ old('phone') }}"
                   class="form-control"
                   placeholder="+1 234 567 8900">
            <i class="bi bi-phone input-icon"></i>
        </div>
    </div>

    {{-- Password --}}
    <div class="auth-field">
        <label>Password</label>
        <div class="auth-input-wrap">
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Min. 8 characters" required>
            <button type="button" class="password-toggle" data-target="password">
                <i class="bi bi-eye"></i>
            </button>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="strength-bar-wrap"><div id="strengthBar"></div></div>
        <div id="strengthText"></div>
    </div>

    {{-- Confirm Password --}}
    <div class="auth-field">
        <label>Confirm Password</label>
        <div class="auth-input-wrap">
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="form-control"
                   placeholder="Repeat your password" required>
            <button type="button" class="password-toggle" data-target="password_confirmation">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-auth">
        <i class="bi bi-person-plus me-2"></i>Create Account
    </button>
</form>

<div class="auth-divider">or</div>

<div class="auth-footer-link">
    Already have an account?
    <a href="{{ route('login') }}">Sign in</a>
</div>

@push('scripts')
<script>
document.getElementById('password')?.addEventListener('input', function () {
    const val = this.value;
    const bar = document.getElementById('strengthBar');
    const txt = document.getElementById('strengthText');
    let score = 0;
    if (val.length >= 8)           score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[a-z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val))  score++;
    const levels = [
        { w: '0%',   bg: 'rgba(255,255,255,.1)', label: '' },
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
