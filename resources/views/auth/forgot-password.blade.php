@extends('layouts.auth')
@section('title', 'Reset Password')
@section('subtitle', 'We\'ll send a recovery link to your email')

@section('content')
<p class="auth-intro-text">
    No worries — enter your registered email address below and we'll send you a secure link to reset your password right away.
</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

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

    <button type="submit" class="btn btn-auth">
        <i class="bi bi-send me-2"></i>Send Reset Link
    </button>
</form>

<div class="auth-divider">or</div>

<div class="auth-footer-link">
    <a href="{{ route('login') }}" style="margin-left:0; display:inline-flex; align-items:center; gap:5px;">
        <i class="bi bi-arrow-left"></i> Back to Sign In
    </a>
</div>
@endsection
