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
            /* Theme palette */
            --primary:      #4f46e5;
            --primary-dark: #4338ca;
            --primary-soft: rgba(79,70,229,.10);
            --primary-grad: linear-gradient(135deg,#4f46e5,#7c3aed);
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

        /* ── Desktop Sidebar Collapse (icon-only mode — desktop only) ── */
        @media (min-width: 769px) {
            .sidebar.sidebar-collapsed { width: 64px; }
            .sidebar.sidebar-collapsed .brand-name,
            .sidebar.sidebar-collapsed .brand-badge { display: none !important; }
            .sidebar.sidebar-collapsed .sidebar-brand { justify-content: center; padding: 0; }
            .sidebar.sidebar-collapsed .nav-section-title { display: none !important; }
            .sidebar.sidebar-collapsed .nav-group-btn { display: none !important; }
            .sidebar.sidebar-collapsed .nav-group-collapse {
                display: block !important; height: auto !important; overflow: visible !important;
            }
            .sidebar.sidebar-collapsed .sidebar-link {
                justify-content: center; margin: 1px 4px; padding: 9px 4px;
            }
            .sidebar.sidebar-collapsed .sidebar-link .nav-label,
            .sidebar.sidebar-collapsed .sidebar-link .badge { display: none !important; }
            .sidebar.sidebar-collapsed .sidebar-link .nav-icon { margin: 0; }
            .sidebar.sidebar-collapsed .sidebar-footer { padding: 8px 6px; }
            .sidebar.sidebar-collapsed .sidebar-user-card { justify-content: center; padding: 8px 4px; }
            .sidebar.sidebar-collapsed .sidebar-user-card .user-name,
            .sidebar.sidebar-collapsed .sidebar-user-card .user-role { display: none !important; }
            .sidebar.sidebar-collapsed .sidebar-user-card .logout-btn { margin-left: 0; }
            .main-wrapper.sidebar-collapsed { margin-left: 64px; }
        }

        /* ── Mobile overlay backdrop ── */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 999;
        }
        @media (max-width: 768px) {
            .sidebar-backdrop.show { display: block; }
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

        /* ── Card Glass (modern form container) ── */
        .card-glass {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
        }
        [data-bs-theme="dark"] .card-glass {
            background: #1a1d27;
            border-color: rgba(255,255,255,.08);
            box-shadow: 0 2px 12px rgba(0,0,0,.3);
        }
        .form-section-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: .75rem;
        }

        /* ── KPI Stat Cards ── */
        .kpi-card {
            border-radius: 16px;
            padding: 20px 16px 18px;
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(0,0,0,.15);
            transition: transform .18s, box-shadow .18s;
        }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 32px rgba(0,0,0,.22); }
        .kpi-card::after {
            content: '';
            position: absolute;
            top: -18px; right: -18px;
            width: 72px; height: 72px;
            background: rgba(255,255,255,.13);
            border-radius: 50%;
            pointer-events: none;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            bottom: -24px; left: -12px;
            width: 90px; height: 90px;
            background: rgba(255,255,255,.07);
            border-radius: 50%;
            pointer-events: none;
        }
        .kpi-icon {
            font-size: 1.55rem;
            margin-bottom: 10px;
            display: block;
            opacity: .92;
            position: relative;
            z-index: 1;
        }
        .kpi-value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 6px;
            position: relative;
            z-index: 1;
            letter-spacing: -.5px;
        }
        .kpi-label {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            opacity: .88;
            position: relative;
            z-index: 1;
        }

        /* ── Global Flat Form Style ── */

        /* Label */
        .form-label {
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
            display: block;
        }
        /* Append colon to all .form-label elements (not section labels or check labels) */
        .form-label:not(.form-section-label):not(.form-check-label)::after {
            content: ' :';
            color: #9ca3af;
            font-weight: 400;
        }

        /* Input & Select — flat style */
        .form-control,
        .form-select {
            background-color: #f5f7fa !important;
            border: 1.5px solid #e5e7eb !important;
            border-radius: 9px !important;
            font-size: .875rem !important;
            color: #1f2937 !important;
            padding: 9px 13px !important;
            height: auto !important;
            min-height: 42px;
            transition: border-color .18s, box-shadow .18s, background .18s !important;
            box-shadow: none !important;
        }
        .form-control:focus,
        .form-select:focus {
            background-color: #fff !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px var(--primary-soft) !important;
            outline: none !important;
        }
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc2626 !important;
            background-color: #fef2f2 !important;
        }
        textarea.form-control {
            min-height: 90px;
            resize: vertical;
        }

        /* Input group text */
        .input-group-text {
            background-color: #eef0f5 !important;
            border: 1.5px solid #e5e7eb !important;
            color: #6b7280 !important;
            font-size: .875rem;
            border-radius: 9px 0 0 9px !important;
        }
        .input-group .form-control:last-child,
        .input-group .form-select:last-child {
            border-radius: 0 9px 9px 0 !important;
        }
        .input-group .form-control:not(:first-child),
        .input-group .form-select:not(:first-child) {
            border-left: none !important;
        }

        /* Card-glass form header */
        .card-glass-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 14px;
            margin-bottom: 20px;
            border-bottom: 1.5px solid #f3f4f6;
        }
        .card-glass-header .card-glass-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
        }
        .card-glass-header .card-glass-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--primary-grad);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: .9rem;
            flex-shrink: 0;
        }

        /* ── Sidebar Accordion Group Buttons ── */
        .nav-group-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: calc(100% - 20px);
            margin: 6px 10px 2px;
            padding: 9px 12px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 10px;
            color: #c4cad6;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
            text-align: left;
            transition: background .18s, border-color .18s;
            outline: none;
        }
        .nav-group-btn:hover {
            background: rgba(255,255,255,.08);
            border-color: rgba(255,255,255,.12);
            color: #e2e8f0;
        }
        .nav-group-left {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .nav-group-hamburger {
            font-size: 1rem;
            color: rgba(255,255,255,.35);
            flex-shrink: 0;
        }
        .nav-group-icon {
            font-size: .95rem;
            flex-shrink: 0;
        }
        .nav-group-label {
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .4px;
            color: #e2e8f0;
            text-transform: uppercase;
            flex-shrink: 0;
        }
        .nav-group-sub {
            font-size: .65rem;
            font-weight: 500;
            color: rgba(255,255,255,.35);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .nav-group-chevron {
            font-size: .7rem;
            color: rgba(255,255,255,.35);
            flex-shrink: 0;
            transition: transform .25s ease;
        }
        .nav-group-btn.collapsed .nav-group-chevron {
            transform: rotate(-90deg);
        }
        .nav-group-collapse {
            /* no extra padding; sidebar-link handles its own margin */
        }

        /* ── Sub-section titles within groups ── */
        .nav-sub-title {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,.22);
            font-size: .58rem;
            font-weight: 700;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            padding: 12px 22px 4px;
        }
        .nav-sub-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,.05);
        }
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
            @if($svcUser->hasAdminAccess())

                {{-- Personal workspace for managers and admins who are also employees --}}
                @if($svcUser->isManager() || $svcUser->employee)
                <div class="nav-section-title">My Workspace</div>
                @if($svcUser->isManager())
                <a href="{{ route('admin.manager.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.manager.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-person-circle"></i></span>
                    <span class="nav-label">My Dashboard</span>
                </a>
                @else
                <a href="{{ route('employee.dashboard') }}" class="sidebar-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-person-circle"></i></span>
                    <span class="nav-label">My Dashboard</span>
                </a>
                @endif
                <a href="{{ route('employee.attendance.index') }}" class="sidebar-link {{ request()->routeIs('employee.attendance.index') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-fingerprint"></i></span>
                    <span class="nav-label">My Attendance</span>
                </a>
                <a href="{{ route('employee.tasks.index') }}" class="sidebar-link {{ request()->routeIs('employee.tasks.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-list-task"></i></span>
                    <span class="nav-label">My Tasks</span>
                </a>
                <a href="{{ route('employee.attendance.leaves') }}" class="sidebar-link {{ request()->routeIs('employee.attendance.leaves') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-calendar-x"></i></span>
                    <span class="nav-label">My Leaves</span>
                </a>
                <a href="{{ route('employee.work-reports.index') }}" class="sidebar-link {{ request()->routeIs('employee.work-reports.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="bi bi-file-earmark-text"></i></span>
                    <span class="nav-label">My Work Reports</span>
                </a>
                @endif

                @php
                    $isErpActive = request()->routeIs('admin.dashboard') || request()->routeIs('documents.*') || request()->routeIs('questions.*') || request()->routeIs('admin.transactions.*') || request()->routeIs('admin.animals.*') || request()->routeIs('admin.milk.*') || request()->routeIs('admin.breeding.*') || request()->routeIs('admin.health.*') || request()->routeIs('admin.feed.*') || request()->routeIs('admin.farm.*') || request()->routeIs('admin.expenses.*') || request()->routeIs('admin.stock.*') || request()->routeIs('admin.maintenance.*') || request()->routeIs('admin.compliance.*') || request()->routeIs('admin.reports.center');
                    $isHrActive  = request()->routeIs('admin.users.*') || request()->routeIs('admin.employees.*') || request()->routeIs('admin.salaries.*') || request()->routeIs('admin.attendance.*') || request()->routeIs('admin.tasks.*') || request()->routeIs('admin.work-reports.*') || request()->routeIs('admin.timesheets.*') || request()->routeIs('admin.teams.*') || request()->routeIs('admin.shifts.*') || request()->routeIs('admin.departments.*') || request()->routeIs('admin.holidays.*') || request()->routeIs('admin.projects.*') || request()->routeIs('admin.queue.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.wallets.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*');
                    $isCrmActive = request()->routeIs('admin.crm.*') || request()->routeIs('admin.franchise.*') || request()->routeIs('admin.procurement.*') || request()->routeIs('admin.vendors.*') || request()->routeIs('admin.sales.*') || request()->routeIs('admin.contacts.*') || request()->routeIs('admin.contact-categories.*');
                    if (!$isErpActive && !$isHrActive && !$isCrmActive) $isErpActive = true;
                @endphp

                {{-- ══════════════════════════════════════════
                     GROUP 1 — ERP (Dairy Farm Operations)
                ══════════════════════════════════════════ --}}
                <button class="nav-group-btn {{ $isErpActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#grpErp"
                        aria-expanded="{{ $isErpActive ? 'true' : 'false' }}">
                    <span class="nav-group-left">
                        <i class="bi bi-list nav-group-hamburger"></i>
                        <i class="bi bi-buildings-fill nav-group-icon text-success"></i>
                        <span class="nav-group-label">ERP</span>
                        <span class="nav-group-sub">Dairy Farm Operations</span>
                    </span>
                    <i class="bi bi-chevron-down nav-group-chevron"></i>
                </button>
                <div id="grpErp" class="nav-group-collapse collapse {{ $isErpActive ? 'show' : '' }}">
                    @if($svcUser->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                    @elseif(!$svcUser->isManager())
                    <a href="{{ route('admin.role-dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.role-dashboard') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                    @endif
                    <a href="{{ route('documents.index') }}" class="sidebar-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-folder2-open"></i></span>
                        <span class="nav-label">Documents</span>
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
                    <div class="nav-sub-title">Animal & Farm</div>
                    @if(\App\Models\ServicePermission::canAccess('animals', $svcUser))
                    <a href="{{ route('admin.animals.index') }}" class="sidebar-link {{ request()->routeIs('admin.animals.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-card-checklist"></i></span>
                        <span class="nav-label">Animals</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('breeds', $svcUser))
                    <a href="{{ route('admin.breeds.index') }}" class="sidebar-link {{ request()->routeIs('admin.breeds.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-collection"></i></span>
                        <span class="nav-label">Breeds</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('milk', $svcUser))
                    <a href="{{ route('admin.milk.index') }}" class="sidebar-link {{ request()->routeIs('admin.milk.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-droplet-fill"></i></span>
                        <span class="nav-label">Milk</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('breeding', $svcUser))
                    <a href="{{ route('admin.breeding.index') }}" class="sidebar-link {{ request()->routeIs('admin.breeding.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-heart-pulse"></i></span>
                        <span class="nav-label">Breeding</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('health', $svcUser))
                    <a href="{{ route('admin.health.index') }}" class="sidebar-link {{ request()->routeIs('admin.health.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-hospital"></i></span>
                        <span class="nav-label">Health</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('feed', $svcUser))
                    <a href="{{ route('admin.feed.calculator') }}" class="sidebar-link {{ request()->routeIs('admin.feed.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-cpu"></i></span>
                        <span class="nav-label">Feed</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('farm', $svcUser))
                    <a href="{{ route('admin.farm.index') }}" class="sidebar-link {{ request()->routeIs('admin.farm.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-tree"></i></span>
                        <span class="nav-label">Farm</span>
                    </a>
                    @endif
                    <div class="nav-sub-title">Finance & Stock</div>
                    @if(\App\Models\ServicePermission::canAccess('expenses', $svcUser))
                    <a href="{{ route('admin.expenses.index') }}" class="sidebar-link {{ request()->routeIs('admin.expenses.*') && !request()->routeIs('admin.expense-categories.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-receipt"></i></span>
                        <span class="nav-label">Expenses</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('expense_categories', $svcUser))
                    <a href="{{ route('admin.expense-categories.index') }}" class="sidebar-link {{ request()->routeIs('admin.expense-categories.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-tags"></i></span>
                        <span class="nav-label">Expense Categories</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('stock', $svcUser))
                    <a href="{{ route('admin.stock.index') }}" class="sidebar-link {{ request()->routeIs('admin.stock.*') || request()->routeIs('admin.stock-items.*') || request()->routeIs('admin.stock-categories.*') || request()->routeIs('admin.stock-types.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-boxes"></i></span>
                        <span class="nav-label">Stock</span>
                    </a>
                    @endif
                    <div class="nav-sub-title">Operations</div>
                    @if(\App\Models\ServicePermission::canAccess('maintenance', $svcUser))
                    <a href="{{ route('admin.maintenance.index') }}" class="sidebar-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-tools"></i></span>
                        <span class="nav-label">Maintenance</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('compliance', $svcUser))
                    <a href="{{ route('admin.compliance.index') }}" class="sidebar-link {{ request()->routeIs('admin.compliance.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-shield-check"></i></span>
                        <span class="nav-label">Compliance</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('report_center', $svcUser))
                    <a href="{{ route('admin.reports.center') }}" class="sidebar-link {{ request()->routeIs('admin.reports.center') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-file-earmark-bar-graph"></i></span>
                        <span class="nav-label">Reports Center</span>
                    </a>
                    @endif
                </div>

                {{-- ══════════════════════════════════════════
                     GROUP 2 — HR (Employee Management)
                ══════════════════════════════════════════ --}}
                <button class="nav-group-btn {{ $isHrActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#grpHr"
                        aria-expanded="{{ $isHrActive ? 'true' : 'false' }}">
                    <span class="nav-group-left">
                        <i class="bi bi-list nav-group-hamburger"></i>
                        <i class="bi bi-person-badge-fill nav-group-icon text-primary"></i>
                        <span class="nav-group-label">HR</span>
                        <span class="nav-group-sub">Employee Management</span>
                    </span>
                    <i class="bi bi-chevron-down nav-group-chevron"></i>
                </button>
                <div id="grpHr" class="nav-group-collapse collapse {{ $isHrActive ? 'show' : '' }}">
                    <div class="nav-sub-title">People</div>
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
                    @if(\App\Models\ServicePermission::canAccess('salaries', $svcUser))
                    <a href="{{ route('admin.salaries.index') }}" class="sidebar-link {{ request()->routeIs('admin.salaries.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-wallet2"></i></span>
                        <span class="nav-label">Salary & Payroll</span>
                    </a>
                    @endif
                    <div class="nav-sub-title">Work Tracking</div>
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
                    <div class="nav-sub-title">Organisation</div>
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
                    <div class="nav-sub-title">Reports & System</div>
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
                </div>

                {{-- ══════════════════════════════════════════
                     GROUP 3 — CRM (Customer Related)
                ══════════════════════════════════════════ --}}
                <button class="nav-group-btn {{ $isCrmActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#grpCrm"
                        aria-expanded="{{ $isCrmActive ? 'true' : 'false' }}">
                    <span class="nav-group-left">
                        <i class="bi bi-list nav-group-hamburger"></i>
                        <i class="bi bi-handshake-fill nav-group-icon text-warning"></i>
                        <span class="nav-group-label">CRM</span>
                        <span class="nav-group-sub">Customer Related</span>
                    </span>
                    <i class="bi bi-chevron-down nav-group-chevron"></i>
                </button>
                <div id="grpCrm" class="nav-group-collapse collapse {{ $isCrmActive ? 'show' : '' }}">
                    @if(\App\Models\ServicePermission::canAccess('contacts', $svcUser))
                    <a href="{{ route('admin.contacts.index') }}" class="sidebar-link {{ request()->routeIs('admin.contacts.*') || request()->routeIs('admin.contact-categories.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-person-lines-fill"></i></span>
                        <span class="nav-label">Contacts</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('crm', $svcUser))
                    <a href="{{ route('admin.crm.index') }}" class="sidebar-link {{ request()->routeIs('admin.crm.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
                        <span class="nav-label">Customers</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('franchise', $svcUser))
                    <a href="{{ route('admin.franchise.index') }}" class="sidebar-link {{ request()->routeIs('admin.franchise.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-shop"></i></span>
                        <span class="nav-label">Franchise</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('procurement', $svcUser))
                    <a href="{{ route('admin.procurement.index') }}" class="sidebar-link {{ request()->routeIs('admin.procurement.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-cart-check"></i></span>
                        <span class="nav-label">Procurement</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('vendors', $svcUser))
                    <a href="{{ route('admin.vendors.index') }}" class="sidebar-link {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-building"></i></span>
                        <span class="nav-label">Vendors</span>
                    </a>
                    @endif
                    @if(\App\Models\ServicePermission::canAccess('sales', $svcUser))
                    <a href="{{ route('admin.sales.index') }}" class="sidebar-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="bi bi-cash-coin"></i></span>
                        <span class="nav-label">Sales</span>
                    </a>
                    @endif
                </div>

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

    <!-- Mobile sidebar backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            {{-- Sidebar toggle — works on all screen sizes --}}
            <button class="topbar-action-btn me-1" id="sidebarCollapseBtn" title="Toggle Sidebar">
                <i class="bi bi-layout-sidebar" id="sidebarCollapseIcon"></i>
            </button>

            <div class="d-none d-md-flex align-items-center topbar-breadcrumb-wrap">
                <a href="{{ auth()->user()->getDashboardRoute() }}"
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
            @if(auth()->user()->hasAdminAccess())
            {{-- Mobile search trigger (icon button, hidden on lg+) --}}
            <button class="topbar-action-btn d-lg-none" id="mobileSearchBtn" title="Search">
                <i class="bi bi-search" style="font-size:.9rem;"></i>
            </button>
            <div class="position-relative d-none d-lg-block ms-2" style="width:280px;" id="globalSearchWrapper">
                <div class="input-group" style="height:36px;">
                    <span class="input-group-text border-end-0" style="background:var(--bs-tertiary-bg,#f3f4f6); border:1px solid var(--bs-border-color); border-radius:10px 0 0 10px; padding:0 10px;">
                        <i class="bi bi-search" style="color:#9ca3af; font-size:.8rem;"></i>
                    </span>
                    <input type="text" id="globalSearch"
                           class="form-control border-start-0 ps-0"
                           style="background:var(--bs-tertiary-bg,#f3f4f6); border:1px solid var(--bs-border-color); border-left:none; border-radius:0 10px 10px 0; font-size:.8rem; height:36px;"
                           placeholder="Search animals, expenses, users..." autocomplete="off">
                </div>
                {{-- Explicit absolute positioning — no Popper dependency --}}
                <div id="searchDropdown"
                     style="display:none; position:absolute; top:calc(100% + 6px); left:0; right:0;
                            background:#fff; border-radius:14px; border:1px solid #e5e7eb;
                            box-shadow:0 8px 32px rgba(0,0,0,.13); max-height:420px;
                            overflow-y:auto; z-index:9999;"></div>
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
                        <div style="border-top:1px solid #f3f4f6; padding:8px 12px;">
                            <a href="{{ route('notifications.all') }}"
                               style="display:block; text-align:center; font-size:.78rem; font-weight:600; color:#4f46e5; text-decoration:none; padding:6px; border-radius:8px; transition:background .15s;"
                               onmouseover="this.style.background='#f0f0ff'" onmouseout="this.style.background=''">
                                <i class="bi bi-clock-history me-1"></i>View All Notifications
                            </a>
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

            @if(auth()->user()->hasAdminAccess())
            {{-- Mobile Search Overlay (covers the topbar on small screens) --}}
            <div id="mobileSearchOverlay"
                 style="display:none; position:absolute; inset:0; background:var(--bs-body-bg);
                        z-index:200; padding:0 14px; align-items:center; gap:10px;
                        border-bottom:1px solid var(--bs-border-color);">
                <i class="bi bi-search" style="color:#9ca3af; font-size:.88rem; flex-shrink:0;"></i>
                <input type="text" id="mobileGlobalSearch" autocomplete="off"
                       style="flex:1; border:none; background:transparent; outline:none; font-size:.88rem; color:inherit;"
                       placeholder="Search animals, transactions, documents…">
                <button id="mobileSearchClose"
                        style="background:none; border:none; color:#9ca3af; font-size:1rem; cursor:pointer; padding:4px 6px; flex-shrink:0; line-height:1;">
                    <i class="bi bi-x-lg"></i>
                </button>
                {{-- Results dropdown (full width, below overlay) --}}
                <div id="mobileSearchDropdown"
                     style="display:none; position:absolute; top:100%; left:0; right:0;
                            background:#fff; border-radius:0 0 14px 14px; border:1px solid #e5e7eb;
                            border-top:none; box-shadow:0 8px 32px rgba(0,0,0,.13);
                            max-height:380px; overflow-y:auto; z-index:9999;"></div>
            </div>
            @endif
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

    // ── Unified Sidebar Toggle (mobile slide + desktop icon-only) ────────────
    (function () {
        const sidebar     = document.getElementById('sidebar');
        const mainWrapper = document.querySelector('.main-wrapper');
        const backdrop    = document.getElementById('sidebarBackdrop');
        const btn         = document.getElementById('sidebarCollapseBtn');
        const icon        = document.getElementById('sidebarCollapseIcon');

        const isMobile = () => window.innerWidth <= 768;

        // ── Desktop: icon-only collapse ──────────────────────────────────────
        function applyDesktopCollapsed(collapsed) {
            sidebar.classList.toggle('sidebar-collapsed', collapsed);
            mainWrapper.classList.toggle('sidebar-collapsed', collapsed);
            if (icon) icon.className = collapsed ? 'bi bi-layout-sidebar-reverse' : 'bi bi-layout-sidebar';
        }

        function enableTooltips() {
            document.querySelectorAll('.sidebar-link').forEach(link => {
                const label = link.querySelector('.nav-label')?.textContent?.trim();
                if (label) {
                    link.setAttribute('data-bs-toggle', 'tooltip');
                    link.setAttribute('data-bs-placement', 'right');
                    link.setAttribute('title', label);
                    new bootstrap.Tooltip(link, { trigger: 'hover' });
                }
            });
        }
        function disableTooltips() {
            document.querySelectorAll('.sidebar-link[data-bs-toggle="tooltip"]').forEach(link => {
                bootstrap.Tooltip.getInstance(link)?.dispose();
                link.removeAttribute('data-bs-toggle');
                link.removeAttribute('title');
            });
        }

        // ── Mobile: slide in/out with backdrop ───────────────────────────────
        function openMobileSidebar() {
            sidebar.classList.add('show');
            backdrop?.classList.add('show');
            if (icon) icon.className = 'bi bi-x-lg';
        }
        function closeMobileSidebar() {
            sidebar.classList.remove('show');
            backdrop?.classList.remove('show');
            if (icon) icon.className = 'bi bi-layout-sidebar';
        }

        // Restore desktop collapsed state on load (never on mobile)
        if (!isMobile()) {
            const saved = localStorage.getItem('sidebarCollapsed') === '1';
            applyDesktopCollapsed(saved);
            if (saved) enableTooltips();
        }

        // Button click
        btn?.addEventListener('click', function () {
            if (isMobile()) {
                sidebar.classList.contains('show') ? closeMobileSidebar() : openMobileSidebar();
            } else {
                const nowCollapsed = !sidebar.classList.contains('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', nowCollapsed ? '1' : '0');
                applyDesktopCollapsed(nowCollapsed);
                if (nowCollapsed) enableTooltips(); else disableTooltips();
            }
        });

        // Close mobile sidebar when backdrop is tapped
        backdrop?.addEventListener('click', closeMobileSidebar);

        // Close mobile sidebar when a nav link is tapped
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => { if (isMobile()) closeMobileSidebar(); });
        });

        // On resize: clean up mobile state when switching to desktop
        window.addEventListener('resize', () => {
            if (!isMobile()) {
                sidebar.classList.remove('show');
                backdrop?.classList.remove('show');
                if (icon && !sidebar.classList.contains('sidebar-collapsed')) {
                    icon.className = 'bi bi-layout-sidebar';
                }
            } else {
                // Remove desktop collapsed on mobile
                sidebar.classList.remove('sidebar-collapsed');
                mainWrapper.classList.remove('sidebar-collapsed');
                disableTooltips();
            }
        });
    })();

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

    // ── Global Search (shared function for desktop + mobile) ─────────────────
    const colorVars = { primary:'#4f46e5', success:'#059669', info:'#0891b2', warning:'#d97706', danger:'#dc2626', secondary:'#6b7280' };

    function buildSearchResults(res) {
        if (!res.results || !res.results.length) {
            return '<div style="padding:14px 16px;color:#9ca3af;font-size:.8rem;text-align:center;">No results found</div>';
        }
        return res.results.map((r, i) => {
            const clr = colorVars[r.color] || '#6b7280';
            const isLast = i === res.results.length - 1;
            return `<a href="${r.url}" style="display:flex;align-items:flex-start;gap:10px;padding:10px 16px;text-decoration:none;color:#111827;${isLast?'':'border-bottom:1px solid #f3f4f6;'}transition:background .15s;"
                       onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">`
                + `<span style="color:${clr};margin-top:2px;flex-shrink:0;"><i class="bi bi-${r.icon}" style="font-size:1.05rem;"></i></span>`
                + `<div style="min-width:0;">`
                + `<div style="font-size:.82rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${r.title}</div>`
                + `<div style="font-size:.7rem;color:#9ca3af;margin-top:1px;">${r.type} &middot; ${r.subtitle}</div>`
                + `</div></a>`;
        }).join('');
    }

    function wireSearch(inputEl, dropdownEl, wrapperEl) {
        if (!inputEl || !dropdownEl) return;
        let timer;
        inputEl.addEventListener('input', function () {
            clearTimeout(timer);
            const q = this.value.trim();
            if (q.length < 2) { dropdownEl.style.display = 'none'; return; }
            timer = setTimeout(() => {
                $.get('/admin/search', { q }, function (res) {
                    dropdownEl.innerHTML = buildSearchResults(res);
                    dropdownEl.style.display = 'block';
                }).fail(() => { dropdownEl.style.display = 'none'; });
            }, 300);
        });
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { dropdownEl.style.display = 'none'; this.value = ''; }
        });
        document.addEventListener('click', function (e) {
            if (wrapperEl && !wrapperEl.contains(e.target)) dropdownEl.style.display = 'none';
        });
    }

    // Desktop search
    wireSearch(
        document.getElementById('globalSearch'),
        document.getElementById('searchDropdown'),
        document.getElementById('globalSearchWrapper')
    );

    // Mobile search overlay
    const mobileSearchBtn     = document.getElementById('mobileSearchBtn');
    const mobileSearchOverlay = document.getElementById('mobileSearchOverlay');
    const mobileSearchClose   = document.getElementById('mobileSearchClose');
    const mobileSearchInput   = document.getElementById('mobileGlobalSearch');
    const mobileSearchDrop    = document.getElementById('mobileSearchDropdown');

    if (mobileSearchBtn && mobileSearchOverlay) {
        mobileSearchBtn.addEventListener('click', () => {
            mobileSearchOverlay.style.display = 'flex';
            mobileSearchInput?.focus();
        });
        mobileSearchClose?.addEventListener('click', () => {
            mobileSearchOverlay.style.display = 'none';
            if (mobileSearchDrop) mobileSearchDrop.style.display = 'none';
            if (mobileSearchInput) mobileSearchInput.value = '';
        });
        wireSearch(mobileSearchInput, mobileSearchDrop, mobileSearchOverlay);
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
    // Only initialise Echo if the CDN script loaded successfully
    if (typeof LaravelEcho !== 'undefined') {
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
                loadNotifications();
                if (notification.type && notification.type.includes('Fraud')) {
                    APP.toast('New fraud alert detected!', 'warning');
                }
            });

        @if(auth()->user()->hasAdminAccess())
        Echo.private('fraud-alerts')
            .listen('FraudAlertCreated', function (e) {
                loadNotifications();
                APP.toast('Fraud alert: ' + (e.message ?? 'New high-risk transaction detected'), 'warning');
                const badge = document.querySelector('.fraud-alert-badge');
                if (badge) badge.textContent = parseInt(badge.textContent || 0) + 1;
            });

        Echo.channel('transactions')
            .listen('TransactionStatusUpdated', function (e) {
                APP.toast('Transaction ' + e.transaction_id + ' → ' + e.status, 'info');
            });
        @endif
        @endauth
    }
    </script>

    @stack('scripts')
</body>
</html>
