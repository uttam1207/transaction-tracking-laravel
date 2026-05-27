@extends('layouts.auth')
@section('title', 'Two-Factor Auth')
@section('subtitle', '2FA Verification')

@section('content')
<div class="text-center mb-4">
    <div class="display-6 mb-2">🔐</div>
    <p class="text-muted small">Enter the 6-digit code from your authenticator app.</p>
</div>

<form method="POST" action="/2fa">
    @csrf
    <div class="mb-4">
        <label for="code" class="form-label text-center d-block">Authentication Code</label>
        <input type="text" name="code" id="code"
               class="form-control form-control-lg text-center letter-spacing-lg @error('code') is-invalid @enderror"
               maxlength="6" pattern="[0-9]{6}" placeholder="000000"
               style="font-size: 2rem; letter-spacing: 0.5rem;" autofocus required>
        @error('code') <div class="invalid-feedback text-center">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-check-circle me-2"></i>Verify Code
    </button>
</form>

<div class="text-center mt-4">
    <a href="{{ route('login') }}" class="small text-muted text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i>Back to Login
    </a>
</div>
@endsection
