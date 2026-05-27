@extends('layouts.auth')
@section('title', 'Reset Password')
@section('subtitle', 'Set your new password')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" name="email" id="email" value="{{ old('email', $email) }}"
               class="form-control @error('email') is-invalid @enderror" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <div class="input-group">
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror" required>
            <button type="button" class="btn btn-outline-secondary password-toggle" data-target="password">
                <i class="bi bi-eye"></i>
            </button>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-shield-check me-2"></i>Reset Password
    </button>
</form>
@endsection
