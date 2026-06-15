<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') — AS Dairy Dashboard</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        ::-webkit-scrollbar { display: none; }
        * { scrollbar-width: none; -ms-overflow-style: none; }

        body {
            background: linear-gradient(135deg, #061410 0%, #0a1f17 35%, #0d2b1e 65%, #0f3323 100%);
            position: relative;
            display: flex;
            align-items: center;
        }

        /* Ambient glows */
        .bg-glow {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .bg-glow-1 {
            top: -180px; left: -120px;
            width: 560px; height: 560px;
            background: radial-gradient(circle, rgba(16,185,129,.13) 0%, transparent 70%);
        }
        .bg-glow-2 {
            bottom: -200px; right: -80px;
            width: 640px; height: 640px;
            background: radial-gradient(circle, rgba(5,150,105,.09) 0%, transparent 70%);
        }
        .bg-glow-3 {
            top: 20%; right: 28%;
            width: 380px; height: 380px;
            background: radial-gradient(circle, rgba(52,211,153,.055) 0%, transparent 70%);
        }

        /* ── Page layout ── */
        .auth-page {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ═══════════════════════════════════
           LEFT  PANEL
        ═══════════════════════════════════ */
        .auth-left {
            flex: 1;
            height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 36px 52px 36px 52px;
            overflow: hidden;
            min-width: 0;
        }

        /* Organic petal decoration — absolute background */
        .auth-decoration {
            position: absolute;
            bottom: -40px;
            right: -30px;
            width: 320px;
            height: 320px;
            pointer-events: none;
            opacity: .55;
        }
        .deco-petal {
            position: absolute;
            top: 50%; left: 50%;
            width: 120px;
            height: 180px;
            margin-left: -60px;
            margin-top: -160px;
            transform-origin: center bottom;
            border-radius: 50% 50% 18% 18%;
        }
        .deco-petal:nth-child(1) { background: linear-gradient(180deg, rgba(110,231,183,.9), rgba(5,150,105,.7));   transform: rotate(0deg); }
        .deco-petal:nth-child(2) { background: linear-gradient(180deg, rgba(52,211,153,.85), rgba(4,120,87,.65));   transform: rotate(51.4deg); }
        .deco-petal:nth-child(3) { background: linear-gradient(180deg, rgba(16,185,129,.8), rgba(6,95,70,.6));      transform: rotate(102.8deg); }
        .deco-petal:nth-child(4) { background: linear-gradient(180deg, rgba(5,150,105,.75), rgba(4,65,55,.55));     transform: rotate(154.2deg); }
        .deco-petal:nth-child(5) { background: linear-gradient(180deg, rgba(110,231,183,.65), rgba(16,185,129,.45)); transform: rotate(205.6deg); }
        .deco-petal:nth-child(6) { background: linear-gradient(180deg, rgba(167,243,208,.6), rgba(110,231,183,.4)); transform: rotate(257deg); }
        .deco-petal:nth-child(7) { background: linear-gradient(180deg, rgba(52,211,153,.55), rgba(5,150,105,.38)); transform: rotate(308.4deg); }
        .deco-center {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) translateY(-58px);
            width: 58px; height: 58px;
            background: radial-gradient(circle, rgba(167,243,208,.9), rgba(52,211,153,.55));
            border-radius: 50%;
            box-shadow: 0 0 40px rgba(52,211,153,.3);
        }

        /* ── Logo (top) ── */
        .auth-logo-wrap {
            flex-shrink: 0;
            margin-bottom: 28px;
            position: relative; z-index: 2;
        }
        .auth-logo-card {
            display: inline-flex;
            align-items: center;
            background: #fff;
            border-radius: 14px;
            padding: 12px 26px;
            box-shadow: 0 4px 24px rgba(0,0,0,.32), 0 1px 4px rgba(0,0,0,.16);
        }
        .auth-logo-card img {
            height: 70px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        /* ── Hero headline (middle) ── */
        .auth-hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative; z-index: 2;
            min-height: 0;
        }

        .auth-hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(52,211,153,.14);
            border: 1px solid rgba(52,211,153,.28);
            border-radius: 20px;
            padding: 4px 12px;
            color: #6ee7b7;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 14px;
            width: fit-content;
        }
        .auth-hero h1 {
            color: #fff;
            font-size: 1.8rem;
            font-weight: 900;
            line-height: 1.2;
            letter-spacing: -.5px;
            text-shadow: 0 2px 16px rgba(0,0,0,.3);
            margin-bottom: 10px;
        }
        .auth-hero h1 span {
            background: linear-gradient(90deg, #34d399, #6ee7b7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .auth-hero-desc {
            color: rgba(255,255,255,.62);
            font-size: .845rem;
            line-height: 1.68;
            max-width: 360px;
            margin-bottom: 22px;
        }

        /* Stat chips */
        .auth-stats {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }
        .auth-stat-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.11);
            border-radius: 10px;
            padding: 8px 14px;
        }
        .auth-stat-chip-icon {
            width: 28px; height: 28px;
            background: rgba(52,211,153,.16);
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            color: #6ee7b7;
            font-size: .82rem;
            flex-shrink: 0;
        }
        .auth-stat-chip-label {
            font-size: .78rem;
            font-weight: 600;
            color: rgba(255,255,255,.78);
        }
        .auth-stat-chip-sub {
            font-size: .65rem;
            color: rgba(255,255,255,.42);
            margin-top: 1px;
        }

        /* Feature rows */
        .auth-features {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }
        .auth-feature {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,.72);
            font-size: .8rem;
        }
        .auth-feature i {
            color: #34d399;
            font-size: .82rem;
            flex-shrink: 0;
        }

        /* ── Bottom CTA buttons ── */
        .auth-left-footer {
            flex-shrink: 0;
            position: relative; z-index: 2;
            margin-top: 22px;
        }
        .auth-left-footer p {
            font-size: .72rem;
            color: rgba(255,255,255,.35);
            margin-bottom: 10px;
            letter-spacing: .2px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .auth-branding-actions {
            display: flex;
            gap: 10px;
        }
        .btn-ghost {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 8px;
            color: rgba(255,255,255,.72);
            font-size: .78rem;
            font-weight: 500;
            padding: 7px 16px;
            cursor: pointer;
            backdrop-filter: blur(8px);
            transition: background .18s, color .18s;
            white-space: nowrap;
        }
        .btn-ghost:hover { background: rgba(255,255,255,.13); color: #fff; }

        /* ═══════════════════════════════════
           RIGHT  PANEL — glass card
        ═══════════════════════════════════ */
        .auth-right {
            width: 410px;
            flex-shrink: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 40px 20px 14px;
            overflow-y: auto;
        }

        .auth-glass-card {
            width: 100%;
            background: rgba(255,255,255,.065);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255,255,255,.11);
            border-radius: 20px;
            padding: 28px 26px;
            box-shadow:
                0 8px 40px rgba(0,0,0,.35),
                inset 0 1px 0 rgba(255,255,255,.09);
        }

        /* Card header */
        .auth-card-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.2px;
            margin-bottom: 4px;
        }
        .auth-card-sub {
            font-size: .8rem;
            color: rgba(255,255,255,.45);
            margin-bottom: 20px;
            line-height: 1.45;
        }

        /* Alerts */
        .auth-alert {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 9px;
            font-size: .8rem;
            margin-bottom: 14px;
        }
        .auth-alert-danger  { background: rgba(239,68,68,.12);  border: 1px solid rgba(239,68,68,.28);  color: #fca5a5; }
        .auth-alert-success { background: rgba(16,185,129,.1);  border: 1px solid rgba(16,185,129,.25); color: #6ee7b7; }
        .auth-alert-info    { background: rgba(59,130,246,.1);  border: 1px solid rgba(59,130,246,.25); color: #93c5fd; }

        /* Fields */
        .auth-field { margin-bottom: 13px; }
        .auth-field > label {
            display: block;
            font-size: .71rem;
            font-weight: 600;
            color: rgba(255,255,255,.55);
            margin-bottom: 5px;
            letter-spacing: .3px;
            text-transform: uppercase;
        }
        .auth-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        .auth-label-row label {
            font-size: .71rem;
            font-weight: 600;
            color: rgba(255,255,255,.55);
            letter-spacing: .3px;
            text-transform: uppercase;
        }
        .auth-label-row a {
            font-size: .71rem;
            color: rgba(255,255,255,.38);
            text-decoration: none;
            transition: color .15s;
        }
        .auth-label-row a:hover { color: #6ee7b7; }

        /* Inputs */
        .auth-input-wrap { position: relative; }
        .auth-input-wrap .form-control {
            width: 100%;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 9px;
            height: 40px;
            padding: 0 38px 0 14px;
            color: #fff;
            font-size: .845rem;
            outline: none;
            box-shadow: none;
            transition: border-color .18s, background .18s, box-shadow .18s;
        }
        .auth-input-wrap .form-control::placeholder { color: rgba(255,255,255,.25); }
        .auth-input-wrap .form-control:focus {
            border-color: rgba(52,211,153,.4);
            background: rgba(255,255,255,.1);
            box-shadow: 0 0 0 3px rgba(52,211,153,.1);
        }
        .auth-input-wrap .form-control.is-invalid { border-color: rgba(239,68,68,.5); }
        .auth-input-wrap .input-icon {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,.28);
            font-size: .88rem;
            pointer-events: none;
        }
        .auth-input-wrap .password-toggle {
            position: absolute;
            right: 10px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: rgba(255,255,255,.28);
            cursor: pointer; padding: 4px;
            line-height: 1; font-size: .88rem;
            transition: color .15s;
        }
        .auth-input-wrap .password-toggle:hover { color: #6ee7b7; }
        .invalid-feedback { color: #fca5a5; font-size: .74rem; margin-top: 4px; }

        /* Remember me */
        .auth-remember {
            display: flex; align-items: center; gap: 7px;
            margin-bottom: 16px;
        }
        .auth-remember input[type="checkbox"] {
            width: 14px; height: 14px;
            accent-color: #10b981; cursor: pointer; flex-shrink: 0;
        }
        .auth-remember label {
            font-size: .77rem;
            color: rgba(255,255,255,.48);
            cursor: pointer;
        }

        /* Buttons */
        .btn-auth {
            width: 100%;
            height: 40px;
            border-radius: 9px;
            font-size: .875rem;
            font-weight: 600;
            background: linear-gradient(135deg, #059669 0%, #34d399 100%);
            border: none;
            color: #fff;
            cursor: pointer;
            letter-spacing: .1px;
            margin-bottom: 10px;
            box-shadow: 0 4px 16px rgba(16,185,129,.28);
            transition: opacity .18s, transform .12s, box-shadow .18s;
        }
        .btn-auth:hover {
            opacity: .9; transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16,185,129,.36);
            color: #fff;
        }
        .btn-auth:active { transform: translateY(0); }

        /* Password strength */
        .strength-bar-wrap {
            margin-top: 6px; height: 3px;
            background: rgba(255,255,255,.1); border-radius: 3px; overflow: hidden;
        }
        #strengthBar { height: 100%; width: 0; border-radius: 3px; transition: width .3s, background .3s; }
        #strengthText { margin-top: 3px; font-size: .67rem; }

        /* Divider */
        .auth-divider {
            display: flex; align-items: center; gap: 10px;
            margin: 14px 0;
            color: rgba(255,255,255,.2); font-size: .72rem;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1; height: 1px; background: rgba(255,255,255,.1);
        }

        /* Footer link */
        .auth-footer-link {
            text-align: center; font-size: .8rem;
            color: rgba(255,255,255,.38);
        }
        .auth-footer-link a {
            color: #6ee7b7; font-weight: 600;
            text-decoration: none; margin-left: 3px;
        }
        .auth-footer-link a:hover { text-decoration: underline; }

        /* Optional field label suffix */
        .label-optional {
            font-size: .65rem; font-weight: 400;
            color: rgba(255,255,255,.28);
            text-transform: none; letter-spacing: 0; margin-left: 4px;
        }
        /* Forgot page intro */
        .auth-intro-text {
            font-size: .82rem; color: rgba(255,255,255,.48);
            line-height: 1.62; margin-bottom: 18px;
        }

        /* Trust badge row */
        .auth-trust {
            display: flex; align-items: center; gap: 6px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .auth-trust i { color: #34d399; font-size: .78rem; }
        .auth-trust span { font-size: .72rem; color: rgba(255,255,255,.36); }

        /* Mobile */
        @media (max-width: 768px) {
            html, body { overflow: auto; height: auto; }
            body { padding: 28px 18px; }
            .auth-page { flex-direction: column; height: auto; gap: 28px; }
            .auth-left { padding: 0; height: auto; flex: none; overflow: visible; }
            .auth-decoration { display: none; }
            .auth-right { width: 100%; height: auto; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>
    <div class="bg-glow bg-glow-3"></div>

    <div class="auth-page">

        {{-- ══ LEFT ══ --}}
        <div class="auth-left">

            {{-- Decorative flower (bottom-right of left panel) --}}
            <div class="auth-decoration">
                <div class="deco-petal"></div>
                <div class="deco-petal"></div>
                <div class="deco-petal"></div>
                <div class="deco-petal"></div>
                <div class="deco-petal"></div>
                <div class="deco-petal"></div>
                <div class="deco-petal"></div>
                <div class="deco-center"></div>
            </div>

            {{-- Logo --}}
            <div class="auth-logo-wrap">
                <div class="auth-logo-card">
                    <img src="{{ asset('images/logowithname.jpeg') }}" alt="AS Dairy Dashboard">
                </div>
            </div>

            {{-- Hero content --}}
            <div class="auth-hero">
                <div class="auth-hero-tag">
                    <i class="bi bi-patch-check-fill"></i>
                    Dairy Management Platform
                </div>

                <h1>Your Smart<br><span>Dairy Hub</span></h1>

                <p class="auth-hero-desc">
                    One powerful dashboard to manage every payment, track your team, monitor wallet balances, and generate real-time financial reports — built for dairy businesses.
                </p>

                <div class="auth-stats">
                    <div class="auth-stat-chip">
                        <div class="auth-stat-chip-icon"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="auth-stat-chip-label">Payments</div>
                            <div class="auth-stat-chip-sub">Track & collect</div>
                        </div>
                    </div>
                    <div class="auth-stat-chip">
                        <div class="auth-stat-chip-icon"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="auth-stat-chip-label">Staff</div>
                            <div class="auth-stat-chip-sub">Attendance & payroll</div>
                        </div>
                    </div>
                    <div class="auth-stat-chip">
                        <div class="auth-stat-chip-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
                        <div>
                            <div class="auth-stat-chip-label">Reports</div>
                            <div class="auth-stat-chip-sub">Live insights</div>
                        </div>
                    </div>
                </div>

                <div class="auth-features">
                    <div class="auth-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Real-time wallet balance & transaction tracking</span>
                    </div>
                    <div class="auth-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Staff attendance, shifts & performance management</span>
                    </div>
                    <div class="auth-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Income, expense & profit reports at a glance</span>
                    </div>
                    <div class="auth-feature">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Secure access with full audit trail</span>
                    </div>
                </div>
            </div>

            {{-- Bottom actions --}}
            <div class="auth-left-footer">
                <p>Explore the platform</p>
                <div class="auth-branding-actions">
                    <button class="btn-ghost"><i class="bi bi-lightning-charge-fill me-1"></i>Key Features</button>
                    <button class="btn-ghost"><i class="bi bi-grid-fill me-1"></i>All Modules</button>
                    <button class="btn-ghost"><i class="bi bi-headset me-1"></i>Support</button>
                </div>
            </div>
        </div>

        {{-- ══ RIGHT — glass form card ══ --}}
        <div class="auth-right">
            <div class="auth-glass-card">

                <div class="auth-card-title">@yield('title', 'Welcome back')</div>
                <div class="auth-card-sub">@yield('subtitle', 'Sign in to your account to continue')</div>

                @if($errors->any())
                <div class="auth-alert auth-alert-danger">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    {{ $errors->first() }}
                </div>
                @endif

                @if(session('success'))
                <div class="auth-alert auth-alert-success">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
                @endif

                @if(session('status'))
                <div class="auth-alert auth-alert-info">
                    <i class="bi bi-info-circle-fill flex-shrink-0"></i>
                    {{ session('status') }}
                </div>
                @endif

                @yield('content')

                <div class="auth-trust">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>256-bit encrypted · Secure login · Your data is safe</span>
                </div>
            </div>
        </div>

    </div>

    @if(config('services.recaptcha.site_key'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.password-toggle').forEach(btn => {
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
    </script>
    @stack('scripts')
</body>
</html>
