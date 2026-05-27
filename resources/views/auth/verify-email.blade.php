@extends('layouts.auth')

@section('title', 'Verify Your Email')

@section('content')
<div class="text-center mb-4">
    <i class="bi bi-envelope-check fs-1 text-primary"></i>
    <h4 class="fw-bold mt-2">Verify Your Email</h4>
    <p class="text-muted">
        A verification link has been sent to <strong>{{ auth()->user()->email }}</strong>.
        Please check your inbox and click the link to verify your account.
    </p>
</div>

@if(session('resent'))
<div class="alert alert-success text-center">
    A fresh verification link has been sent to your email address.
</div>
@endif

<div class="d-grid gap-2">
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-send me-2"></i>Resend Verification Email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </button>
    </form>
</div>
@endsection
