@extends('layouts.auth')
@section('title', 'Forgot Password')
@section('subtitle', 'Reset your password')

@section('content')
<p class="text-muted small text-center mb-4">
    Enter your email address and we'll send you a password reset link.
</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-4">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="you@example.com" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-send me-2"></i>Send Reset Link
    </button>
</form>

<div class="text-center mt-4">
    <a href="{{ route('login') }}" class="small text-primary text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i>Back to Login
    </a>
</div>
@endsection
