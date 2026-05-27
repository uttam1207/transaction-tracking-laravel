@extends('layouts.auth')
@section('title', 'Login')
@section('subtitle', 'Sign in to your account')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@example.com" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="••••••••" required>
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="password">
                <i class="bi bi-eye"></i>
            </button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label small" for="remember">Remember me</label>
        </div>
        <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary">Forgot password?</a>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
    </button>
</form>

<div class="text-center mt-4">
    <span class="small text-muted">Don't have an account?</span>
    <a href="{{ route('register') }}" class="small text-primary text-decoration-none fw-semibold ms-1">Create Account</a>
</div>

<div class="text-center mt-3 p-3 rounded" style="background: #f0f4ff;">
    <div class="small text-muted mb-1"><strong>Demo Credentials</strong></div>
    <div class="small">Admin: <code>admin@demo.com</code> / <code>Admin@123</code></div>
    <div class="small">Employee: <code>emp@demo.com</code> / <code>Admin@123</code></div>
</div>
@endsection
