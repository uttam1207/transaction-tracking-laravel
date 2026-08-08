@extends('layouts.app')
@section('title', 'ASDairy Reports Center')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports Center</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Module 17</div>
            <h4>ASDairy Reports Center</h4>
            <p>Consolidated analytics & reports across all ERP modules</p>
        </div>
    </div>
</div>

{{-- ── Section 1: Dairy Operations ── --}}
<h6 class="form-section-label mb-3">Dairy Operations Reports</h6>
<div class="row g-3 mb-4">

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#0369a1,#06b6d4);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-droplet-fill" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Milk Production Report</div>
                    <div class="text-muted" style="font-size:.72rem;">Shift-wise yield, fat/SNF, rejection</div>
                </div>
            </div>
            <a href="{{ route('admin.reports.milk') }}" class="btn btn-sm btn-primary-grad mt-auto">
                <i class="bi bi-arrow-right me-1"></i>Open Report
            </a>
        </div>
    </div>

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#065f46,#10b981);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-collection-fill" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Animal Population Report</div>
                    <div class="text-muted" style="font-size:.72rem;">Herd breakdown, health, lactation</div>
                </div>
            </div>
            <a href="{{ route('admin.reports.animals') }}" class="btn btn-sm btn-primary-grad mt-auto">
                <i class="bi bi-arrow-right me-1"></i>Open Report
            </a>
        </div>
    </div>

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#78350f,#f59e0b);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-basket-fill" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Feed Requirement Report</div>
                    <div class="text-muted" style="font-size:.72rem;">Daily/weekly/monthly needs vs stock</div>
                </div>
            </div>
            <a href="{{ route('admin.reports.feed') }}" class="btn btn-sm btn-primary-grad mt-auto">
                <i class="bi bi-arrow-right me-1"></i>Open Report
            </a>
        </div>
    </div>

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#7c3aed,#a78bfa);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-heart-pulse-fill" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Breeding & Fertility</div>
                    <div class="text-muted" style="font-size:.72rem;">AI success rate, expected calving</div>
                </div>
            </div>
            <a href="{{ route('admin.reports.breeding') }}" class="btn btn-sm btn-primary-grad mt-auto">
                <i class="bi bi-arrow-right me-1"></i>Open Report
            </a>
        </div>
    </div>

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#dc2626,#f87171);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-bandaid-fill" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Health & Treatment</div>
                    <div class="text-muted" style="font-size:.72rem;">Vaccination, treatment, disease</div>
                </div>
            </div>
            <a href="{{ route('admin.reports.health') }}" class="btn btn-sm btn-primary-grad mt-auto">
                <i class="bi bi-arrow-right me-1"></i>Open Report
            </a>
        </div>
    </div>

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#0d9488,#2dd4bf);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-boxes" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Inventory & Stock Report</div>
                    <div class="text-muted" style="font-size:.72rem;">Stock levels, movements, alerts</div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-auto">
                <a href="{{ route('admin.reports.inventory') }}" class="btn btn-sm btn-primary-grad flex-fill">
                    <i class="bi bi-arrow-right me-1"></i>Open
                </a>
                <a href="{{ route('admin.stock.export.excel') }}" class="btn btn-sm btn-outline-success" style="min-width:50px;text-align:center;">Excel</a>
            </div>
        </div>
    </div>
</div>

{{-- ── Section 2: Financial ── --}}
<h6 class="form-section-label mb-3">Financial Reports</h6>
<div class="row g-3 mb-4">

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#d97706,#fbbf24);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-receipt" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Expense Report</div>
                    <div class="text-muted" style="font-size:.72rem;">Category-wise, vendor-wise</div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-auto">
                <a href="{{ route('admin.expenses.export.pdf') }}" class="btn btn-sm btn-outline-secondary flex-fill">PDF</a>
                <a href="{{ route('admin.expenses.export.excel') }}" class="btn btn-sm btn-outline-success flex-fill">Excel</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-graph-up-arrow" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Financial P&L Summary</div>
                    <div class="text-muted" style="font-size:.72rem;">Revenue vs expenses</div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-auto">
                <a href="{{ route('admin.reports.profitability') }}" class="btn btn-sm btn-primary-grad flex-fill">
                    <i class="bi bi-arrow-right me-1"></i>P&L Report
                </a>
                <a href="{{ route('admin.reports.financial-summary') }}" class="btn btn-sm btn-outline-secondary" style="min-width:70px;text-align:center;">Transactions</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#0f766e,#14b8a6);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-cart3" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Sales Orders</div>
                    <div class="text-muted" style="font-size:.72rem;">Milk sales, animal sales</div>
                </div>
            </div>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary mt-auto">
                <i class="bi bi-arrow-right me-1"></i>View Sales
            </a>
        </div>
    </div>

    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#9333ea,#c084fc);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-truck" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Procurement</div>
                    <div class="text-muted" style="font-size:.72rem;">Purchase orders, vendors</div>
                </div>
            </div>
            <a href="{{ route('admin.procurement.index') }}" class="btn btn-sm btn-outline-secondary mt-auto">
                <i class="bi bi-arrow-right me-1"></i>View Procurement
            </a>
        </div>
    </div>
</div>

{{-- ── Section 3: HR & Ops ── --}}
<h6 class="form-section-label mb-3">HR & Operations</h6>
<div class="row g-3">
    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#374151,#6b7280);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-people-fill" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Employee Report</div>
                    <div class="text-muted" style="font-size:.72rem;">Staff list, performance</div>
                </div>
            </div>
            <a href="{{ route('admin.reports.employees') }}" class="btn btn-sm btn-primary-grad mt-auto">
                <i class="bi bi-arrow-right me-1"></i>Open Report
            </a>
        </div>
    </div>
    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#475569,#94a3b8);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-calendar-check" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Attendance Report</div>
                    <div class="text-muted" style="font-size:.72rem;">Daily presence, leaves</div>
                </div>
            </div>
            <a href="{{ route('admin.reports.attendance') }}" class="btn btn-sm btn-primary-grad mt-auto">
                <i class="bi bi-arrow-right me-1"></i>Open Report
            </a>
        </div>
    </div>
    <div class="col-md-4 col-lg-3">
        <div class="card-glass p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#0c4a6e,#0ea5e9);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-journal-text" style="font-size:1.2rem;color:#fff;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.88rem;">Audit Logs</div>
                    <div class="text-muted" style="font-size:.72rem;">System activity trail</div>
                </div>
            </div>
            <a href="{{ route('admin.reports.audit-logs') }}" class="btn btn-sm btn-primary-grad mt-auto">
                <i class="bi bi-arrow-right me-1"></i>Open Logs
            </a>
        </div>
    </div>
</div>

@endsection
