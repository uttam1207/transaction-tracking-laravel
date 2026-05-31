@extends('layouts.auth')
@section('title', 'Welcome back')
@section('subtitle', 'Sign in to your account to continue')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    {{-- Email --}}
    <div class="auth-field">
        <label for="email">Email Address</label>
        <div class="auth-input-wrap">
            <i class="bi bi-envelope input-icon"></i>
            <input type="email" name="email" id="email"
                   value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@company.com" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Password --}}
    <div class="auth-field">
        <label for="password">
            Password
            <a href="{{ route('password.request') }}"
               class="float-end text-decoration-none"
               style="color:#4f46e5; font-weight:500;">Forgot password?</a>
        </label>
        <div class="auth-input-wrap">
            <i class="bi bi-lock input-icon"></i>
            <input type="password" name="password" id="password"
                   class="form-control pe-5 @error('password') is-invalid @enderror"
                   placeholder="••••••••" required>
            <button type="button" class="password-toggle" data-target="password">
                <i class="bi bi-eye"></i>
            </button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Remember me --}}
    <div class="d-flex align-items-center mb-4">
        <input class="form-check-input me-2" type="checkbox" name="remember" id="remember"
               style="border-radius:5px; cursor:pointer;">
        <label class="form-check-label small" for="remember" style="color:#6b7280; cursor:pointer;">
            Keep me signed in for 30 days
        </label>
    </div>

    {{-- reCAPTCHA --}}
    @if(config('services.recaptcha.site_key'))
    <div class="mb-4">
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
        @error('g-recaptcha-response')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    @endif

    <button type="submit" class="btn btn-auth">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
    </button>
</form>

<div class="text-center mt-4" style="font-size:.875rem; color:#6b7280;">
    Don't have an account?
    <a href="{{ route('register') }}" class="fw-semibold text-decoration-none ms-1" style="color:#4f46e5;">
        Create one free
    </a>
</div>

{{-- Demo credentials --}}
<div class="demo-box">
    <div class="demo-title">Demo Credentials</div>
    <div class="demo-row">
        <span>Admin</span>
        <span><code>admin@demo.com</code> / <code>Admin@123</code></span>
    </div>
    <div class="demo-row">
        <span>Employee</span>
        <span><code>emp@demo.com</code> / <code>Admin@123</code></span>
    </div>
</div>
@endsection
