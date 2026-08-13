@extends('layouts.app')
@section('title', 'My Dashboard')

@push('styles')
<style>
/* ── Hero ────────────────────────────────────────────────────── */
.rd-hero {
    background: linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#1e293b 100%);
    border-radius: 20px;
    padding: 32px 36px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.rd-hero::before {
    content:''; position:absolute; top:-70px; right:-50px;
    width:240px; height:240px; border-radius:50%;
    background:rgba(99,102,241,.14); pointer-events:none;
}
.rd-hero::after {
    content:''; position:absolute; bottom:-80px; left:25%;
    width:320px; height:320px; border-radius:50%;
    background:rgba(14,165,233,.07); pointer-events:none;
}
.rd-role-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(255,255,255,.13);
    border:1px solid rgba(255,255,255,.2);
    border-radius:20px; padding:4px 14px;
    font-size:.72rem; color:rgba(255,255,255,.88);
    font-weight:700; text-transform:uppercase; letter-spacing:.07em;
    margin-bottom:10px;
}
.rd-hero-name { font-size:1.75rem; font-weight:800; color:#fff; margin:0 0 5px; line-height:1.2; }
.rd-hero-sub  { font-size:.83rem; color:rgba(255,255,255,.55); margin:0; }
.rd-clock-wrap { text-align:right; position:relative; z-index:1; }
.rd-clock      { font-size:2.1rem; font-weight:800; color:#fff; letter-spacing:.06em; line-height:1; }
.rd-clock-sub  { font-size:.75rem; color:rgba(255,255,255,.5); margin-top:4px; }

/* ── Full-gradient Stat Cards (matching screenshot) ────────── */
.rd-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px;
    margin-bottom: 26px;
}
.rd-stat-card {
    border-radius: 18px;
    padding: 24px 16px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 148px;
    transition: transform .2s, box-shadow .2s;
    cursor: default;
}
.rd-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,.18);
}
/* decorative blobs inside each card */
.rd-stat-card::before {
    content:''; position:absolute;
    width:130px; height:130px; border-radius:50%;
    background:rgba(255,255,255,.09);
    bottom:-35px; right:-30px; pointer-events:none;
}
.rd-stat-card::after {
    content:''; position:absolute;
    width:80px; height:80px; border-radius:50%;
    background:rgba(255,255,255,.07);
    top:-20px; left:-15px; pointer-events:none;
}
.rd-stat-icon-wrap {
    width:52px; height:52px; border-radius:50%;
    background:rgba(255,255,255,.18);
    display:flex; align-items:center; justify-content:center;
    font-size:1.35rem; color:#fff;
    margin-bottom:12px;
    position:relative; z-index:1;
}
.rd-stat-val {
    font-size:2rem; font-weight:800; color:#fff;
    line-height:1; position:relative; z-index:1;
}
.rd-stat-lbl {
    font-size:.68rem; color:rgba(255,255,255,.78);
    text-transform:uppercase; letter-spacing:.1em;
    font-weight:600; margin-top:7px;
    position:relative; z-index:1;
}

/* ── Module Section Header ──────────────────────────────────── */
.rd-mod-header {
    display:flex; align-items:center;
    justify-content:space-between; margin-bottom:16px;
}
.rd-mod-title {
    font-size:1rem; font-weight:700; color:#0f172a;
    display:flex; align-items:center; gap:8px;
}
.rd-mod-count {
    background:#f1f5f9; color:#64748b;
    border-radius:20px; padding:2px 11px;
    font-size:.73rem; font-weight:600;
}

/* ── Module Grid Cards ──────────────────────────────────────── */
.rd-modules {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(210px,1fr));
    gap:14px;
}
.rd-mod-card {
    background:#fff;
    border:1px solid #f1f5f9;
    border-radius:16px;
    padding:20px;
    text-decoration:none;
    display:block;
    position:relative;
    overflow:hidden;
    transition:box-shadow .2s, transform .18s, border-color .2s;
}
.rd-mod-card:hover {
    box-shadow:0 8px 28px rgba(0,0,0,.09);
    transform:translateY(-3px);
    border-color:transparent;
    text-decoration:none;
}
.rd-mod-topbar {
    position:absolute; top:0; left:0; right:0; height:3px;
    border-radius:16px 16px 0 0; opacity:0;
    transition:opacity .22s;
}
.rd-mod-card:hover .rd-mod-topbar { opacity:1; }
.rd-mod-icon-wrap {
    width:46px; height:46px; border-radius:13px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.15rem; margin-bottom:13px;
}
.rd-mod-name {
    font-weight:700; font-size:.88rem; color:#0f172a;
    margin-bottom:5px; line-height:1.3;
}
.rd-mod-desc {
    font-size:.74rem; color:#94a3b8; line-height:1.45;
}
.rd-mod-arrow {
    position:absolute; top:16px; right:15px;
    font-size:.88rem; transition:transform .18s, color .18s;
    color:#cbd5e1;
}
.rd-mod-card:hover .rd-mod-arrow { transform:translate(2px,-2px); }

/* ── Empty ──────────────────────────────────────────────────── */
.rd-empty {
    text-align:center; padding:60px 24px;
    background:#fff; border-radius:16px; border:1px solid #f1f5f9;
}
.rd-empty-icon { font-size:2.8rem; color:#e2e8f0; margin-bottom:12px; }
.rd-empty-title{ font-size:1rem; font-weight:700; color:#475569; margin-bottom:6px; }
.rd-empty-sub  { font-size:.8rem; color:#94a3b8; }

/* ── Dark mode ──────────────────────────────────────────────── */
[data-bs-theme="dark"] .rd-mod-card,
[data-bs-theme="dark"] .rd-empty { background:#1e293b; border-color:#334155; }
[data-bs-theme="dark"] .rd-mod-name  { color:#f1f5f9; }
[data-bs-theme="dark"] .rd-mod-title { color:#f1f5f9; }
[data-bs-theme="dark"] .rd-mod-count { background:#334155; color:#94a3b8; }
</style>
@endpush

@section('content')
@php
    $user      = auth()->user();
    $roleLabel = ucwords(str_replace('_', ' ', $user->role));
    $hour      = (int) now()->format('H');
    $greeting  = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    // ── Service key → route name ─────────────────────────────────
    $serviceRoutes = [
        'transactions'       => 'admin.transactions.index',
        'fraud_alerts'       => 'admin.fraud-alerts.index',
        'wallets'            => 'admin.wallets.index',
        'expenses'           => 'admin.expenses.index',
        'expense_categories' => 'admin.expense-categories.index',
        'salaries'           => 'admin.salaries.index',
        'users'              => 'admin.users.index',
        'employees'          => 'admin.employees.index',
        'departments'        => 'admin.departments.index',
        'teams'              => 'admin.teams.index',
        'attendance'         => 'admin.attendance.index',
        'shifts'             => 'admin.shifts.index',
        'holidays'           => 'admin.holidays.index',
        'tasks'              => 'admin.tasks.index',
        'projects'           => 'admin.projects.index',
        'work_reports'       => 'admin.work-reports.index',
        'timesheets'         => 'admin.timesheets.index',
        'animals'            => 'admin.animals.index',
        'breeds'             => 'admin.breeds.index',
        'milk'               => 'admin.milk.index',
        'breeding'           => 'admin.breeding.index',
        'health'             => 'admin.health.index',
        'feed'               => 'admin.feed.calculator',
        'farm'               => 'admin.farm.index',
        'stock'              => 'admin.stock.index',
        'maintenance'        => 'admin.maintenance.index',
        'compliance'         => 'admin.compliance.index',
        'contacts'           => 'admin.contacts.index',
        'contact_categories' => 'admin.contact-categories.index',
        'crm'                => 'admin.crm.index',
        'franchise'          => 'admin.franchise.index',
        'procurement'        => 'admin.procurement.index',
        'vendors'            => 'admin.vendors.index',
        'sales'              => 'admin.sales.index',
        'reports'            => 'admin.reports.index',
        'report_center'      => 'admin.report-center.index',
        'documents'          => 'documents.index',
        'questions'          => 'questions.index',
        'queue'              => 'admin.queue.index',
        'settings'           => 'admin.settings.index',
        'permissions'        => 'admin.permissions.index',
    ];

    // ── Per-service icon colour + gradient ───────────────────────
    $svcStyle = [
        'transactions'       => ['bg'=>'rgba(99,102,241,.12)',  'color'=>'#6366f1', 'bar'=>'linear-gradient(90deg,#6366f1,#818cf8)'],
        'fraud_alerts'       => ['bg'=>'rgba(239,68,68,.12)',   'color'=>'#ef4444', 'bar'=>'linear-gradient(90deg,#ef4444,#f87171)'],
        'wallets'            => ['bg'=>'rgba(16,185,129,.12)',  'color'=>'#10b981', 'bar'=>'linear-gradient(90deg,#10b981,#34d399)'],
        'expenses'           => ['bg'=>'rgba(245,158,11,.12)',  'color'=>'#f59e0b', 'bar'=>'linear-gradient(90deg,#f59e0b,#fbbf24)'],
        'expense_categories' => ['bg'=>'rgba(245,158,11,.10)',  'color'=>'#d97706', 'bar'=>'linear-gradient(90deg,#d97706,#f59e0b)'],
        'salaries'           => ['bg'=>'rgba(20,184,166,.12)',  'color'=>'#14b8a6', 'bar'=>'linear-gradient(90deg,#14b8a6,#2dd4bf)'],
        'users'              => ['bg'=>'rgba(139,92,246,.12)',  'color'=>'#8b5cf6', 'bar'=>'linear-gradient(90deg,#8b5cf6,#a78bfa)'],
        'employees'          => ['bg'=>'rgba(99,102,241,.12)',  'color'=>'#6366f1', 'bar'=>'linear-gradient(90deg,#6366f1,#818cf8)'],
        'departments'        => ['bg'=>'rgba(59,130,246,.12)',  'color'=>'#3b82f6', 'bar'=>'linear-gradient(90deg,#3b82f6,#60a5fa)'],
        'teams'              => ['bg'=>'rgba(236,72,153,.12)',  'color'=>'#ec4899', 'bar'=>'linear-gradient(90deg,#ec4899,#f472b6)'],
        'attendance'         => ['bg'=>'rgba(14,165,233,.12)',  'color'=>'#0ea5e9', 'bar'=>'linear-gradient(90deg,#0ea5e9,#38bdf8)'],
        'shifts'             => ['bg'=>'rgba(99,102,241,.10)',  'color'=>'#4f46e5', 'bar'=>'linear-gradient(90deg,#4f46e5,#6366f1)'],
        'holidays'           => ['bg'=>'rgba(239,68,68,.10)',   'color'=>'#dc2626', 'bar'=>'linear-gradient(90deg,#dc2626,#ef4444)'],
        'tasks'              => ['bg'=>'rgba(168,85,247,.12)',  'color'=>'#a855f7', 'bar'=>'linear-gradient(90deg,#a855f7,#c084fc)'],
        'projects'           => ['bg'=>'rgba(99,102,241,.12)',  'color'=>'#6366f1', 'bar'=>'linear-gradient(90deg,#6366f1,#818cf8)'],
        'work_reports'       => ['bg'=>'rgba(107,114,128,.12)', 'color'=>'#6b7280', 'bar'=>'linear-gradient(90deg,#6b7280,#9ca3af)'],
        'timesheets'         => ['bg'=>'rgba(20,184,166,.10)',  'color'=>'#0d9488', 'bar'=>'linear-gradient(90deg,#0d9488,#14b8a6)'],
        'animals'            => ['bg'=>'rgba(16,185,129,.12)',  'color'=>'#059669', 'bar'=>'linear-gradient(90deg,#059669,#10b981)'],
        'breeds'             => ['bg'=>'rgba(16,185,129,.10)',  'color'=>'#10b981', 'bar'=>'linear-gradient(90deg,#10b981,#34d399)'],
        'milk'               => ['bg'=>'rgba(14,165,233,.12)',  'color'=>'#0ea5e9', 'bar'=>'linear-gradient(90deg,#0ea5e9,#38bdf8)'],
        'breeding'           => ['bg'=>'rgba(239,68,68,.10)',   'color'=>'#e11d48', 'bar'=>'linear-gradient(90deg,#e11d48,#f43f5e)'],
        'health'             => ['bg'=>'rgba(16,185,129,.12)',  'color'=>'#10b981', 'bar'=>'linear-gradient(90deg,#10b981,#34d399)'],
        'feed'               => ['bg'=>'rgba(132,204,22,.12)',  'color'=>'#65a30d', 'bar'=>'linear-gradient(90deg,#65a30d,#84cc16)'],
        'farm'               => ['bg'=>'rgba(34,197,94,.12)',   'color'=>'#16a34a', 'bar'=>'linear-gradient(90deg,#16a34a,#22c55e)'],
        'stock'              => ['bg'=>'rgba(245,158,11,.12)',  'color'=>'#d97706', 'bar'=>'linear-gradient(90deg,#d97706,#f59e0b)'],
        'maintenance'        => ['bg'=>'rgba(107,114,128,.12)', 'color'=>'#4b5563', 'bar'=>'linear-gradient(90deg,#4b5563,#6b7280)'],
        'compliance'         => ['bg'=>'rgba(20,184,166,.12)',  'color'=>'#0f766e', 'bar'=>'linear-gradient(90deg,#0f766e,#14b8a6)'],
        'contacts'           => ['bg'=>'rgba(249,115,22,.12)',  'color'=>'#ea580c', 'bar'=>'linear-gradient(90deg,#ea580c,#f97316)'],
        'contact_categories' => ['bg'=>'rgba(249,115,22,.10)',  'color'=>'#f97316', 'bar'=>'linear-gradient(90deg,#f97316,#fb923c)'],
        'crm'                => ['bg'=>'rgba(236,72,153,.12)',  'color'=>'#db2777', 'bar'=>'linear-gradient(90deg,#db2777,#ec4899)'],
        'franchise'          => ['bg'=>'rgba(99,102,241,.12)',  'color'=>'#4f46e5', 'bar'=>'linear-gradient(90deg,#4f46e5,#6366f1)'],
        'procurement'        => ['bg'=>'rgba(245,158,11,.12)',  'color'=>'#b45309', 'bar'=>'linear-gradient(90deg,#b45309,#d97706)'],
        'vendors'            => ['bg'=>'rgba(107,114,128,.12)', 'color'=>'#374151', 'bar'=>'linear-gradient(90deg,#374151,#6b7280)'],
        'sales'              => ['bg'=>'rgba(16,185,129,.12)',  'color'=>'#047857', 'bar'=>'linear-gradient(90deg,#047857,#059669)'],
        'reports'            => ['bg'=>'rgba(59,130,246,.12)',  'color'=>'#2563eb', 'bar'=>'linear-gradient(90deg,#2563eb,#3b82f6)'],
        'report_center'      => ['bg'=>'rgba(14,165,233,.12)',  'color'=>'#0284c7', 'bar'=>'linear-gradient(90deg,#0284c7,#0ea5e9)'],
        'documents'          => ['bg'=>'rgba(245,158,11,.12)',  'color'=>'#92400e', 'bar'=>'linear-gradient(90deg,#92400e,#b45309)'],
        'questions'          => ['bg'=>'rgba(139,92,246,.12)',  'color'=>'#7c3aed', 'bar'=>'linear-gradient(90deg,#7c3aed,#8b5cf6)'],
        'queue'              => ['bg'=>'rgba(107,114,128,.12)', 'color'=>'#4b5563', 'bar'=>'linear-gradient(90deg,#4b5563,#9ca3af)'],
        'settings'           => ['bg'=>'rgba(107,114,128,.12)', 'color'=>'#374151', 'bar'=>'linear-gradient(90deg,#374151,#6b7280)'],
        'permissions'        => ['bg'=>'rgba(139,92,246,.12)',  'color'=>'#6d28d9', 'bar'=>'linear-gradient(90deg,#6d28d9,#7c3aed)'],
    ];
    $defaultStyle = ['bg'=>'rgba(99,102,241,.12)','color'=>'#6366f1','bar'=>'linear-gradient(90deg,#6366f1,#818cf8)'];
@endphp

{{-- ── Hero ──────────────────────────────────────────────────── --}}
<div class="rd-hero">
    <div style="position:relative;z-index:1;">
        <div class="rd-role-badge"><i class="bi bi-shield-check"></i> {{ $roleLabel }}</div>
        <h3 class="rd-hero-name">{{ $greeting }}, {{ $user->name }}!</h3>
        <p class="rd-hero-sub">
            @if($user->employee?->department)
                <i class="bi bi-building me-1"></i>{{ $user->employee->department->name }} &nbsp;&middot;&nbsp;
            @endif
            <i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}
        </p>
    </div>
    <div class="rd-clock-wrap">
        <div class="rd-clock" id="rdClock">--:--:--</div>
        <div class="rd-clock-sub">
            {{ now()->format('T') }}
            &bull; {{ $accessibleServices->count() }} module{{ $accessibleServices->count() === 1 ? '' : 's' }} assigned
        </div>
    </div>
</div>

{{-- ── Full-gradient Stat Cards ─────────────────────────────── --}}
@if(!empty($statCards))
<div class="rd-stats">
    @foreach($statCards as $card)
    <div class="rd-stat-card" style="background:{{ $card['gradient'] }};">
        <div class="rd-stat-icon-wrap">
            <i class="bi bi-{{ $card['icon'] }}"></i>
        </div>
        <div class="rd-stat-val">{{ $card['value'] }}</div>
        <div class="rd-stat-lbl">{{ $card['label'] }}</div>
    </div>
    @endforeach
</div>
@endif

{{-- ── Module Grid ───────────────────────────────────────────── --}}
<div class="rd-mod-header">
    <div class="rd-mod-title">
        <i class="bi bi-grid-3x3-gap" style="color:#6366f1;font-size:1.05rem;"></i>
        My Modules
        <span class="rd-mod-count">{{ $accessibleServices->count() }}</span>
    </div>
    <span style="font-size:.77rem;color:#94a3b8;">Click any module to open it</span>
</div>

@if($accessibleServices->isEmpty())
<div class="rd-empty">
    <div class="rd-empty-icon"><i class="bi bi-shield-lock"></i></div>
    <div class="rd-empty-title">No modules assigned yet</div>
    <div class="rd-empty-sub">Contact your administrator to grant access to specific modules.</div>
</div>
@else
<div class="rd-modules">
    @foreach($accessibleServices as $svc)
    @php
        $s   = $svcStyle[$svc->service_key] ?? $defaultStyle;
        $key = $serviceRoutes[$svc->service_key] ?? null;
        $url = ($key && Route::has($key)) ? route($key) : '#';
    @endphp
    <a href="{{ $url }}" class="rd-mod-card">
        <div class="rd-mod-topbar" style="background:{{ $s['bar'] }};"></div>

        <div class="rd-mod-icon-wrap" style="background:{{ $s['bg'] }};">
            <i class="bi bi-{{ $svc->icon }}" style="color:{{ $s['color'] }};"></i>
        </div>

        <div class="rd-mod-name">{{ $svc->service_name }}</div>
        @if($svc->description)
            <div class="rd-mod-desc">{{ $svc->description }}</div>
        @endif

        <i class="bi bi-arrow-up-right rd-mod-arrow" style="color:{{ $s['color'] }};"></i>
    </a>
    @endforeach
</div>
@endif

@endsection

@push('scripts')
<script>
/* Live clock */
(function tick(){
    const n=new Date(), p=v=>String(v).padStart(2,'0');
    const el=document.getElementById('rdClock');
    if(el) el.textContent=p(n.getHours())+':'+p(n.getMinutes())+':'+p(n.getSeconds());
    setTimeout(tick,1000);
})();

/* Reveal top-bar on card hover */
document.querySelectorAll('.rd-mod-card').forEach(card=>{
    const bar=card.querySelector('.rd-mod-topbar');
    if(!bar) return;
    card.addEventListener('mouseenter',()=>bar.style.opacity='1');
    card.addEventListener('mouseleave',()=>bar.style.opacity='0');
});
</script>
@endpush
