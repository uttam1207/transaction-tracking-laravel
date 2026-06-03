<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') — AS Dairy Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; }

        body {
            height: 100vh;
            overflow: hidden;
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex;
            background: #f8fafc;
        }

        /* ── Left Panel ── */
        .auth-panel-left {
            width: 52%;
            height: 100vh;
            background:
                linear-gradient(
                    160deg,
                    rgba(6,  50, 20, .95)  0%,
                    rgba(10, 90, 40, .88) 35%,
                    rgba(5, 130, 60, .80) 70%,
                    rgba(16,185, 80, .70) 100%
                ),
                url('https://images.unsplash.com/photo-1628352081506-83c43123a6b9?auto=format&fit=crop&w=1400&q=80')
                center / cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 36px 48px;
            position: relative;
            overflow: hidden;
        }

        /* decorative circles */
        .auth-panel-left::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 320px; height: 320px;
            background: rgba(255,255,255,.05);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .auth-panel-left::after {
            content: '';
            position: absolute;
            bottom: -100px; left: -60px;
            width: 280px; height: 280px;
            background: rgba(255,255,255,.04);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative; z-index: 1;
        }
        .auth-brand-icon {
            width: 46px; height: 46px;
            background: rgba(255,255,255,.18);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.28);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem;
            color: #fff;
        }
        .auth-brand-name {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -.3px;
            text-shadow: 0 1px 6px rgba(0,0,0,.4);
        }
        .auth-brand-tag {
            font-size: .65rem;
            background: rgba(255,255,255,.2);
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 20px;
            padding: 2px 8px;
            color: rgba(255,255,255,.9);
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-left: 2px;
        }

        /* Floating stat cards */
        .hero-stats {
            display: flex; gap: 10px; margin-bottom: 20px;
        }
        .hero-stat-card {
            flex: 1;
            background: rgba(255,255,255,.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 12px;
            padding: 12px 14px;
            position: relative;
            overflow: hidden;
        }
        .hero-stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, rgba(255,255,255,.6), rgba(255,255,255,.1));
            border-radius: 12px 12px 0 0;
        }
        .hero-stat-val {
            font-size: 1.3rem; font-weight: 800; color: #fff;
            letter-spacing: -.5px; line-height: 1;
        }
        .hero-stat-lbl {
            font-size: .68rem; color: rgba(255,255,255,.7);
            margin-top: 3px; text-transform: uppercase; letter-spacing: .5px;
        }

        .auth-panel-left .hero {
            position: relative; z-index: 1;
        }
        .auth-panel-left .hero h2 {
            color: #fff;
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1.28;
            margin-bottom: 10px;
            letter-spacing: -.4px;
            text-shadow: 0 2px 16px rgba(0,0,0,.35);
        }
        .auth-panel-left .hero p {
            color: rgba(255,255,255,.75);
            font-size: .86rem;
            line-height: 1.65;
            max-width: 380px;
        }

        /* Glass-card feature items */
        .feature-list { margin-top: 18px; display: flex; flex-direction: column; gap: 7px; }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,.92);
            font-size: .82rem;
            background: rgba(255,255,255,.08);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.11);
            border-radius: 10px;
            padding: 9px 13px;
            transition: background .2s;
        }
        .feature-item:hover { background: rgba(255,255,255,.14); }
        .feature-item-icon {
            width: 30px; height: 30px;
            background: rgba(255,255,255,.15);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: .85rem;
        }

        .auth-footer-quote {
            position: relative; z-index: 1;
            background: rgba(255,255,255,.07);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.12);
            border-left: 3px solid rgba(255,255,255,.45);
            border-radius: 0 10px 10px 0;
            padding: 12px 16px;
            color: rgba(255,255,255,.65);
            font-size: .8rem;
            font-style: italic;
        }

        /* ── Right Panel ── */
        .auth-panel-right {
            width: 48%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 48px;
            background: #fff;
            overflow-y: auto;
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 400px;
        }

        .auth-form-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -.5px;
            margin-bottom: 4px;
        }
        .auth-form-sub {
            color: #6b7280;
            font-size: .85rem;
            margin-bottom: 22px;
        }

        /* Inputs */
        .auth-field { margin-bottom: 16px; }
        .auth-field label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            letter-spacing: .2px;
        }
        .auth-input-wrap { position: relative; }
        .auth-input-wrap .input-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: .95rem;
            pointer-events: none;
        }
        .auth-input-wrap .form-control {
            padding-left: 40px;
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            height: 42px;
            font-size: .875rem;
            transition: border-color .2s, box-shadow .2s;
            background: #f9fafb;
        }
        .auth-input-wrap .form-control:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5,150,105,.12);
            background: #fff;
        }
        .auth-input-wrap .form-control.is-invalid {
            border-color: #ef4444;
        }
        .auth-input-wrap .password-toggle {
            position: absolute;
            right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
        }
        .auth-input-wrap .password-toggle:hover { color: #4f46e5; }

        /* Submit Button */
        .btn-auth {
            width: 100%;
            height: 42px;
            border-radius: 10px;
            font-size: .92rem;
            font-weight: 600;
            background: linear-gradient(135deg, #065f46, #059669);
            border: none;
            color: #fff;
            transition: opacity .2s, transform .1s;
            letter-spacing: .2px;
        }
        .btn-auth:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
        .btn-auth:active { transform: translateY(0); }

        /* Divider */
        .auth-divider {
            display: flex; align-items: center; gap: 12px;
            color: #d1d5db; font-size: .78rem;
            margin: 24px 0;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1; height: 1px; background: #e5e7eb;
        }

        /* Demo Box */
        .demo-box {
            background: #f0f4ff;
            border: 1px solid #c7d2fe;
            border-radius: 10px;
            padding: 10px 14px;
            margin-top: 14px;
        }
        .demo-box .demo-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #4f46e5;
            margin-bottom: 8px;
        }
        .demo-box .demo-row {
            display: flex; justify-content: space-between;
            font-size: .78rem; color: #374151;
            padding: 3px 0;
        }
        .demo-box code {
            background: #e0e7ff;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: .74rem;
            color: #4338ca;
        }

        /* Mobile */
        @media (max-width: 768px) {
            body { flex-direction: column; height: auto; overflow: auto; }
            .auth-panel-left { display: none; }
            .auth-panel-right {
                width: 100%;
                height: auto;
                padding: 40px 24px;
                align-items: flex-start;
            }
            .auth-form-wrap { max-width: 100%; }
        }
    </style>
</head>
<body>

    {{-- ── Left Branding Panel ── --}}
    <div class="auth-panel-left">

        {{-- Brand --}}
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <i class="bi bi-droplet-fill"></i>
            </div>
            <div>
                <span class="auth-brand-name">AS Dairy</span>
                <span class="auth-brand-tag">Dashboard</span>
            </div>
        </div>

        {{-- Hero Content --}}
        <div class="hero">

            {{-- Floating stat cards --}}
            <div class="hero-stats">
                <div class="hero-stat-card">
                    <div class="hero-stat-val"><i class="bi bi-wallet2 me-1" style="font-size:1rem;"></i>Wallet</div>
                    <div class="hero-stat-lbl">Live Balance Tracking</div>
                </div>
                <div class="hero-stat-card">
                    <div class="hero-stat-val"><i class="bi bi-people-fill me-1" style="font-size:1rem;"></i>Staff</div>
                    <div class="hero-stat-lbl">Attendance & Payroll</div>
                </div>
                <div class="hero-stat-card">
                    <div class="hero-stat-val"><i class="bi bi-bar-chart-fill me-1" style="font-size:1rem;"></i>Reports</div>
                    <div class="hero-stat-lbl">Financial Insights</div>
                </div>
            </div>

            <h2>Manage your dairy business smarter, not harder.</h2>
            <p>Track every payment, monitor your team, manage your wallet, and get real-time financial reports — all from one simple dashboard.</p>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-item-icon"><i class="bi bi-cash-coin"></i></div>
                    <span>Payment & collection tracking with wallet management</span>
                </div>
                <div class="feature-item">
                    <div class="feature-item-icon"><i class="bi bi-people-fill"></i></div>
                    <span>Staff attendance, shifts & performance management</span>
                </div>
                <div class="feature-item">
                    <div class="feature-item-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <span>Financial summary reports — income, expenses & balance</span>
                </div>
                <div class="feature-item">
                    <div class="feature-item-icon"><i class="bi bi-shield-check"></i></div>
                    <span>Secure transactions with full audit trail</span>
                </div>
            </div>
        </div>

        <div class="auth-footer-quote">
            "Good management is the foundation of a great dairy business."
        </div>
    </div>

    {{-- ── Right Form Panel ── --}}
    <div class="auth-panel-right">
        <div class="auth-form-wrap">
            {{-- Mobile-only brand --}}
            <div class="d-flex align-items-center gap-2 mb-4 d-md-none">
                <div style="background:linear-gradient(135deg,#065f46,#059669); border-radius:10px; width:36px; height:36px; font-size:1.1rem; display:flex; align-items:center; justify-content:center; color:#fff;">
                    <i class="bi bi-droplet-fill"></i>
                </div>
                <span style="font-weight:800; font-size:1rem; color:#111827;">AS Dairy Dashboard</span>
            </div>

            <div class="auth-form-title">@yield('title', 'Welcome back')</div>
            <div class="auth-form-sub">@yield('subtitle', 'Sign in to your account to continue')</div>

            @if($errors->any())
            <div class="alert alert-danger py-2 px-3 d-flex align-items-center gap-2 mb-4" style="border-radius:10px; font-size:.85rem; border:none; background:#fef2f2; color:#b91c1c;">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success py-2 px-3 d-flex align-items-center gap-2 mb-4" style="border-radius:10px; font-size:.85rem; border:none;">
                <i class="bi bi-check-circle-fill"></i>{{ session('success') }}
            </div>
            @endif

            @if(session('status'))
            <div class="alert alert-info py-2 px-3 mb-4" style="border-radius:10px; font-size:.85rem; border:none;">
                {{ session('status') }}
            </div>
            @endif

            @yield('content')
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
</body>
</html>
