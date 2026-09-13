@extends('layouts.app')
@section('title', 'Business Ledgers')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.coa.index') }}">Finance</a></li>
    <li class="breadcrumb-item active">Ledgers</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Business Ledgers</h4>
            <p>Bank Book &mdash; Sales / AR Ledger &mdash; Purchase / AP Ledger</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.finance.reports.trial-balance') }}"    class="btn btn-outline-secondary btn-sm">Trial Balance</a>
            <a href="{{ route('admin.finance.reports.profit-loss') }}"      class="btn btn-outline-success btn-sm">P&amp;L</a>
            <a href="{{ route('admin.finance.reports.balance-sheet') }}"    class="btn btn-outline-primary btn-sm">Balance Sheet</a>
        </div>
    </div>
</div>

{{-- Date filter --}}
<form method="GET" class="card-glass px-4 py-3 mb-4">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;">From Date</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;">To Date</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary-grad btn-sm px-4">Filter</button>
            <a href="{{ route('admin.finance.ledgers.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </div>
</form>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-0" style="border-bottom:2px solid #e5e7eb;">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'bank' ? 'active fw-bold' : '' }}"
           href="{{ route('admin.finance.ledgers.index', array_merge(request()->query(), ['tab' => 'bank'])) }}"
           style="{{ $tab === 'bank' ? 'border-bottom:3px solid var(--primary);color:var(--primary);' : 'color:#6b7280;' }}">
            <i class="bi bi-bank me-1"></i>Bank Book
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'sales' ? 'active fw-bold' : '' }}"
           href="{{ route('admin.finance.ledgers.index', array_merge(request()->query(), ['tab' => 'sales'])) }}"
           style="{{ $tab === 'sales' ? 'border-bottom:3px solid var(--primary);color:var(--primary);' : 'color:#6b7280;' }}">
            <i class="bi bi-receipt-cutoff me-1"></i>Sales &amp; AR Ledger
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'purchase' ? 'active fw-bold' : '' }}"
           href="{{ route('admin.finance.ledgers.index', array_merge(request()->query(), ['tab' => 'purchase'])) }}"
           style="{{ $tab === 'purchase' ? 'border-bottom:3px solid var(--primary);color:var(--primary);' : 'color:#6b7280;' }}">
            <i class="bi bi-cart-check me-1"></i>Purchase &amp; AP Ledger
        </a>
    </li>
</ul>

<div class="card-glass overflow-hidden" style="border-radius:0 0 14px 14px;border-top:none;">

{{-- ═══════════════ BANK BOOK ══════════════════════════════════════════════ --}}
@if($tab === 'bank')
<div class="p-4">

    @php
        $bankRows  = $bankData['rows'] ?? [];
        $bankBal   = $bankData['closing_balance'] ?? 0;
        $bankDr    = collect($bankRows)->sum('debit');
        $bankCr    = collect($bankRows)->sum('credit');
    @endphp

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
                <i class="bi bi-bank kpi-icon"></i>
                <div class="kpi-value">&#8377;{{ number_format($bankBal, 2) }}</div>
                <div class="kpi-label">Bank Balance</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                <i class="bi bi-arrow-down-circle kpi-icon"></i>
                <div class="kpi-value">&#8377;{{ number_format($bankDr, 2) }}</div>
                <div class="kpi-label">Total Receipts (Dr)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#9f1239);">
                <i class="bi bi-arrow-up-circle kpi-icon"></i>
                <div class="kpi-value">&#8377;{{ number_format($bankCr, 2) }}</div>
                <div class="kpi-label">Total Payments (Cr)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
                <i class="bi bi-journal-text kpi-icon"></i>
                <div class="kpi-value">{{ count($bankRows) }}</div>
                <div class="kpi-label">Transactions</div>
            </div>
        </div>
    </div>

    {{-- Bank Ledger Table --}}
    <div class="table-responsive">
        <table class="table modern-table mb-0" style="font-size:.83rem;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entry #</th>
                    <th>Description</th>
                    <th class="text-end">Debit (&#8377;)</th>
                    <th class="text-end">Credit (&#8377;)</th>
                    <th class="text-end">Balance (&#8377;)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bankRows as $row)
                <tr>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.finance.journal.show', $row['entry']->id) }}"
                           style="color:#4f46e5;font-weight:600;font-family:monospace;font-size:.78rem;">
                            {{ $row['entry_number'] }}
                        </a>
                    </td>
                    <td>{{ Str::limit($row['description'] ?? '—', 55) }}</td>
                    <td class="text-end fw-bold" style="color:#1d4ed8;">{{ $row['debit'] > 0 ? '₹'.number_format($row['debit'],2) : '—' }}</td>
                    <td class="text-end fw-bold" style="color:#7c3aed;">{{ $row['credit'] > 0 ? '₹'.number_format($row['credit'],2) : '—' }}</td>
                    <td class="text-end fw-bold" style="color:{{ $row['balance'] >= 0 ? '#059669' : '#dc2626' }};">
                        &#8377;{{ number_format(abs($row['balance']), 2) }}
                        <span style="font-size:.68rem;font-weight:700;">{{ $row['balance'] >= 0 ? 'Dr' : 'Cr' }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="empty-state"><i class="bi bi-bank"></i><p>No bank transactions posted yet</p></td></tr>
                @endforelse
            </tbody>
            @if(count($bankRows) > 0)
            <tfoot style="background:#1e293b;color:#fff;">
                <tr>
                    <td colspan="3" class="text-end fw-bold" style="color:#94a3b8;padding:10px 16px;">Closing Balance</td>
                    <td class="text-end fw-bold" style="color:#60a5fa;padding:10px 16px;">&#8377;{{ number_format($bankDr,2) }}</td>
                    <td class="text-end fw-bold" style="color:#a78bfa;padding:10px 16px;">&#8377;{{ number_format($bankCr,2) }}</td>
                    <td class="text-end fw-bold" style="color:{{ $bankBal >= 0 ? '#34d399' : '#f87171' }};padding:10px 16px;">
                        &#8377;{{ number_format(abs($bankBal),2) }} {{ $bankBal >= 0 ? 'Dr' : 'Cr' }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif

{{-- ═══════════════ SALES / AR LEDGER ══════════════════════════════════════ --}}
@if($tab === 'sales')
<div class="p-4">

    @php
        $arBal = $arData['closing_balance'] ?? 0;
        $totalSales = $salesOrders->sum('total_amount');
        $pendingAR  = $salesOrders->where('payment_status', '!=', 'Paid')->sum('total_amount');
    @endphp

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
                <i class="bi bi-receipt-cutoff kpi-icon"></i>
                <div class="kpi-value">&#8377;{{ number_format($totalSales, 0) }}</div>
                <div class="kpi-label">Total Sales</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#9f1239);">
                <i class="bi bi-hourglass-split kpi-icon"></i>
                <div class="kpi-value">&#8377;{{ number_format($pendingAR, 0) }}</div>
                <div class="kpi-label">Outstanding AR</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#0891b2);">
                <i class="bi bi-person-check kpi-icon"></i>
                <div class="kpi-value">{{ $customerAR->count() }}</div>
                <div class="kpi-label">Customers with Balance</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                <i class="bi bi-journal-bookmark kpi-icon"></i>
                <div class="kpi-value">&#8377;{{ number_format(abs($arBal), 0) }}</div>
                <div class="kpi-label">AR Ledger Balance</div>
            </div>
        </div>
    </div>

    {{-- Customer-wise AR --}}
    @if($customerAR->isNotEmpty())
    <div class="mb-4">
        <h6 class="fw-bold mb-3" style="color:#374151;"><i class="bi bi-people me-2 text-danger"></i>Customer-wise Outstanding Balance</h6>
        <div class="row g-3">
            @foreach($customerAR as $ar)
            <div class="col-md-4">
                <div style="background:#fff;border-radius:12px;padding:14px 18px;box-shadow:0 1px 6px rgba(0,0,0,.06);border-left:4px solid #dc2626;">
                    <div class="fw-semibold" style="color:#1e293b;">{{ $ar->customer?->name ?? 'Retail Customer' }}</div>
                    <div style="font-size:.75rem;color:#6b7280;">{{ $ar->invoice_count }} unpaid invoice{{ $ar->invoice_count > 1 ? 's' : '' }}</div>
                    <div style="font-size:1.1rem;font-weight:800;color:#dc2626;margin-top:5px;">&#8377;{{ number_format($ar->outstanding, 2) }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Sales Invoice Table --}}
    <h6 class="fw-bold mb-3" style="color:#374151;"><i class="bi bi-receipt-cutoff me-2 text-success"></i>Sales Invoice Ledger</h6>
    <div class="table-responsive">
        <table class="table modern-table mb-0" style="font-size:.83rem;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Item Type</th>
                    <th class="text-end">Amount (&#8377;)</th>
                    <th>Payment</th>
                    <th>Journal Entry</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesOrders as $sale)
                <tr>
                    <td class="text-muted small">{{ $sale->sale_date->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.sales.show', $sale) }}" class="fw-bold" style="color:var(--primary);font-size:.85rem;">
                            {{ $sale->invoice_number }}
                        </a>
                    </td>
                    <td style="font-size:.82rem;">{{ $sale->customer?->name ?? 'Retail' }}</td>
                    <td><span class="spill spill-info">{{ $sale->item_type }}</span></td>
                    <td class="text-end fw-bold text-success">&#8377;{{ number_format($sale->total_amount, 2) }}</td>
                    <td>
                        @php $pColor = match($sale->payment_status) { 'Paid' => 'spill-success', 'Pending' => 'spill-warning', default => 'spill-info' }; @endphp
                        <span class="spill {{ $pColor }}">{{ $sale->payment_status }}</span>
                    </td>
                    <td>
                        @if($sale->journal_entry_id)
                            <a href="{{ route('admin.finance.journal.show', $sale->journal_entry_id) }}"
                               style="color:#4f46e5;font-weight:600;font-family:monospace;font-size:.75rem;">
                               JE #{{ $sale->journal_entry_id }}
                            </a>
                        @else
                            <span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>Not posted</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty-state"><i class="bi bi-receipt-cutoff"></i><p>No sales invoices found</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($salesOrders->hasPages())
    <div class="px-3 py-2">{{ $salesOrders->withQueryString()->links() }}</div>
    @endif
</div>
@endif

{{-- ═══════════════ PURCHASE / AP LEDGER ═══════════════════════════════════ --}}
@if($tab === 'purchase')
<div class="p-4">

    @php
        $apBal      = $apData['closing_balance'] ?? 0;
        $totalPO    = $purchaseOrders->sum('total_amount');
        $pendingAP  = $purchaseOrders->where('status', 'Received')->sum('total_amount');
    @endphp

    {{-- KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
                <i class="bi bi-cart-check kpi-icon"></i>
                <div class="kpi-value">&#8377;{{ number_format($totalPO, 0) }}</div>
                <div class="kpi-label">Total Purchases</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#9f1239);">
                <i class="bi bi-hourglass-split kpi-icon"></i>
                <div class="kpi-value">&#8377;{{ number_format($pendingAP, 0) }}</div>
                <div class="kpi-label">Outstanding AP (Payable)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#0891b2);">
                <i class="bi bi-people kpi-icon"></i>
                <div class="kpi-value">{{ $vendorAP->count() }}</div>
                <div class="kpi-label">Vendors with Balance</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                <i class="bi bi-journal-bookmark kpi-icon"></i>
                <div class="kpi-value">&#8377;{{ number_format(abs($apBal), 0) }}</div>
                <div class="kpi-label">AP Ledger Balance</div>
            </div>
        </div>
    </div>

    {{-- Vendor-wise AP --}}
    @if($vendorAP->isNotEmpty())
    <div class="mb-4">
        <h6 class="fw-bold mb-3" style="color:#374151;"><i class="bi bi-people me-2 text-danger"></i>Vendor-wise Outstanding Payable</h6>
        <div class="row g-3">
            @foreach($vendorAP as $ap)
            <div class="col-md-4">
                <div style="background:#fff;border-radius:12px;padding:14px 18px;box-shadow:0 1px 6px rgba(0,0,0,.06);border-left:4px solid #dc2626;">
                    <div class="fw-semibold" style="color:#1e293b;">{{ $ap->vendor?->name ?? 'Vendor' }}</div>
                    <div style="font-size:.75rem;color:#6b7280;">{{ $ap->po_count }} PO{{ $ap->po_count > 1 ? 's' : '' }} received, not paid</div>
                    <div style="font-size:1.1rem;font-weight:800;color:#dc2626;margin-top:5px;">&#8377;{{ number_format($ap->outstanding, 2) }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Purchase Orders Table --}}
    <h6 class="fw-bold mb-3" style="color:#374151;"><i class="bi bi-cart-check me-2 text-primary"></i>Purchase Order Ledger</h6>
    <div class="table-responsive">
        <table class="table modern-table mb-0" style="font-size:.83rem;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>PO #</th>
                    <th>Vendor</th>
                    <th class="text-end">Amount (&#8377;)</th>
                    <th>Status</th>
                    <th>Goods Receipt JE</th>
                    <th>Payment JE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                @php
                    $statusColor = match($po->status) {
                        'Paid'     => 'spill-success',
                        'Received' => 'spill-warning',
                        'Sent'     => 'spill-info',
                        default    => '',
                    };
                @endphp
                <tr>
                    <td class="text-muted small">{{ $po->order_date->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.procurement.show', $po) }}" class="fw-bold" style="color:var(--primary);font-size:.85rem;">
                            {{ $po->po_number }}
                        </a>
                    </td>
                    <td style="font-size:.82rem;">{{ $po->vendor?->name ?? '—' }}</td>
                    <td class="text-end fw-bold text-primary">&#8377;{{ number_format($po->total_amount, 2) }}</td>
                    <td><span class="spill {{ $statusColor }}">{{ $po->status }}</span></td>
                    <td>
                        @if($po->journal_entry_id)
                            <a href="{{ route('admin.finance.journal.show', $po->journal_entry_id) }}"
                               style="color:#4f46e5;font-weight:600;font-family:monospace;font-size:.75rem;">
                               JE #{{ $po->journal_entry_id }}
                            </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($po->payment_journal_entry_id)
                            <a href="{{ route('admin.finance.journal.show', $po->payment_journal_entry_id) }}"
                               style="color:#059669;font-weight:600;font-family:monospace;font-size:.75rem;">
                               JE #{{ $po->payment_journal_entry_id }}
                            </a>
                        @elseif($po->status === 'Received')
                            <span class="text-warning small"><i class="bi bi-clock me-1"></i>Pending</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty-state"><i class="bi bi-cart-check"></i><p>No purchase orders found</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchaseOrders->hasPages())
    <div class="px-3 py-2">{{ $purchaseOrders->withQueryString()->links() }}</div>
    @endif
</div>
@endif

</div>{{-- end card-glass --}}

@endsection