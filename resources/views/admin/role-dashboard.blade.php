@extends('layouts.app')
@section('title', 'My Dashboard')

@push('styles')
<style>
/* ── Keyframes ────────────────────────────────────────────── */
@keyframes fadeUp {
    from { opacity:0; transform:translateY(18px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes pulse-ring {
    0%   { transform:scale(.9); opacity:.6; }
    70%  { transform:scale(1.25); opacity:0; }
    100% { transform:scale(1.25); opacity:0; }
}

/* ── Hero ────────────────────────────────────────────────────── */
.rd-hero {
    background: linear-gradient(135deg,#0f172a 0%,#1a2e50 50%,#1e293b 100%);
    border-radius: 24px;
    padding: 0;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 40px rgba(0,0,0,.22);
}
/* mesh gradient orbs */
.rd-hero-orb1 {
    position:absolute; width:380px; height:380px; border-radius:50%;
    background:radial-gradient(circle,rgba(99,102,241,.25) 0%,transparent 70%);
    top:-120px; right:-80px; pointer-events:none;
}
.rd-hero-orb2 {
    position:absolute; width:260px; height:260px; border-radius:50%;
    background:radial-gradient(circle,rgba(14,165,233,.18) 0%,transparent 70%);
    bottom:-80px; left:10%; pointer-events:none;
}
.rd-hero-orb3 {
    position:absolute; width:180px; height:180px; border-radius:50%;
    background:radial-gradient(circle,rgba(236,72,153,.12) 0%,transparent 70%);
    top:30px; left:42%; pointer-events:none;
}
/* grid pattern overlay */
.rd-hero-grid {
    position:absolute; inset:0; pointer-events:none;
    background-image:
        linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
    background-size: 40px 40px;
}
.rd-hero-inner {
    position:relative; z-index:1;
    padding: 30px 36px;
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:20px;
}
.rd-role-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(99,102,241,.25);
    border:1px solid rgba(99,102,241,.45);
    border-radius:20px; padding:4px 14px;
    font-size:.7rem; color:rgba(255,255,255,.9);
    font-weight:700; text-transform:uppercase; letter-spacing:.08em;
    margin-bottom:10px;
}
.rd-hero-name { font-size:1.9rem; font-weight:800; color:#fff; margin:0 0 6px; line-height:1.2; }
.rd-hero-sub  { font-size:.82rem; color:rgba(255,255,255,.5); margin:0; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.rd-hero-sub span { display:flex; align-items:center; gap:5px; }

/* Clock panel */
.rd-clock-panel {
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.1);
    border-radius:18px;
    padding:18px 28px;
    text-align:center;
    min-width:160px;
    backdrop-filter:blur(10px);
}
.rd-clock { font-size:2.2rem; font-weight:800; color:#fff; letter-spacing:.06em; line-height:1; font-variant-numeric:tabular-nums; }
.rd-clock-date { font-size:.7rem; color:rgba(255,255,255,.45); margin-top:6px; letter-spacing:.04em; }
.rd-clock-modules {
    margin-top:10px; padding-top:10px; border-top:1px solid rgba(255,255,255,.1);
    font-size:.7rem; color:rgba(255,255,255,.55);
}
.rd-clock-modules strong { color:rgba(255,255,255,.85); font-size:.95rem; display:block; line-height:1; }

/* ── Stat Cards ──────────────────────────────────────────────── */
.rd-stats {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(155px,1fr));
    gap:14px;
    margin-bottom:26px;
    animation: fadeUp .5s ease both;
}
.rd-stat-card {
    border-radius:20px;
    padding:20px 18px;
    position:relative; overflow:hidden;
    min-height:130px;
    transition:transform .22s, box-shadow .22s;
    display:flex; flex-direction:column; justify-content:space-between;
}
.rd-stat-card:hover {
    transform:translateY(-5px);
    box-shadow:0 18px 40px rgba(0,0,0,.22);
}
.rd-stat-card::before {
    content:''; position:absolute;
    width:140px; height:140px; border-radius:50%;
    background:rgba(255,255,255,.08);
    bottom:-45px; right:-35px; pointer-events:none;
}
.rd-stat-card::after {
    content:''; position:absolute;
    width:80px; height:80px; border-radius:50%;
    background:rgba(255,255,255,.06);
    top:-22px; left:-16px; pointer-events:none;
}
.rd-stat-top {
    display:flex; align-items:center; justify-content:space-between;
    position:relative; z-index:1;
}
.rd-stat-icon-wrap {
    width:42px; height:42px; border-radius:12px;
    background:rgba(255,255,255,.2);
    display:flex; align-items:center; justify-content:center;
    font-size:1.15rem; color:#fff;
}
.rd-stat-dot {
    width:8px; height:8px; border-radius:50%;
    background:rgba(255,255,255,.5);
    position:relative;
}
.rd-stat-dot::after {
    content:''; position:absolute; inset:-3px; border-radius:50%;
    border:1px solid rgba(255,255,255,.3);
    animation:pulse-ring 2.5s ease infinite;
}
.rd-stat-bottom { position:relative; z-index:1; }
.rd-stat-val  { font-size:2rem; font-weight:800; color:#fff; line-height:1; }
.rd-stat-lbl  { font-size:.67rem; color:rgba(255,255,255,.72); text-transform:uppercase; letter-spacing:.1em; font-weight:600; margin-top:5px; }

/* ── Section Header ──────────────────────────────────────────── */
.rd-section-hd {
    display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;
    animation: fadeUp .5s .1s ease both;
}
.rd-section-title {
    font-size:.95rem; font-weight:700;
    display:flex; align-items:center; gap:9px;
}
.rd-section-title i { font-size:1rem; }
.rd-mod-count {
    background:rgba(99,102,241,.12); color:#6366f1;
    border-radius:20px; padding:3px 12px;
    font-size:.72rem; font-weight:700;
}
.rd-hint { font-size:.74rem; color:var(--text-muted,#94a3b8); }

/* ── Module Cards ────────────────────────────────────────────── */
.rd-modules {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(210px,1fr));
    gap:16px;
}
.rd-mod-card {
    border-radius:20px;
    text-decoration:none;
    display:flex; flex-direction:column;
    overflow:hidden;
    transition:box-shadow .25s, transform .22s;
    box-shadow:0 2px 12px rgba(0,0,0,.07);
    background:var(--card-bg, #fff);
    animation: fadeUp .45s ease both;
}
.rd-mod-card:hover {
    box-shadow:0 16px 44px rgba(0,0,0,.14);
    transform:translateY(-6px);
    text-decoration:none;
}

/* Icon strip at top */
.rd-mod-strip {
    height:72px;
    display:flex; align-items:center; padding:0 20px;
    position:relative; overflow:hidden; flex-shrink:0;
    gap:14px;
}
.rd-mod-strip::after {
    content:''; position:absolute;
    width:120px; height:120px; border-radius:50%;
    background:rgba(255,255,255,.1);
    top:-40px; right:-30px; pointer-events:none;
}
.rd-mod-icon {
    width:46px; height:46px; border-radius:13px;
    background:rgba(255,255,255,.22);
    display:flex; align-items:center; justify-content:center;
    font-size:1.2rem; color:#fff;
    position:relative; z-index:1; flex-shrink:0;
    transition:transform .2s;
}
.rd-mod-card:hover .rd-mod-icon { transform:scale(1.1) rotate(-3deg); }
.rd-mod-strip-name {
    font-size:.82rem; font-weight:700; color:#fff;
    position:relative; z-index:1;
    line-height:1.25;
    text-shadow:0 1px 3px rgba(0,0,0,.2);
}

/* Body */
.rd-mod-body {
    padding:14px 18px 10px;
    flex:1;
}
.rd-mod-desc {
    font-size:.73rem; color:var(--text-muted,#94a3b8);
    line-height:1.5;
}

/* Footer */
.rd-mod-foot {
    padding:10px 18px;
    border-top:1px solid rgba(0,0,0,.05);
    display:flex; align-items:center; justify-content:space-between;
}
.rd-mod-open-btn {
    font-size:.7rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.07em;
    display:flex; align-items:center; gap:5px;
    border:none; background:none; padding:0; cursor:pointer;
    transition:gap .18s;
}
.rd-mod-card:hover .rd-mod-open-btn { gap:9px; }
.rd-mod-open-btn i { font-size:.75rem; transition:transform .18s; }
.rd-mod-card:hover .rd-mod-open-btn i { transform:translateX(3px); }
.rd-mod-pip {
    width:24px; height:5px; border-radius:10px;
    opacity:.35; flex-shrink:0;
}

/* ── Empty state ─────────────────────────────────────────────── */
.rd-empty {
    text-align:center; padding:70px 24px;
    border-radius:20px;
    border:2px dashed rgba(0,0,0,.08);
}
.rd-empty-icon { font-size:3rem; opacity:.2; margin-bottom:14px; }
.rd-empty-title{ font-size:1rem; font-weight:700; margin-bottom:6px; }
.rd-empty-sub  { font-size:.8rem; color:var(--text-muted,#94a3b8); }

/* ── Dark mode ───────────────────────────────────────────────── */
[data-bs-theme="dark"] .rd-mod-card    { background:#1e293b; }
[data-bs-theme="dark"] .rd-mod-foot   { border-color:#334155; }
[data-bs-theme="dark"] .rd-empty      { border-color:#334155; }
[data-bs-theme="dark"] .rd-section-title { color:#f1f5f9; }
[data-bs-theme="dark"] .rd-mod-count  { background:rgba(99,102,241,.2); }

/* ── Stagger animation delays for module cards ─────────────── */
.rd-modules .rd-mod-card:nth-child(1)  { animation-delay:.05s; }
.rd-modules .rd-mod-card:nth-child(2)  { animation-delay:.08s; }
.rd-modules .rd-mod-card:nth-child(3)  { animation-delay:.11s; }
.rd-modules .rd-mod-card:nth-child(4)  { animation-delay:.14s; }
.rd-modules .rd-mod-card:nth-child(5)  { animation-delay:.17s; }
.rd-modules .rd-mod-card:nth-child(6)  { animation-delay:.20s; }
.rd-modules .rd-mod-card:nth-child(7)  { animation-delay:.23s; }
.rd-modules .rd-mod-card:nth-child(8)  { animation-delay:.26s; }
.rd-modules .rd-mod-card:nth-child(9)  { animation-delay:.29s; }
.rd-modules .rd-mod-card:nth-child(10) { animation-delay:.32s; }
.rd-modules .rd-mod-card:nth-child(n+11) { animation-delay:.35s; }
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
        'reports'            => 'admin.reports.financial-summary',
        'report_center'      => 'admin.reports.center',
        'documents'          => 'documents.index',
        'questions'          => 'questions.index',
        'queue'              => 'admin.queue.index',
        'settings'           => 'admin.settings.index',
        'permissions'        => 'admin.permissions.index',
    ];

    // ── Per-service gradient ─────────────────────────────────────
    $svcStyle = [
        'transactions'       => ['grad'=>'linear-gradient(135deg,#6366f1,#818cf8)', 'color'=>'#6366f1'],
        'fraud_alerts'       => ['grad'=>'linear-gradient(135deg,#ef4444,#f87171)', 'color'=>'#ef4444'],
        'wallets'            => ['grad'=>'linear-gradient(135deg,#10b981,#34d399)', 'color'=>'#10b981'],
        'expenses'           => ['grad'=>'linear-gradient(135deg,#f59e0b,#fbbf24)', 'color'=>'#f59e0b'],
        'expense_categories' => ['grad'=>'linear-gradient(135deg,#d97706,#f59e0b)', 'color'=>'#d97706'],
        'salaries'           => ['grad'=>'linear-gradient(135deg,#14b8a6,#2dd4bf)', 'color'=>'#14b8a6'],
        'users'              => ['grad'=>'linear-gradient(135deg,#8b5cf6,#a78bfa)', 'color'=>'#8b5cf6'],
        'employees'          => ['grad'=>'linear-gradient(135deg,#6366f1,#a78bfa)', 'color'=>'#6366f1'],
        'departments'        => ['grad'=>'linear-gradient(135deg,#3b82f6,#60a5fa)', 'color'=>'#3b82f6'],
        'teams'              => ['grad'=>'linear-gradient(135deg,#ec4899,#f472b6)', 'color'=>'#ec4899'],
        'attendance'         => ['grad'=>'linear-gradient(135deg,#0ea5e9,#38bdf8)', 'color'=>'#0ea5e9'],
        'shifts'             => ['grad'=>'linear-gradient(135deg,#4f46e5,#6366f1)', 'color'=>'#4f46e5'],
        'holidays'           => ['grad'=>'linear-gradient(135deg,#dc2626,#ef4444)', 'color'=>'#dc2626'],
        'tasks'              => ['grad'=>'linear-gradient(135deg,#a855f7,#c084fc)', 'color'=>'#a855f7'],
        'projects'           => ['grad'=>'linear-gradient(135deg,#6366f1,#8b5cf6)', 'color'=>'#6366f1'],
        'work_reports'       => ['grad'=>'linear-gradient(135deg,#6b7280,#9ca3af)', 'color'=>'#6b7280'],
        'timesheets'         => ['grad'=>'linear-gradient(135deg,#0d9488,#14b8a6)', 'color'=>'#0d9488'],
        'animals'            => ['grad'=>'linear-gradient(135deg,#059669,#10b981)', 'color'=>'#059669'],
        'breeds'             => ['grad'=>'linear-gradient(135deg,#10b981,#34d399)', 'color'=>'#10b981'],
        'milk'               => ['grad'=>'linear-gradient(135deg,#0ea5e9,#38bdf8)', 'color'=>'#0ea5e9'],
        'breeding'           => ['grad'=>'linear-gradient(135deg,#e11d48,#f43f5e)', 'color'=>'#e11d48'],
        'health'             => ['grad'=>'linear-gradient(135deg,#10b981,#6ee7b7)', 'color'=>'#10b981'],
        'feed'               => ['grad'=>'linear-gradient(135deg,#65a30d,#84cc16)', 'color'=>'#65a30d'],
        'farm'               => ['grad'=>'linear-gradient(135deg,#16a34a,#22c55e)', 'color'=>'#16a34a'],
        'stock'              => ['grad'=>'linear-gradient(135deg,#d97706,#f59e0b)', 'color'=>'#d97706'],
        'maintenance'        => ['grad'=>'linear-gradient(135deg,#4b5563,#6b7280)', 'color'=>'#4b5563'],
        'compliance'         => ['grad'=>'linear-gradient(135deg,#0f766e,#14b8a6)', 'color'=>'#0f766e'],
        'contacts'           => ['grad'=>'linear-gradient(135deg,#ea580c,#f97316)', 'color'=>'#ea580c'],
        'contact_categories' => ['grad'=>'linear-gradient(135deg,#f97316,#fb923c)', 'color'=>'#f97316'],
        'crm'                => ['grad'=>'linear-gradient(135deg,#db2777,#ec4899)', 'color'=>'#db2777'],
        'franchise'          => ['grad'=>'linear-gradient(135deg,#4f46e5,#818cf8)', 'color'=>'#4f46e5'],
        'procurement'        => ['grad'=>'linear-gradient(135deg,#b45309,#d97706)', 'color'=>'#b45309'],
        'vendors'            => ['grad'=>'linear-gradient(135deg,#374151,#6b7280)', 'color'=>'#374151'],
        'sales'              => ['grad'=>'linear-gradient(135deg,#047857,#059669)', 'color'=>'#047857'],
        'reports'            => ['grad'=>'linear-gradient(135deg,#2563eb,#3b82f6)', 'color'=>'#2563eb'],
        'report_center'      => ['grad'=>'linear-gradient(135deg,#0284c7,#0ea5e9)', 'color'=>'#0284c7'],
        'documents'          => ['grad'=>'linear-gradient(135deg,#92400e,#b45309)', 'color'=>'#92400e'],
        'questions'          => ['grad'=>'linear-gradient(135deg,#7c3aed,#8b5cf6)', 'color'=>'#7c3aed'],
        'queue'              => ['grad'=>'linear-gradient(135deg,#4b5563,#9ca3af)', 'color'=>'#4b5563'],
        'settings'           => ['grad'=>'linear-gradient(135deg,#374151,#6b7280)', 'color'=>'#374151'],
        'permissions'        => ['grad'=>'linear-gradient(135deg,#6d28d9,#7c3aed)', 'color'=>'#6d28d9'],
    ];
    $defaultStyle = ['grad'=>'linear-gradient(135deg,#6366f1,#818cf8)', 'color'=>'#6366f1'];
@endphp

{{-- ── Hero ─────────────────────────────────────────────────── --}}
<div class="rd-hero" style="animation:fadeUp .4s ease both;">
    <div class="rd-hero-grid"></div>
    <div class="rd-hero-orb1"></div>
    <div class="rd-hero-orb2"></div>
    <div class="rd-hero-orb3"></div>
    <div class="rd-hero-inner">
        <div>
            <div class="rd-role-badge">
                <i class="bi bi-shield-check"></i> {{ $roleLabel }}
            </div>
            <h3 class="rd-hero-name">{{ $greeting }}, {{ explode(' ', $user->name)[0] }}!</h3>
            <p class="rd-hero-sub">
                @if($user->employee?->department)
                    <span><i class="bi bi-building"></i>{{ $user->employee->department->name }}</span>
                    <span style="opacity:.3;">|</span>
                @endif
                <span><i class="bi bi-calendar3"></i>{{ now()->format('l, d F Y') }}</span>
            </p>
        </div>
        <div class="rd-clock-panel">
            <div class="rd-clock" id="rdClock">--:--:--</div>
            <div class="rd-clock-date">{{ now()->format('T') }}</div>
            <div class="rd-clock-modules">
                <strong>{{ $accessibleServices->count() }}</strong>
                Module{{ $accessibleServices->count() === 1 ? '' : 's' }} Assigned
            </div>
        </div>
    </div>
</div>

{{-- ── Stat Cards ───────────────────────────────────────────── --}}
@if(!empty($statCards))
<div class="rd-stats">
    @foreach($statCards as $i => $card)
    <div class="rd-stat-card" style="background:{{ $card['gradient'] }};animation-delay:{{ $i * 0.06 }}s;">
        <div class="rd-stat-top">
            <div class="rd-stat-icon-wrap">
                <i class="bi bi-{{ $card['icon'] }}"></i>
            </div>
            <div class="rd-stat-dot"></div>
        </div>
        <div class="rd-stat-bottom">
            <div class="rd-stat-val">{{ $card['value'] }}</div>
            <div class="rd-stat-lbl">{{ $card['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── Module Grid ─────────────────────────────────────────── --}}
<div class="rd-section-hd">
    <div class="rd-section-title">
        <i class="bi bi-grid-3x3-gap" style="color:#6366f1;"></i>
        My Modules
        <span class="rd-mod-count">{{ $accessibleServices->count() }}</span>
    </div>
    <span class="rd-hint"><i class="bi bi-cursor me-1"></i>Click any module to open</span>
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
        {{-- Icon strip --}}
        <div class="rd-mod-strip" style="background:{{ $s['grad'] }};">
            <div class="rd-mod-icon">
                <i class="bi bi-{{ $svc->icon }}"></i>
            </div>
            <span class="rd-mod-strip-name">{{ $svc->service_name }}</span>
        </div>

        {{-- Description --}}
        @if($svc->description)
        <div class="rd-mod-body">
            <div class="rd-mod-desc">{{ Str::limit($svc->description, 65) }}</div>
        </div>
        @else
        <div style="flex:1;"></div>
        @endif

        {{-- Footer --}}
        <div class="rd-mod-foot">
            <button class="rd-mod-open-btn" style="color:{{ $s['color'] }};">
                Open module <i class="bi bi-arrow-right"></i>
            </button>
            <div class="rd-mod-pip" style="background:{{ $s['color'] }};"></div>
        </div>
    </a>
    @endforeach
</div>
@endif

@endsection

@push('scripts')
<script>
(function tick(){
    const n=new Date(), p=v=>String(v).padStart(2,'0');
    const el=document.getElementById('rdClock');
    if(el) el.textContent=p(n.getHours())+':'+p(n.getMinutes())+':'+p(n.getSeconds());
    setTimeout(tick,1000);
})();
</script>
@endpush
