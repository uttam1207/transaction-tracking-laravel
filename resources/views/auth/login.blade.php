@extends('layouts.auth')
@section('title', 'Welcome Back')
@section('subtitle', 'Sign in to your AS Dairy Dashboard account')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    {{-- Email --}}
    <div class="auth-field">
        <label>Email Address</label>
        <div class="auth-input-wrap">
            <input type="email" name="email" id="email"
                   value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@company.com" required autofocus>
            <i class="bi bi-envelope input-icon"></i>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Password --}}
    <div class="auth-field">
        <div class="auth-label-row">
            <label>Password</label>
            <a href="{{ route('password.request') }}">Forgot password?</a>
        </div>
        <div class="auth-input-wrap">
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="••••••••••••" required>
            <button type="button" class="password-toggle" data-target="password">
                <i class="bi bi-eye"></i>
            </button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Remember me --}}
    <div class="auth-remember">
        <input type="checkbox" name="remember" id="remember">
        <label for="remember">Keep me signed in for 30 days</label>
    </div>

    {{-- reCAPTCHA --}}
    @if(config('services.recaptcha.site_key'))
    <div style="margin-bottom:14px;">
        <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
        @error('g-recaptcha-response')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    @endif

    <button type="submit" class="btn btn-auth">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
    </button>
</form>

<div class="auth-divider">or</div>

<div class="auth-footer-link">
    Don't have an account?
    <a href="{{ route('register') }}">Create one free</a>
</div>
@endsection
