@extends('layouts.app')
@section('title', 'Change Password')

@section('breadcrumb')
    <li class="breadcrumb-item active">Change Password</li>
@endsection

@section('content')

<div class="page-hero">
    <div style="position:relative;z-index:1;">
        <h4><i class="bi bi-shield-lock me-2"></i>Change Password</h4>
        <p>Update your account password. Use a strong password with at least 8 characters.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:14px;">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card-glass">
            <div class="px-4 py-3 border-bottom d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-key-fill" style="color:#fff;font-size:1.1rem;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:.9rem;">{{ auth()->user()->name }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted);">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <form action="{{ route('change-password.update') }}" method="POST" class="p-4" id="changePwForm">
                @csrf

                {{-- Current Password --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">Current Password</label>
                    <div class="input-group">
                        <input type="password" name="current_password" id="curPw"
                            class="form-control @error('current_password') is-invalid @enderror"
                            placeholder="Enter current password" autocomplete="current-password">
                        <button type="button" class="btn btn-outline-secondary pw-toggle" data-target="curPw" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- New Password --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">New Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="newPw"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Minimum 8 characters" autocomplete="new-password"
                            oninput="checkStrength(this.value)">
                        <button type="button" class="btn btn-outline-secondary pw-toggle" data-target="newPw" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Strength bar --}}
                    <div class="mt-2" id="strengthWrap" style="display:none;">
                        <div style="height:4px;border-radius:4px;background:#e5e7eb;overflow:hidden;">
                            <div id="strengthBar" style="height:100%;width:0;border-radius:4px;transition:width .3s,background .3s;"></div>
                        </div>
                        <div id="strengthLabel" style="font-size:.7rem;margin-top:4px;color:var(--text-muted);"></div>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold" style="font-size:.82rem;">Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="confPw"
                            class="form-control"
                            placeholder="Repeat new password" autocomplete="new-password"
                            oninput="checkMatch()">
                        <button type="button" class="btn btn-outline-secondary pw-toggle" data-target="confPw" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="matchMsg" style="font-size:.72rem;margin-top:5px;"></div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius:10px;padding:10px;">
                    <i class="bi bi-shield-check me-2"></i>Update Password
                </button>
            </form>
        </div>

        {{-- Tips --}}
        <div class="card-glass mt-3 p-3" style="font-size:.78rem;">
            <div class="fw-bold mb-2" style="font-size:.8rem;"><i class="bi bi-lightbulb me-1 text-warning"></i>Password Tips</div>
            <ul class="mb-0 ps-3" style="color:var(--text-muted);line-height:1.8;">
                <li>Use at least 8 characters</li>
                <li>Mix uppercase, lowercase, numbers &amp; symbols</li>
                <li>Avoid using your name or email</li>
                <li>Don't reuse passwords from other sites</li>
            </ul>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Toggle show/hide password
document.querySelectorAll('.pw-toggle').forEach(btn => {
    btn.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        const icon  = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });
});

// Password strength
function checkStrength(val) {
    const wrap  = document.getElementById('strengthWrap');
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';

    let score = 0;
    if (val.length >= 8)              score++;
    if (val.length >= 12)             score++;
    if (/[A-Z]/.test(val))            score++;
    if (/[0-9]/.test(val))            score++;
    if (/[^A-Za-z0-9]/.test(val))     score++;

    const levels = [
        { pct:'20%', bg:'#ef4444', txt:'Very Weak' },
        { pct:'40%', bg:'#f97316', txt:'Weak' },
        { pct:'60%', bg:'#eab308', txt:'Fair' },
        { pct:'80%', bg:'#3b82f6', txt:'Good' },
        { pct:'100%',bg:'#10b981', txt:'Strong' },
    ];
    const lvl = levels[Math.min(score, 4)];
    bar.style.width      = lvl.pct;
    bar.style.background = lvl.bg;
    label.textContent    = lvl.txt;
    label.style.color    = lvl.bg;
}

// Match check
function checkMatch() {
    const newPw  = document.getElementById('newPw').value;
    const confPw = document.getElementById('confPw').value;
    const msg    = document.getElementById('matchMsg');
    if (!confPw) { msg.textContent = ''; return; }
    if (newPw === confPw) {
        msg.innerHTML = '<i class="bi bi-check2-circle" style="color:#10b981"></i> <span style="color:#10b981">Passwords match</span>';
    } else {
        msg.innerHTML = '<i class="bi bi-x-circle" style="color:#ef4444"></i> <span style="color:#ef4444">Passwords do not match</span>';
    }
}
</script>
@endpush
