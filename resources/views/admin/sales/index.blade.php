@extends('layouts.app')
@section('title', 'Sales & Invoicing')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Sales & Invoicing</h4>
            <p>Milk Sales, Animal Sales, Feed Sales, Online Orders, Invoices & Outstanding Payments</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.export.sales', 'excel') }}?{{ http_build_query(request()->only(['date_from','date_to','payment_status','item_type'])) }}"
               class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('admin.export.sales', 'pdf') }}?{{ http_build_query(request()->only(['date_from','date_to','payment_status','item_type'])) }}"
               class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('admin.sales.item-types.index') }}" class="btn btn-sm btn-outline-secondary px-3">
                <i class="bi bi-gear me-1"></i>Item Types
            </a>
            <a href="{{ route('admin.sales.trash') }}" class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-trash3 me-1"></i>Trash
            </a>
            <a href="{{ route('admin.sales.create') }}" class="btn btn-primary-grad btn-sm px-4">
                <i class="bi bi-plus-lg me-1"></i>Create Invoice
            </a>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-currency-rupee kpi-icon"></i>
            <div class="kpi-value" style="font-size:1.4rem;">&#8377;{{ number_format($summary['total_sales'],0) }}</div>
            <div class="kpi-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#0891b2);">
            <i class="bi bi-droplet-fill kpi-icon"></i>
            <div class="kpi-value" style="font-size:1.4rem;">&#8377;{{ number_format($summary['milk_sales'],0) }}</div>
            <div class="kpi-label">Milk Sales</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#9f1239);">
            <i class="bi bi-clock-history kpi-icon"></i>
            <div class="kpi-value" style="font-size:1.4rem;">&#8377;{{ number_format($summary['pending_payment'],0) }}</div>
            <div class="kpi-label">Pending Outstanding</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#b45309,#d97706);">
            <i class="bi bi-exclamation-triangle-fill kpi-icon"></i>
            <div class="kpi-value" style="font-size:1.4rem;">
                {{ $summary['overdue_count'] }}
                @if($summary['overdue_count'] > 0)
                    <span style="font-size:.7rem;font-weight:600;opacity:.85;display:block;margin-top:2px;">&#8377;{{ number_format($summary['overdue_balance'],0) }}</span>
                @endif
            </div>
            <div class="kpi-label">Overdue Invoices</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-3">
        {{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.sales.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Invoice</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;"><i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Invoice number&#8230;" value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Item Type</label>
            <select name="item_type" class="form-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach($itemTypes as $t)
                    <option value="{{ $t->name }}" @selected(request('item_type')===$t->name)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Payment</label>
            <select name="payment_status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['Paid','Pending','Partial','Unbilled','Overdue'] as $s)
                    <option value="{{ $s }}" @selected(request('payment_status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','item_type','payment_status']))
                <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Table --}}
<div class="card-glass">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Sales Invoices Log</h6>
            @if(request()->hasAny(['search','item_type','payment_status']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $sales->total() }} invoices</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Customer</th>
                    <th>Item Type</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Balance Due</th>
                    <th>Status</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                    @php
                        $today    = now()->startOfDay();
                        $isOverdue = $s->due_date
                            && !in_array($s->payment_status, ['Paid'])
                            && $s->due_date->lt($today);
                        $overdueDays = $isOverdue ? $today->diffInDays($s->due_date) : 0;
                        $outstanding = max(0, (float)$s->total_amount - (float)$s->amount_paid);
                    @endphp
                    <tr class="{{ $isOverdue ? 'table-danger-soft' : '' }}">
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $s->invoice_number }}</div>
                            @if($s->payment_terms)
                                <div style="font-size:.68rem;color:#9ca3af;">{{ $s->payment_terms }}</div>
                            @endif
                        </td>
                        <td style="font-size:.82rem;">{{ $s->sale_date?->format('d M Y') }}</td>
                        <td style="font-size:.82rem;">
                            @if($s->due_date)
                                <div class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-body' }}">
                                    {{ $s->due_date->format('d M Y') }}
                                </div>
                                @if($isOverdue)
                                    <div style="font-size:.65rem;font-weight:700;color:#dc2626;letter-spacing:.02em;">
                                        OVERDUE {{ $overdueDays }}d
                                    </div>
                                @endif
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td style="font-size:.83rem;">{{ $s->customer?->name ?? 'Retail Customer' }}</td>
                        <td><span class="spill spill-info">{{ $s->item_type }}</span></td>
                        <td class="text-end fw-bold text-success">&#8377;{{ number_format($s->total_amount,2) }}</td>
                        <td class="text-end">
                            @if($s->payment_status === 'Paid')
                                <span style="font-size:.8rem;color:#16a34a;font-weight:600;">Settled</span>
                            @else
                                <span class="fw-bold {{ $isOverdue ? 'text-danger' : 'text-warning' }}" style="font-size:.9rem;">
                                    &#8377;{{ number_format($outstanding,2) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $pColor = match(true) {
                                    $isOverdue                          => 'spill-danger',
                                    $s->payment_status === 'Paid'       => 'spill-success',
                                    $s->payment_status === 'Pending'    => 'spill-warning',
                                    $s->payment_status === 'Partial'    => 'spill-info',
                                    default                             => 'spill-secondary',
                                };
                                $label = $isOverdue ? 'Overdue' : $s->payment_status;
                            @endphp
                                <span class="spill {{ $pColor }}">{{ $label }}</span>
                            @if($s->transaction_id)
                                <div style="margin-top:3px;">
                                    <span class="spill spill-info" style="font-size:.65rem;padding:1px 6px;opacity:.85;">Via TXN</span>
                                </div>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.sales.show',$s) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.sales.edit',$s) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="empty-state"><i class="bi bi-cash-coin"></i><p>No sales invoices recorded</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $sales->firstItem() }}</strong>&#8211;<strong>{{ $sales->lastItem() }}</strong> of <strong>{{ $sales->total() }}</strong> invoices
            </div>
            {{ $sales->links() }}
        </div>
    @endif
</div>

@endsection
