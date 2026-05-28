<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ auth()->user()?->theme ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1a1d23;
            --sidebar-text: #a0aec0;
            --sidebar-active: #4f46e5;
            --header-height: 60px;
        }

        [data-bs-theme="dark"] {
            --bs-body-bg: #0f1117;
            --bs-body-color: #e2e8f0;
        }

        body { font-family: 'Inter', sans-serif; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            color: #fff;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: var(--sidebar-active);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }

        .sidebar-nav { padding: 16px 0; }

        .nav-section-title {
            color: rgba(255,255,255,0.35);
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 12px 20px 4px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.875rem;
            border-radius: 0;
            transition: all 0.2s;
            position: relative;
        }

        .sidebar-link:hover, .sidebar-link.active {
            color: #fff;
            background: rgba(79, 70, 229, 0.15);
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: var(--sidebar-active);
            border-radius: 0 2px 2px 0;
        }

        .sidebar-link .bi {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-link .badge {
            margin-left: auto;
            font-size: 0.6rem;
        }

        /* Main Content */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        /* Topbar */
        .topbar {
            height: var(--header-height);
            background: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color);
            position: sticky;
            top: 0; z-index: 999;
            display: flex; align-items: center;
            padding: 0 24px;
            gap: 16px;
        }

        .page-content { padding: 24px; }

        /* Cards */
        .stat-card {
            border-radius: 12px;
            border: 1px solid var(--bs-border-color);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }

        /* Tables */
        .table-responsive { border-radius: 12px; }

        /* Notification bell */
        .notif-bell { position: relative; }
        .notif-count {
            position: absolute;
            top: -4px; right: -4px;
            background: #ef4444;
            color: #fff;
            font-size: 0.6rem;
            padding: 1px 5px;
            border-radius: 10px;
            min-width: 16px;
            text-align: center;
        }

        /* Mobile sidebar toggle */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 5px; }

        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.3s ease; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="bi bi-shield-check text-white"></i>
            </div>
            <span>TxMonitor</span>
        </div>

        <div class="sidebar-nav">
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <div class="nav-section-title">Main</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('admin.transactions.index') }}" class="sidebar-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i> Transactions
                </a>
                <a href="{{ route('admin.fraud-alerts.index') }}" class="sidebar-link {{ request()->routeIs('admin.fraud-alerts.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-exclamation"></i> Fraud Alerts
                    @php $fraudCount = \App\Models\FraudAlert::where('status','open')->count(); @endphp
                    @if($fraudCount > 0) <span class="badge bg-danger">{{ $fraudCount }}</span> @endif
                </a>

                <div class="nav-section-title">People</div>
                <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Users
                </a>
                <a href="{{ route('admin.employees.index') }}" class="sidebar-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> Employees
                </a>

                <div class="nav-section-title">Work Tracking</div>
                <a href="{{ route('admin.attendance.index') }}" class="sidebar-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Attendance
                </a>
                <a href="{{ route('admin.tasks.index') }}" class="sidebar-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                    <i class="bi bi-kanban"></i> Tasks
                </a>
                <a href="{{ route('admin.work-reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.work-reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-check"></i> Work Reports
                </a>
                <a href="{{ route('admin.timesheets.index') }}" class="sidebar-link {{ request()->routeIs('admin.timesheets.*') ? 'active' : '' }}">
                    <i class="bi bi-table"></i> Timesheets
                </a>
                <a href="{{ route('admin.teams.index') }}" class="sidebar-link {{ request()->routeIs('admin.teams.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Teams
                </a>
                <a href="{{ route('admin.shifts.index') }}" class="sidebar-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-range"></i> Shifts
                </a>

                <div class="nav-section-title">Reports</div>
                <a href="{{ route('admin.reports.transactions') }}" class="sidebar-link {{ request()->routeIs('admin.reports.transactions') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart"></i> Tx Reports
                </a>
                <a href="{{ route('admin.reports.employees') }}" class="sidebar-link {{ request()->routeIs('admin.reports.employees') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i> HR Reports
                </a>
                <a href="{{ route('admin.reports.audit-logs') }}" class="sidebar-link {{ request()->routeIs('admin.reports.audit-logs') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Audit Logs
                </a>

                <div class="nav-section-title">Organisation</div>
                <a href="{{ route('admin.departments.index') }}" class="sidebar-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> Departments
                </a>
                <a href="{{ route('admin.holidays.index') }}" class="sidebar-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-heart"></i> Holidays
                </a>
                <a href="{{ route('admin.projects.index') }}" class="sidebar-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3"></i> Projects
                </a>

                <div class="nav-section-title">System</div>
                <a href="{{ route('admin.queue.index') }}" class="sidebar-link {{ request()->routeIs('admin.queue.*') ? 'active' : '' }}">
                    <i class="bi bi-cpu"></i> Queue Monitor
                    @php $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')->count(); @endphp
                    @if($failedJobs > 0) <span class="badge bg-danger">{{ $failedJobs }}</span> @endif
                </a>
                <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
            @else
                <div class="nav-section-title">My Workspace</div>
                <a href="{{ route('employee.dashboard') }}" class="sidebar-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('employee.attendance.index') }}" class="sidebar-link {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Attendance
                </a>
                <a href="{{ route('employee.tasks.index') }}" class="sidebar-link {{ request()->routeIs('employee.tasks.*') ? 'active' : '' }}">
                    <i class="bi bi-list-task"></i> My Tasks
                </a>
                <a href="{{ route('employee.work-reports.index') }}" class="sidebar-link {{ request()->routeIs('employee.work-reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Work Reports
                </a>
                <a href="{{ route('employee.attendance.leaves') }}" class="sidebar-link {{ request()->routeIs('employee.attendance.leaves') ? 'active' : '' }}">
                    <i class="bi bi-calendar-x"></i> Leave Requests
                </a>
            @endif
        </div>

        <!-- Sidebar Footer -->
        <div class="p-3 border-top" style="border-color: rgba(255,255,255,0.08) !important; margin-top: auto;">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="32" height="32" alt="">
                <div class="flex-grow-1 overflow-hidden">
                    <div class="text-white small fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                    <div style="color: var(--sidebar-text); font-size: 0.7rem;">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="color: var(--sidebar-text);" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <button class="btn btn-sm d-md-none me-2" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>

            <div class="d-none d-md-block">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            {{-- Global Search --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <div class="position-relative d-none d-lg-block ms-3" style="width:280px" id="globalSearchWrapper">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="globalSearch" class="form-control border-start-0 ps-0"
                           placeholder="Search transactions, users, employees..." autocomplete="off">
                </div>
                <div id="searchDropdown" class="dropdown-menu w-100 shadow p-0 mt-1" style="display:none; max-height:400px; overflow-y:auto;"></div>
            </div>
            @endif

            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary border-0" data-bs-toggle="dropdown" title="Language">
                        <i class="bi bi-translate"></i>
                        <span class="d-none d-md-inline small ms-1">{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item small {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="?lang=en">&#127468;&#127463; English</a></li>
                        <li><a class="dropdown-item small {{ app()->getLocale() === 'es' ? 'active' : '' }}" href="?lang=es">&#127466;&#127480; Español</a></li>
                    </ul>
                </div>

                <!-- Theme Toggle -->
                <button class="btn btn-sm btn-outline-secondary border-0" id="themeToggle" title="Toggle Theme">
                    <i class="bi bi-sun-fill" id="themeIcon"></i>
                </button>

                <!-- Notifications -->
                <div class="dropdown notif-bell">
                    <button class="btn btn-sm btn-outline-secondary border-0" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="notif-count" id="notifCount">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow" style="width: 350px; max-height: 400px; overflow-y: auto;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <strong>Notifications</strong>
                            <button class="btn btn-sm btn-link text-decoration-none" onclick="markAllRead()">Mark all read</button>
                        </div>
                        <div id="notifList">
                            <div class="dropdown-item text-muted small text-center py-3">Loading...</div>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="32" height="32" alt="">
                        <span class="d-none d-md-inline small">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        @if(auth()->user()->isEmployee())
                            <li><a class="dropdown-item" href="{{ route('employee.profile') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content fade-in">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    const APP = {
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        userId: document.querySelector('meta[name="user-id"]').content,

        ajax(url, method = 'GET', data = {}) {
            return $.ajax({
                url, method,
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                data: method !== 'GET' ? JSON.stringify(data) : data,
                contentType: method !== 'GET' ? 'application/json' : undefined,
            });
        },

        toast(message, type = 'success') {
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false,
                timer: 3000, timerProgressBar: true,
            });
            Toast.fire({ icon: type, title: message });
        },

        confirm(title, text, callback) {
            Swal.fire({ title, text, icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#4f46e5', cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!',
            }).then(result => { if (result.isConfirmed) callback(); });
        },
    };

    // Sidebar Toggle
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // Theme Toggle
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    document.getElementById('themeIcon').className = savedTheme === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';

    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-bs-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        document.getElementById('themeIcon').className = next === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        localStorage.setItem('theme', next);
    });

    // Load Notifications
    function loadNotifications() {
        $.get('/api/v1/notifications', function(res) {
            if (res.success) {
                const count = res.unread_count;
                document.getElementById('notifCount').textContent = count > 0 ? count : '';
                document.getElementById('notifCount').style.display = count > 0 ? 'block' : 'none';

                const list = document.getElementById('notifList');
                if (res.data.length === 0) {
                    list.innerHTML = '<div class="dropdown-item text-muted small text-center py-3">No notifications</div>';
                    return;
                }

                list.innerHTML = res.data.slice(0, 10).map(n => `
                    <a class="dropdown-item py-2 ${n.is_read ? '' : 'bg-primary bg-opacity-10'}" href="${n.link || '#'}" onclick="markRead(${n.id})">
                        <div class="d-flex gap-2">
                            <div class="text-${getNotifColor(n.type)} mt-1">
                                <i class="bi bi-${n.icon || 'bell'}"></i>
                            </div>
                            <div>
                                <div class="small fw-semibold">${n.title}</div>
                                <div class="small text-muted">${n.message}</div>
                                <div class="smaller text-muted">${new Date(n.created_at).toLocaleString()}</div>
                            </div>
                        </div>
                    </a>
                `).join('');
            }
        });
    }

    function getNotifColor(type) {
        return { success: 'success', warning: 'warning', danger: 'danger', fraud: 'danger', task: 'info' }[type] || 'primary';
    }

    function markRead(id) {
        $.post(`/api/v1/notifications/${id}/read`, { _token: APP.csrfToken });
    }

    function markAllRead() {
        $.post('/api/v1/notifications/read-all', { _token: APP.csrfToken }, () => loadNotifications());
    }

    // Load notifications on page load
    loadNotifications();

    // ── Global Search ────────────────────────────────────────────────────────
    const searchInput = document.getElementById('globalSearch');
    const searchDropdown = document.getElementById('searchDropdown');
    let searchTimer;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            const q = this.value.trim();
            if (q.length < 2) { searchDropdown.style.display = 'none'; return; }

            searchTimer = setTimeout(() => {
                $.get('/admin/search', { q }, function (res) {
                    if (!res.results || !res.results.length) {
                        searchDropdown.innerHTML = '<div class="p-3 text-muted small text-center">No results found</div>';
                    } else {
                        const colorMap = { primary:'primary', success:'success', info:'info', warning:'warning', danger:'danger' };
                        searchDropdown.innerHTML = res.results.map(r => `
                            <a class="dropdown-item d-flex align-items-start gap-2 py-2 border-bottom" href="${r.url}">
                                <span class="text-${r.color} mt-1"><i class="bi bi-${r.icon} fs-5"></i></span>
                                <div>
                                    <div class="small fw-semibold">${r.title}</div>
                                    <div class="text-muted" style="font-size:.7rem">${r.type} · ${r.subtitle}</div>
                                </div>
                            </a>
                        `).join('');
                    }
                    searchDropdown.style.display = 'block';
                }).fail(() => { searchDropdown.style.display = 'none'; });
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!document.getElementById('globalSearchWrapper')?.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { searchDropdown.style.display = 'none'; this.value = ''; }
        });
    }
    setInterval(loadNotifications, 30000);

    // Initialize DataTables
    $(document).ready(function() {
        if ($('.datatable').length) {
            $('.datatable').DataTable({
                responsive: true,
                pageLength: 10,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
            });
        }
    });
    </script>

    <!-- Laravel Echo / Real-time WebSocket (Laravel Reverb) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.3/echo.iife.js"></script>
    <script>
    // Initialise Laravel Echo with Laravel Reverb
    window.Echo = new LaravelEcho.default({
        broadcaster: 'reverb',
        key:         '{{ env('REVERB_APP_KEY') }}',
        wsHost:      '{{ env('REVERB_HOST', '127.0.0.1') }}',
        wsPort:      {{ env('REVERB_PORT', 8080) }},
        wssPort:     {{ env('REVERB_PORT', 8080) }},
        forceTLS:    false,
        enabledTransports: ['ws', 'wss'],
        auth: {
            headers: {
                'X-CSRF-TOKEN': APP.csrfToken,
            },
        },
    });

    // Private channel for the authenticated user — real-time notifications
    @auth
    Echo.private('App.Models.User.{{ auth()->id() }}')
        .notification(function (notification) {
            // Refresh notification bell
            loadNotifications();
            // Show toast for fraud alerts
            if (notification.type && notification.type.includes('Fraud')) {
                APP.toast('New fraud alert detected!', 'warning');
            }
        });

    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
    // Listen for new fraud alerts on the admin channel
    Echo.private('fraud-alerts')
        .listen('FraudAlertCreated', function (e) {
            loadNotifications();
            APP.toast('Fraud alert: ' + (e.message ?? 'New high-risk transaction detected'), 'warning');
            // Update badge count if element exists
            const badge = document.querySelector('.fraud-alert-badge');
            if (badge) badge.textContent = parseInt(badge.textContent || 0) + 1;
        });

    // Listen for transaction status updates
    Echo.channel('transactions')
        .listen('TransactionStatusUpdated', function (e) {
            APP.toast('Transaction ' + e.transaction_id + ' → ' + e.status, 'info');
        });
    @endif
    @endauth
    </script>

    @stack('scripts')
</body>
</html>
