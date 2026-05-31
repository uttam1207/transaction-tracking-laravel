@extends('layouts.auth')
@section('title', 'Forgot Password')
@section('subtitle', 'Enter your email to receive a reset link')

@section('content')
<p style="font-size:.85rem; color:#6b7280; margin-bottom:28px; line-height:1.6;">
    No worries — enter your registered email address and we'll send you a link to reset your password.
</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

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

    <button type="submit" class="btn btn-auth">
        <i class="bi bi-send me-2"></i>Send Reset Link
    </button>
</form>

<div class="text-center mt-4" style="font-size:.875rem; color:#6b7280;">
    <a href="{{ route('login') }}" class="fw-semibold text-decoration-none" style="color:#4f46e5;">
        <i class="bi bi-arrow-left me-1"></i>Back to Sign In
    </a>
</div>
@endsection
