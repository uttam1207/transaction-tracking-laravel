@extends('layouts.app')
@section('title', 'My Dashboard')
@section('content')

@php
    $user = auth()->user();
    $roleLabel = ucwords(str_replace('_', ' ', $user->role));

    // Map service_key → route name for quick-access links
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

    // Icon colour palette (cycles through)
    $colours = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'];
@endphp

{{-- Hero --}}
<div class="page-hero" style="background:linear-gradient(135deg,#0f172a,#1e3a5f);">
    <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h4 style="margin:0;font-weight:800;color:#fff;">
                Good {{ now()->format('H') < 12 ? 'Morning' : (now()->format('H') < 17 ? 'Afternoon' : 'Evening') }},
                {{ $user->name }}!
            </h4>
            <p style="opacity:.75;margin:4px 0 0;font-size:.88rem;color:#fff;">
                {{ $roleLabel }}
                @if($user->employee?->department)
                    &bull; {{ $user->employee->department->name }}
                @endif
            </p>
        </div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.6);text-align:right;">
            <div>{{ now()->format('l, F j, Y') }}</div>
            <div id="liveClock" style="font-size:1.4rem;font-weight:700;color:#fff;letter-spacing:.06em;"></div>
        </div>
    </div>
</div>

{{-- Quick stats (only for services the user can access) --}}
@if(!empty($stats))
<div class="row g-3 mb-4">
    @if(isset($stats['transactions']))
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#6366f1,#818cf8);">
                <i class="bi bi-arrow-left-right"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['transactions']) }}</div>
            <div class="stat-label">Transactions</div>
        </div>
    </div>
    @endif
    @if(isset($stats['animals']))
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#34d399);">
                <i class="bi bi-card-checklist"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['animals']) }}</div>
            <div class="stat-label">Total Animals</div>
        </div>
    </div>
    @endif
    @if(isset($stats['milk_today']))
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);">
                <i class="bi bi-droplet-fill"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['milk_today'], 1) }} L</div>
            <div class="stat-label">Milk Today</div>
        </div>
    </div>
    @endif
    @if(isset($stats['expenses_month']))
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-value">₹{{ number_format($stats['expenses_month']) }}</div>
            <div class="stat-label">Expenses This Month</div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- Accessible Modules --}}
<div class="table-card">
    <div class="card-header">
        <span class="card-title"><i class="bi bi-grid-3x3-gap me-2"></i>My Modules</span>
        <div style="font-size:.8rem;color:#6b7280;">Services you have access to</div>
    </div>
    <div class="p-3">
        @if($accessibleServices->isEmpty())
            <div class="text-center py-5" style="color:#9ca3af;">
                <i class="bi bi-shield-lock" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-0">No modules assigned yet.</p>
                <p style="font-size:.82rem;">Contact your administrator to grant access.</p>
            </div>
        @else
        <div class="row g-3">
            @foreach($accessibleServices as $i => $svc)
            @php
                $colour  = $colours[$i % count($colours)];
                $routeKey = $serviceRoutes[$svc->service_key] ?? null;
                $url = $routeKey ? (Route::has($routeKey) ? route($routeKey) : '#') : '#';
            @endphp
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="{{ $url }}" class="text-decoration-none" style="display:block;">
                    <div style="background:#fff;border:1px solid #f3f4f6;border-radius:14px;padding:18px 16px;
                                transition:box-shadow .2s,transform .2s;cursor:pointer;"
                         onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.08)';this.style.transform='translateY(-2px)'"
                         onmouseout="this.style.boxShadow='none';this.style.transform='none'">
                        <div style="width:44px;height:44px;border-radius:12px;background:{{ $colour }}20;
                                    display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                            <i class="bi bi-{{ $svc->icon }}" style="font-size:1.2rem;color:{{ $colour }};"></i>
                        </div>
                        <div style="font-weight:700;font-size:.9rem;color:#111827;margin-bottom:4px;">
                            {{ $svc->service_name }}
                        </div>
                        @if($svc->description)
                        <div style="font-size:.76rem;color:#9ca3af;line-height:1.4;">
                            {{ $svc->description }}
                        </div>
                        @endif
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
(function tick() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    const el = document.getElementById('liveClock');
    if (el) el.textContent = h + ':' + m + ':' + s;
    setTimeout(tick, 1000);
})();
</script>
@endpush
