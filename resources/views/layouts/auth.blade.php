<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') - Transaction Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1d23 0%, #2d3748 50%, #1a1d23 100%);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .auth-card {
            width: 100%; max-width: 440px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 32px;
            text-align: center;
            color: #fff;
        }
        .auth-header .brand-icon {
            width: 64px; height: 64px;
            background: rgba(255,255,255,0.2);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 1.8rem;
        }
        .auth-body { padding: 32px; }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }
        .btn-primary { background: #4f46e5; border-color: #4f46e5; }
        .btn-primary:hover { background: #4338ca; border-color: #4338ca; }
        .form-label { font-weight: 500; font-size: 0.875rem; }
        .input-group-text { background: #f8fafc; }
        .password-toggle { cursor: pointer; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <div class="brand-icon"><i class="bi bi-shield-check"></i></div>
            <h4 class="mb-1 fw-bold">Transaction Monitor</h4>
            <p class="mb-0 opacity-75 small">@yield('subtitle', 'Enterprise Security Platform')</p>
        </div>
        <div class="auth-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show py-2">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    {{ $errors->first() }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('status'))
                <div class="alert alert-info alert-dismissible fade show py-2">
                    {{ session('status') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
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
        // Password visibility toggle
        document.querySelectorAll('.password-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = document.getElementById(this.dataset.target);
                const icon = this.querySelector('i');
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
