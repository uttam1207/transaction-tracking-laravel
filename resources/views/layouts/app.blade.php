<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ auth()->user()?->theme ?? 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">

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
            --sidebar-bg: #0f1117;
            --sidebar-text: #8b95a8;
            --sidebar-active: #4f46e5;
            --header-height: 64px;
        }

        [data-bs-theme="dark"] {
            --bs-body-bg: #0f1117;
            --bs-body-color: #e2e8f0;
        }

        body { font-family: 'Inter', sans-serif; }

        /* ── Sidebar Shell ── */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow: hidden;               /* brand + footer stay fixed */
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.06);
        }

        /* ── Brand ── */
        .sidebar-brand {
            padding: 0 16px;
            height: 64px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }
        .sidebar-brand .brand-icon img {
            width: 100%; height: 100%;
            object-fit: contain;
        }
        .sidebar-brand .brand-name {
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -.3px;
        }
        .sidebar-brand .brand-badge {
            margin-left: auto;
            background: rgba(79,70,229,.2);
            color: #818cf8;
            font-size: .6rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            border: 1px solid rgba(129,140,248,.2);
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        /* ── Nav ── */
        .sidebar-nav {
            padding: 12px 0;
            flex: 1;
            min-height: 0;          /* allows flex child to shrink and scroll */
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.12) transparent;
        }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.18); border-radius: 3px; }

        .nav-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,.25);
            font-size: .6rem;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            padding: 16px 20px 6px;
        }
        .nav-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,.06);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 1px 10px;
            padding: 8px 12px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all .18s;
            position: relative;
        }
        .sidebar-link:hover {
            color: #e2e8f0;
            background: rgba(255,255,255,.06);
        }
        .sidebar-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(79,70,229,.35), rgba(124,58,237,.25));
            box-shadow: inset 0 0 0 1px rgba(129,140,248,.15);
        }
        .sidebar-link .nav-icon {
            width: 30px; height: 30px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
            background: rgba(255,255,255,.05);
            transition: all .18s;
        }
        .sidebar-link:hover .nav-icon {
            background: rgba(255,255,255,.1);
        }
        .sidebar-link.active .nav-icon {
            background: rgba(79,70,229,.5);
            color: #a5b4fc;
        }
        .sidebar-link .nav-label { flex: 1; }
        .sidebar-link .badge {
            font-size: .58rem;
            padding: 2px 6px;
            border-radius: 20px;
        }

        /* ── Sidebar Footer ── */
        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,.06);
            flex-shrink: 0;
        }
        .sidebar-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.06);
        }
        .sidebar-user-card img { flex-shrink: 0; }
        .sidebar-user-card .user-name {
            color: #e2e8f0;
            font-size: .82rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-card .user-role {
            color: var(--sidebar-text);
            font-size: .68rem;
        }
        .sidebar-user-card .logout-btn {
            margin-left: auto;
            width: 28px; height: 28px;
            border-radius: 8px;
            background: rgba(239,68,68,.1);
            border: none;
            color: #f87171;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem;
            cursor: pointer;
            transition: background .15s;
            flex-shrink: 0;
        }
        .sidebar-user-card .logout-btn:hover { background: rgba(239,68,68,.25); }

        /* ── Main Content ── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        /* ── Topbar ── */
        .topbar {
            height: var(--header-height);
            background: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color);
            position: sticky;
            top: 0; z-index: 999;
            display: flex; align-items: center;
            padding: 0 20px;
            gap: 14px;
            backdrop-filter: blur(8px);
        }
        .topbar-action-btn {
            width: 34px; height: 34px;
            border-radius: 10px;
            border: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
            color: #6b7280;
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s, color .15s, border-color .15s;
        }
        .topbar-action-btn:hover {
            background: #f3f4f6;
            color: #374151;
            border-color: #d1d5db;
        }
        [data-bs-theme="dark"] .topbar-action-btn:hover {
            background: rgba(255,255,255,.08);
            color: #e2e8f0;
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
            top: -3px; right: -3px;
            background: #ef4444;
            color: #fff;
            font-size: 0.55rem;
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 10px;
            min-width: 15px;
            text-align: center;
            border: 1.5px solid var(--bs-body-bg);
            line-height: 1.4;
        }

        /* Mobile */
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

        /* ─────────────────────────────────────────
           SHARED MODERN UI COMPONENTS (all pages)
        ───────────────────────────────────────── */

        /* Page Hero Banner */
        .page-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
            border-radius: 16px; padding: 24px 28px; margin-bottom: 24px;
            color: #fff; position: relative; overflow: hidden;
        }
        .page-hero::before {
            content:''; position:absolute; top:-50px; right:-40px;
            width:200px; height:200px; background:rgba(255,255,255,.05); border-radius:50%;
            pointer-events:none;
        }
        .page-hero::after {
            content:''; position:absolute; bottom:-60px; right:80px;
            width:140px; height:140px; background:rgba(255,255,255,.04); border-radius:50%;
            pointer-events:none;
        }
        .page-hero h4, .page-hero h5 { font-weight:800; letter-spacing:-.4px; margin-bottom:4px; }
        .page-hero p   { opacity:.7; font-size:.83rem; margin-bottom:0; }
        .page-hero-stat { text-align:center; }
        .page-hero-stat .v { font-size:1.6rem; font-weight:800; line-height:1; }
        .page-hero-stat .l { font-size:.7rem; opacity:.65; margin-top:3px; text-transform:uppercase; letter-spacing:.5px; }
        .hero-vr { width:1px; background:rgba(255,255,255,.2); align-self:stretch; margin:4px 0; }

        /* Filter Bar */
        .filter-card {
            background:#fff; border-radius:14px; border:1px solid #e5e7eb;
            padding:16px 20px; margin-bottom:20px; box-shadow:0 1px 4px rgba(0,0,0,.04);
        }
        .filter-card .form-control,
        .filter-card .form-select {
            border-radius:8px !important; border:1.5px solid #e5e7eb !important;
            font-size:.83rem !important; height:36px !important; background:#f9fafb !important;
        }
        .filter-card .form-control:focus,
        .filter-card .form-select:focus {
            border-color:#4f46e5 !important; box-shadow:0 0 0 3px rgba(79,70,229,.1) !important; background:#fff !important;
        }
        .btn-filter { height:36px; border-radius:8px; font-size:.82rem; font-weight:600; padding:0 16px; }

        /* Table Card */
        .table-card {
            background:#fff; border-radius:14px; border:1px solid #e5e7eb;
            box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden;
        }
        .table-card .card-header {
            background:#fff; border-bottom:1px solid #f3f4f6; padding:14px 20px;
            display:flex; align-items:center; justify-content:space-between;
        }
        .table-card .card-header .card-title {
            font-weight:700; font-size:.9rem; color:#111827; margin:0;
        }
        .table-card .pagination-wrap {
            padding:12px 20px; border-top:1px solid #f3f4f6;
            display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;
        }
        .table-card .pagination-info { font-size:.78rem; color:#6b7280; }

        /* Modern Table */
        .modern-table thead th {
            background:#f8fafc; font-size:.72rem; font-weight:700;
            text-transform:uppercase; letter-spacing:.6px; color:#6b7280;
            border-bottom:1px solid #e5e7eb; padding:10px 14px; white-space:nowrap;
        }
        .modern-table tbody td {
            padding:11px 14px; font-size:.85rem; vertical-align:middle;
            border-bottom:1px solid #f3f4f6;
        }
        .modern-table tbody tr:last-child td { border-bottom:none; }
        .modern-table tbody tr:hover { background:#fafbff; }

        /* Status Pills */
        .spill {
            display:inline-flex; align-items:center; gap:5px;
            padding:3px 10px; border-radius:20px; font-size:.73rem; font-weight:600;
        }
        .spill::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block; }
        .spill-success   { background:#dcfce7; color:#16a34a; }
        .spill-danger    { background:#fee2e2; color:#dc2626; }
        .spill-warning   { background:#fef9c3; color:#ca8a04; }
        .spill-info      { background:#dbeafe; color:#2563eb; }
        .spill-secondary { background:#f3f4f6; color:#6b7280; }
        .spill-purple    { background:#ede9fe; color:#7c3aed; }
        .spill-open      { background:#fee2e2; color:#dc2626; }
        .spill-investigating { background:#fef3c7; color:#92400e; }
        .spill-resolved  { background:#dcfce7; color:#16a34a; }
        .spill-false_positive { background:#f3f4f6; color:#6b7280; }
        .spill-active    { background:#dcfce7; color:#16a34a; }
        .spill-inactive  { background:#fee2e2; color:#dc2626; }
        .spill-on_leave  { background:#fef9c3; color:#ca8a04; }
        .spill-pending   { background:#fef9c3; color:#ca8a04; }
        .spill-processing { background:#dbeafe; color:#2563eb; }
        .spill-failed    { background:#fee2e2; color:#dc2626; }
        .spill-cancelled { background:#f3f4f6; color:#6b7280; }
        .spill-reversed  { background:#ede9fe; color:#7c3aed; }

        /* Severity / Risk */
        .sev-critical { background:#fee2e2; color:#dc2626; }
        .sev-high     { background:#fed7aa; color:#c2410c; }
        .sev-medium   { background:#fef3c7; color:#92400e; }
        .sev-low      { background:#dcfce7; color:#16a34a; }

        /* Action Buttons */
        .act-btn {
            display:inline-flex; align-items:center; justify-content:center;
            width:28px; height:28px; border-radius:7px; border:none;
            font-size:.8rem; cursor:pointer; text-decoration:none; transition:background .15s;
        }
        .act-view   { background:#ede9fe; color:#7c3aed; }
        .act-view:hover { background:#ddd6fe; }
        .act-edit   { background:#fef3c7; color:#d97706; }
        .act-edit:hover { background:#fde68a; }
        .act-delete { background:#fee2e2; color:#dc2626; }
        .act-delete:hover { background:#fecaca; }
        .act-green  { background:#dcfce7; color:#16a34a; }
        .act-green:hover { background:#bbf7d0; }
        .act-info   { background:#dbeafe; color:#2563eb; }
        .act-info:hover { background:#bfdbfe; }

        /* Info Card */
        .info-card {
            background:#fff; border:1px solid #e5e7eb; border-radius:14px;
            box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:20px; overflow:hidden;
        }
        .info-card-hdr {
            padding:13px 20px; border-bottom:1px solid #f3f4f6;
            background:#f9fafb; display:flex; align-items:center; gap:8px;
            font-size:.78rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.5px; color:#6b7280;
        }
        .info-card-hdr i { color:#4f46e5; font-size:.9rem; }
        .info-card-body { padding:20px; }
        .dl { margin-bottom:14px; }
        .dl:last-child { margin-bottom:0; }
        .dl dt { font-size:.7rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.4px; margin-bottom:3px; }
        .dl dd { font-size:.87rem; color:#111827; font-weight:500; margin:0; }

        /* Form Section */
        .form-section {
            background:#fff; border:1px solid #e5e7eb; border-radius:14px;
            box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:20px; overflow:hidden;
        }
        .form-section-hdr {
            padding:13px 20px; border-bottom:1px solid #f3f4f6; background:#f9fafb;
            display:flex; align-items:center; gap:8px;
            font-size:.78rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.5px; color:#6b7280;
        }
        .form-section-hdr i { color:#4f46e5; font-size:.9rem; }
        .form-section-body { padding:20px; }
        .flabel { font-size:.78rem !important; font-weight:600 !important; color:#374151 !important; margin-bottom:6px !important; display:block; }
        .flabel .req { color:#ef4444; }
        .finput, .fselect {
            border-radius:9px !important; border:1.5px solid #e5e7eb !important;
            font-size:.875rem !important; height:40px !important; background:#f9fafb !important;
            transition:border-color .2s, box-shadow .2s !important; width:100%; padding:.375rem .75rem;
        }
        .finput:focus, .fselect:focus {
            border-color:#4f46e5 !important; box-shadow:0 0 0 3px rgba(79,70,229,.1) !important; background:#fff !important; outline:none;
        }
        textarea.finput { height:auto !important; }

        /* Empty State */
        .empty-state { text-align:center; padding:52px 24px; color:#9ca3af; }
        .empty-state i { font-size:2.4rem; display:block; margin-bottom:12px; opacity:.3; }
        .empty-state p { font-size:.88rem; margin:0; }

        /* Back Button */
        .back-btn {
            display:inline-flex; align-items:center; gap:6px;
            font-size:.82rem; color:#6b7280; text-decoration:none;
            padding:6px 12px; border-radius:8px; border:1px solid #e5e7eb;
            background:#fff; font-weight:600; margin-bottom:16px;
            transition:background .15s;
        }
        .back-btn:hover { background:#f3f4f6; color:#374151; }

        /* Primary gradient button */
        .btn-primary-grad {
            background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff;
            border:none; border-radius:9px; font-weight:700; font-size:.875rem;
            transition:opacity .2s;
        }
        .btn-primary-grad:hover { opacity:.9; color:#fff; }

        /* Modal polish */
        .modal-content { border-radius:14px; border:none; box-shadow:0 20px 60px rgba(0,0,0,.15); }
        .modal-header  { border-bottom:1px solid #f3f4f6; padding:16px 20px; }
        .modal-footer  { border-top:1px solid #f3f4f6; padding:12px 20px; }

        /* ── Breadcrumb ── */
        .topbar-breadcrumb-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--bs-tertiary-bg, #f3f4f6);
            border: 1px solid var(--bs-border-color);
            border-radius: 20px;
            padding: 4px 12px 4px 5px;
        }
        .bc-home-btn {
            width: 24px; height: 24px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: .65rem;
            flex-shrink: 0;
            text-decoration: none;
            transition: opacity .15s;
        }
        .bc-home-btn:hover { opacity: .85; color: #fff; }
        .topbar-breadcrumb-wrap .breadcrumb {
            margin: 0;
            padding: 0;
            background: transparent;
            font-size: .78rem;
            flex-wrap: nowrap;
            align-items: center;
        }
        .topbar-breadcrumb-wrap .breadcrumb-item {
            display: flex;
            align-items: center;
            color: #6b7280;
            font-weight: 500;
            white-space: nowrap;
            line-height: 1;
        }
        .topbar-breadcrumb-wrap .breadcrumb-item + .breadcrumb-item::before {
            content: '';
            display: inline-block;
            width: 5px; height: 8px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 6 10'%3E%3Cpath d='M1 1l4 4-4 4' stroke='%23d1d5db' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-size: contain;
            padding: 0;
            margin: 0 6px;
            vertical-align: middle;
            opacity: 1;
            float: none;
        }
        .topbar-breadcrumb-wrap .breadcrumb-item a {
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            transition: color .15s;
        }
        .topbar-breadcrumb-wrap .breadcrumb-item a:hover { color: #4f46e5; }
        .topbar-breadcrumb-wrap .breadcrumb-item.active {
            color: #111827;
            font-weight: 600;
        }
        [data-bs-theme="dark"] .topbar-breadcrumb-wrap .breadcrumb-item.active { color: #e2e8f0; }
        [data-bs-theme="dark"] .topbar-breadcrumb-wrap { background: rgba(255,255,255,.05); }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">

        {{-- Brand --}}
        <div class="sidebar-brand">
            <div class="brand-icon"><img src="{{ asset('images/logo.jpeg') }}" alt="Logo"></div>
            <span class="brand-name">AS Dairy Dashboard</span>
            <span class="brand-badge">Pro</span>
        </div>

        {{-- Nav Items --}}
        <div class="sidebar-nav">
            @php $svcUser = auth()->user(); @endphp
            @if($svcUser->isAdmin() || $svcUser->isManager())

                <div class="nav-section-title">Main</div>

                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="{{ route('documents.index') }}" class="sidebar-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-folder2-open"></i></span>
                    <span class="nav-label">Document</span>
                </a>
                <a href="{{ route('questions.index') }}" class="sidebar-link {{ request()->routeIs('questions.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-patch-question"></i></span>
                    <span class="nav-label">Q&amp;A</span>
                </a>
                @if(\App\Models\ServicePermission::canAccess('transactions', $svcUser))
                <a href="{{ route('admin.transactions.index') }}" class="sidebar-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-arrow-left-right"></i></span>
                    <span class="nav-label">Transactions</span>
                </a>
                @endif

                <div class="nav-section-title">People</div>

                @if(\App\Models\ServicePermission::canAccess('users', $svcUser))
                <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-people"></i></span>
                    <span class="nav-label">Users</span>
                </a>
                @endif
                @if(\App\Models\ServicePermission::canAccess('employees', $svcUser))
                <a href="{{ route('admin.employees.index') }}" class="sidebar-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-person-badge"></i></span>
                    <span class="nav-label">Employees</span>
                </a>
                @endif

                <div class="nav-section-title">Work Tracking</div>

                @if(\App\Models\ServicePermission::canAccess('attendance', $svcUser))
                <a href="{{ route('admin.attendance.index') }}" class="sidebar-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-clock-history"></i></span>
                    <span class="nav-label">Attendance</span>
                </a>
                @endif
                @if(\App\Models\ServicePermission::canAccess('tasks', $svcUser))
                <a href="{{ route('admin.tasks.index') }}" class="sidebar-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-kanban"></i></span>
                    <span class="nav-label">Tasks</span>
                </a>
                @endif
                @if(\App\Models\ServicePermission::canAccess('work_reports', $svcUser))
                <a href="{{ route('admin.work-reports.index') }}" class="sidebar-link {{ request()->routeIs('admin.work-reports.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-file-earmark-check"></i></span>
                    <span class="nav-label">Work Reports</span>
                </a>
                @endif
                @if(\App\Models\ServicePermission::canAccess('timesheets', $svcUser))
                <a href="{{ route('admin.timesheets.index') }}" class="sidebar-link {{ request()->routeIs('admin.timesheets.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-table"></i></span>
                    <span class="nav-label">Timesheets</span>
                </a>
                @endif
                @if(\App\Models\ServicePermission::canAccess('teams', $svcUser))
                <a href="{{ route('admin.teams.index') }}" class="sidebar-link {{ request()->routeIs('admin.teams.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
                    <span class="nav-label">Teams</span>
                </a>
                @endif
                @if(\App\Models\ServicePermission::canAccess('shifts', $svcUser))
                <a href="{{ route('admin.shifts.index') }}" class="sidebar-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-calendar-range"></i></span>
                    <span class="nav-label">Shifts</span>
                </a>
                @endif

                <div class="nav-section-title">Reports</div>

                @if(\App\Models\ServicePermission::canAccess('reports', $svcUser))
                <a href="{{ route('admin.reports.transactions') }}" class="sidebar-link {{ request()->routeIs('admin.reports.transactions') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-bar-chart"></i></span>
                    <span class="nav-label">Tx Reports</span>
                </a>
                <a href="{{ route('admin.reports.financial-summary') }}" class="sidebar-link {{ request()->routeIs('admin.reports.financial-summary') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-graph-up-arrow"></i></span>
                    <span class="nav-label">Financial</span>
                </a>
                <a href="{{ route('admin.reports.employees') }}" class="sidebar-link {{ request()->routeIs('admin.reports.employees') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-graph-up"></i></span>
                    <span class="nav-label">HR Reports</span>
                </a>
                <a href="{{ route('admin.reports.audit-logs') }}" class="sidebar-link {{ request()->routeIs('admin.reports.audit-logs') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-journal-text"></i></span>
                    <span class="nav-label">Audit Logs</span>
                </a>
                @endif

                <div class="nav-section-title">Organisation</div>

                @if(\App\Models\ServicePermission::canAccess('departments', $svcUser))
                <a href="{{ route('admin.departments.index') }}" class="sidebar-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-building"></i></span>
                    <span class="nav-label">Departments</span>
                </a>
                @endif
                @if(\App\Models\ServicePermission::canAccess('holidays', $svcUser))
                <a href="{{ route('admin.holidays.index') }}" class="sidebar-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-calendar-heart"></i></span>
                    <span class="nav-label">Holidays</span>
                </a>
                @endif
                @if(\App\Models\ServicePermission::canAccess('projects', $svcUser))
                <a href="{{ route('admin.projects.index') }}" class="sidebar-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-diagram-3"></i></span>
                    <span class="nav-label">Projects</span>
                </a>
                @endif

                <div class="nav-section-title">System</div>

                @if(\App\Models\ServicePermission::canAccess('queue', $svcUser))
                <a href="{{ route('admin.queue.index') }}" class="sidebar-link {{ request()->routeIs('admin.queue.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-cpu"></i></span>
                    <span class="nav-label">Queue Monitor</span>
                    @php $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')->count(); @endphp
                    @if($failedJobs > 0)<span class="badge bg-danger">{{ $failedJobs }}</span>@endif
                </a>
                @endif
                @if(\App\Models\ServicePermission::canAccess('settings', $svcUser))
                <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-gear"></i></span>
                    <span class="nav-label">Settings</span>
                </a>
                @endif
                @if($svcUser->isSuperAdmin())
                <a href="{{ route('admin.wallets.index') }}" class="sidebar-link {{ request()->routeIs('admin.wallets.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-wallet2"></i></span>
                    <span class="nav-label">Wallet</span>
                </a>
                <a href="{{ route('admin.roles.index') }}" class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-person-badge"></i></span>
                    <span class="nav-label">Roles</span>
                </a>
                <a href="{{ route('admin.permissions.index') }}" class="sidebar-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-shield-lock"></i></span>
                    <span class="nav-label">Permissions</span>
                </a>
                @endif

            @else

                <div class="nav-section-title">My Workspace</div>

                <a href="{{ route('employee.dashboard') }}" class="sidebar-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="{{ route('documents.index') }}" class="sidebar-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-folder2-open"></i></span>
                    <span class="nav-label">Document</span>
                </a>
                <a href="{{ route('questions.index') }}" class="sidebar-link {{ request()->routeIs('questions.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-patch-question"></i></span>
                    <span class="nav-label">Q&amp;A</span>
                </a>
                <a href="{{ route('employee.attendance.index') }}" class="sidebar-link {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-clock-history"></i></span>
                    <span class="nav-label">Attendance</span>
                </a>
                <a href="{{ route('employee.tasks.index') }}" class="sidebar-link {{ request()->routeIs('employee.tasks.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-list-task"></i></span>
                    <span class="nav-label">My Tasks</span>
                </a>
                <a href="{{ route('employee.work-reports.index') }}" class="sidebar-link {{ request()->routeIs('employee.work-reports.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span>
                    <span class="nav-label">Work Reports</span>
                </a>
                <a href="{{ route('employee.attendance.leaves') }}" class="sidebar-link {{ request()->routeIs('employee.attendance.leaves') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-calendar-x"></i></span>
                    <span class="nav-label">Leave Requests</span>
                </a>
                <a href="{{ route('employee.wallet.index') }}" class="sidebar-link {{ request()->routeIs('employee.wallet.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-wallet2"></i></span>
                    <span class="nav-label">Wallet</span>
                </a>


            @endif
        </div>

        {{-- Footer User Card --}}
        <div class="sidebar-footer">
            <div class="sidebar-user-card">
                <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="32" height="32" alt="">
                <div style="flex:1; min-width:0;">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout">
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

            <div class="d-none d-md-flex align-items-center topbar-breadcrumb-wrap">
                <a href="{{ auth()->user()->isEmployee() ? route('employee.dashboard') : route('admin.dashboard') }}"
                   class="bc-home-btn" title="Dashboard">
                    <i class="bi bi-house-fill"></i>
                </a>
                @hasSection('breadcrumb')
                <ol class="breadcrumb">
                    @yield('breadcrumb')
                </ol>
                @endif
            </div>

            {{-- Global Search --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <div class="position-relative d-none d-lg-flex ms-2" style="width:260px;" id="globalSearchWrapper">
                <div class="input-group" style="height:34px;">
                    <span class="input-group-text border-end-0" style="background:var(--bs-tertiary-bg,#f3f4f6); border:1px solid var(--bs-border-color); border-radius:10px 0 0 10px; padding:0 10px;">
                        <i class="bi bi-search" style="color:#9ca3af; font-size:.8rem;"></i>
                    </span>
                    <input type="text" id="globalSearch"
                           class="form-control border-start-0 ps-0"
                           style="background:var(--bs-tertiary-bg,#f3f4f6); border:1px solid var(--bs-border-color); border-left:none; border-radius:0 10px 10px 0; font-size:.8rem; height:34px;"
                           placeholder="Search..." autocomplete="off">
                </div>
                <div id="searchDropdown" class="dropdown-menu w-100 shadow-sm p-0 mt-1"
                     style="display:none; max-height:400px; overflow-y:auto; border-radius:12px; border:1px solid #e5e7eb;"></div>
            </div>
            @endif

            <div class="ms-auto d-flex align-items-center gap-2">
                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="topbar-action-btn" data-bs-toggle="dropdown" title="Language"
                            style="width:auto; padding:0 10px; gap:5px; border:1px solid var(--bs-border-color); background:var(--bs-body-bg); border-radius:10px; height:34px; display:flex; align-items:center; font-size:.78rem; font-weight:600; color:#6b7280; cursor:pointer;">
                        <i class="bi bi-translate" style="font-size:.85rem;"></i>
                        <span class="d-none d-md-inline">{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:12px; border:1px solid #e5e7eb; font-size:.83rem; min-width:140px;">
                        <li><a class="dropdown-item rounded-2 {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="?lang=en">&#127468;&#127463; English</a></li>
                        <li><a class="dropdown-item rounded-2 {{ app()->getLocale() === 'es' ? 'active' : '' }}" href="?lang=es">&#127466;&#127480; Español</a></li>
                    </ul>
                </div>

                <!-- Theme Toggle -->
                <button class="topbar-action-btn" id="themeToggle" title="Toggle Theme">
                    <i class="bi bi-sun-fill" id="themeIcon"></i>
                </button>

                <!-- Notifications -->
                <div class="dropdown notif-bell">
                    <button class="topbar-action-btn" data-bs-toggle="dropdown" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="notif-count" id="notifCount" style="display:none;"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm" style="width:360px; max-height:420px; overflow-y:auto; border-radius:14px; border:1px solid #e5e7eb;">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <span style="font-size:.82rem; font-weight:700; color:#111827;">Notifications</span>
                            <button class="btn btn-link btn-sm text-decoration-none p-0" style="font-size:.75rem; color:#4f46e5;" onclick="markAllRead()">Mark all read</button>
                        </div>
                        <div id="notifList">
                            <div class="text-muted text-center py-4" style="font-size:.82rem;">Loading...</div>
                        </div>
                    </div>
                </div>

                <div style="width:1px; height:22px; background:var(--bs-border-color); margin:0 2px;"></div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="d-flex align-items-center gap-2 border-0 bg-transparent" data-bs-toggle="dropdown"
                            style="cursor:pointer; padding:4px 8px; border-radius:10px; transition:background .15s;"
                            onmouseenter="this.style.background='rgba(0,0,0,.05)'" onmouseleave="this.style.background='transparent'">
                        <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="30" height="30" alt=""
                             style="border: 2px solid #e5e7eb;">
                        <div class="d-none d-md-block text-start" style="line-height:1.2;">
                            <div style="font-size:.8rem; font-weight:700; color:#111827;">{{ auth()->user()->name }}</div>
                            <div style="font-size:.68rem; color:#9ca3af;">{{ ucwords(str_replace('_',' ', auth()->user()->role)) }}</div>
                        </div>
                        <i class="bi bi-chevron-down d-none d-md-inline" style="font-size:.6rem; color:#9ca3af;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:14px; border:1px solid #e5e7eb; min-width:200px; font-size:.83rem;">
                        <li class="px-3 py-2" style="border-bottom:1px solid #f3f4f6;">
                            <div style="font-weight:700; font-size:.82rem; color:#111827;">{{ auth()->user()->name }}</div>
                            <div style="font-size:.72rem; color:#9ca3af;">{{ auth()->user()->email }}</div>
                        </li>
                        @if(auth()->user()->isEmployee())
                        <li><a class="dropdown-item rounded-2 mt-1" href="{{ route('employee.profile') }}">
                            <i class="bi bi-person me-2 text-indigo-600"></i>My Profile</a></li>
                        @endif
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item rounded-2 text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
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
        $.get('/notifications', function(res) {
            if (res.success) {
                const count = res.unread_count;
                const badge = document.getElementById('notifCount');
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'block';
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                }

                const list = document.getElementById('notifList');
                if (!res.data || res.data.length === 0) {
                    list.innerHTML = '<div class="dropdown-item text-muted small text-center py-3"><i class="bi bi-bell-slash me-1"></i>No notifications</div>';
                    return;
                }

                list.innerHTML = res.data.slice(0, 10).map(n => `
                    <a class="dropdown-item py-2 ${n.is_read ? '' : 'bg-primary bg-opacity-10'}"
                       href="${n.link || '#'}" onclick="markRead(event,${n.id})">
                        <div class="d-flex gap-2 align-items-start">
                            <div class="text-${getNotifColor(n.type)} mt-1" style="flex-shrink:0;">
                                <i class="bi bi-${n.icon || 'bell'}"></i>
                            </div>
                            <div style="min-width:0;">
                                <div class="small fw-semibold" style="white-space:normal;">${n.title}</div>
                                <div class="small text-muted" style="white-space:normal;font-size:.75rem;">${n.message}</div>
                                <div class="text-muted" style="font-size:.7rem;">${new Date(n.created_at).toLocaleString()}</div>
                            </div>
                            ${!n.is_read ? '<span style="width:7px;height:7px;border-radius:50%;background:#4f46e5;flex-shrink:0;margin-top:5px;"></span>' : ''}
                        </div>
                    </a>
                `).join('');
            }
        }).fail(function() {
            document.getElementById('notifList').innerHTML =
                '<div class="dropdown-item text-muted small text-center py-3">Could not load notifications</div>';
        });
    }

    function getNotifColor(type) {
        return { success: 'success', warning: 'warning', danger: 'danger', fraud: 'danger', task: 'info' }[type] || 'primary';
    }

    function markRead(e, id) {
        $.post(`/notifications/${id}/read`, { _token: APP.csrfToken });
        // Don't prevent navigation — let href handle it
    }

    function markAllRead() {
        $.post('/notifications/read-all', { _token: APP.csrfToken }, () => loadNotifications());
    }

    // Load notifications on page load + poll every 60 seconds
    loadNotifications();
    setInterval(loadNotifications, 60000);

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

    // ── Session Timeout (30-minute inactivity auto-logout) ───────────────────
    (function () {
        const TIMEOUT_MS = 30 * 60 * 1000; // 30 minutes
        const WARN_MS    = 2  * 60 * 1000; // warn 2 minutes before
        let timer, warnTimer, warnShown = false;

        function resetTimer() {
            clearTimeout(timer);
            clearTimeout(warnTimer);
            if (warnShown) { Swal.close(); warnShown = false; }

            warnTimer = setTimeout(() => {
                warnShown = true;
                Swal.fire({
                    icon: 'warning',
                    title: 'Session Expiring',
                    html: 'Your session will expire due to inactivity in <strong>2 minutes</strong>.<br>Click OK to stay logged in.',
                    confirmButtonText: 'Stay Logged In',
                    showCancelButton: true,
                    cancelButtonText: 'Logout Now',
                    timer: WARN_MS,
                    timerProgressBar: true,
                }).then(result => {
                    warnShown = false;
                    if (result.isDismissed && result.dismiss !== Swal.DismissReason.cancel) {
                        resetTimer(); // user clicked OK or interacted
                    } else if (result.dismiss === Swal.DismissReason.cancel || result.dismiss === Swal.DismissReason.timer) {
                        document.getElementById('sessionLogoutForm').submit();
                    }
                });
            }, TIMEOUT_MS - WARN_MS);

            timer = setTimeout(() => {
                document.getElementById('sessionLogoutForm').submit();
            }, TIMEOUT_MS);
        }

        ['mousemove','keydown','click','scroll','touchstart'].forEach(evt =>
            document.addEventListener(evt, resetTimer, { passive: true })
        );
        resetTimer();
    })();
    </script>

    {{-- Hidden logout form for session timeout --}}
    <form id="sessionLogoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">
        @csrf
    </form>

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
