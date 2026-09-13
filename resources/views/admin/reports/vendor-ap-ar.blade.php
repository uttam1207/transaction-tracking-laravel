@extends('layouts.app')
@section('title', 'Vendor AP/AR Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.transactions') }}">Reports</a></li>
    <li class="breadcrumb-item active">Vendor AP / AR</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Vendor Accounts Payable &amp; Receivable</h4>
            <p>Outstanding amounts to pay and receive per vendor</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.procurement.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-seam me-1"></i>Purchase Orders
            </a>
            <button onclick="window.print()" class="btn btn-sm" style="background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #6366f1;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Vendors</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">{{ $vendors->count() }}</div>
            <div style="font-size:.72rem;color:#6366f1;margin-top:3px;font-weight:600;">with purchase orders</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #3b82f6;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Committed</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">₹{{ number_format($grandCommitted, 2) }}</div>
            <div style="font-size:.72rem;color:#3b82f6;margin-top:3px;font-weight:600;">all purchase orders</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #10b981;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Paid</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">₹{{ number_format($grandPaid, 2) }}</div>
            <div style="font-size:.72rem;color:#10b981;margin-top:3px;font-weight:600;">settled / cleared</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #ef4444;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Outstanding (To Pay)</div>
            <div style="font-size:1.4rem;font-weight:800;color:#dc2626;margin-top:5px;line-height:1.2;">₹{{ number_format($grandOutstanding, 2) }}</div>
            <div style="font-size:.72rem;color:#ef4444;margin-top:3px;font-weight:600;">pending payment</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div style="background:#fff;border-radius:14px;padding:16px 22px;box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:22px;">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">Vendor</label>
            <select name="vendor_id" class="form-select form-select-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                <option value="">— All Vendors —</option>
                @foreach ($allVendors as $v)
                    <option value="{{ $v->id }}" {{ $v->id == $vendorId ? 'selected' : '' }}>{{ $v->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">Status</label>
            <select name="status" class="form-select form-select-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                <option value="">— All —</option>
                @foreach (['Draft','Sent','Received','Paid'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">From</label>
            <input type="date" name="date_from" value="{{ $dateFrom ? $dateFrom->format('Y-m-d') : '' }}"
                   class="form-control form-control-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">To</label>
            <input type="date" name="date_to" value="{{ $dateTo ? $dateTo->format('Y-m-d') : '' }}"
                   class="form-control form-control-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary-grad btn-sm px-4">Filter</button>
            <a href="{{ route('admin.reports.vendor-ap-ar') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

{{-- Vendor Summary Table --}}
<div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden;margin-bottom:28px;">
    <div style="padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-weight:700;font-size:.92rem;color:#1e293b;">
            <i class="bi bi-people me-2" style="color:#6366f1;"></i>Vendor Summary
        </div>
        <div style="font-size:.78rem;color:#6b7280;">{{ $vendors->count() }} vendor(s)</div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size:.83rem;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Vendor</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:center;border:none;">POs</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Committed (₹)</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Paid (₹)</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Outstanding (₹)</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Payment Progress</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vendors as $vendor)
                @php
                    $pct = $vendor->total_committed > 0
                        ? round(($vendor->total_paid / $vendor->total_committed) * 100)
                        : 0;
                    $barColor = $pct >= 100 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td style="padding:12px 22px;border:none;">
                        <div style="font-weight:600;color:#1e293b;">{{ $vendor->name }}</div>
                        @if ($vendor->contact_person)
                            <div style="font-size:.72rem;color:#9ca3af;">{{ $vendor->contact_person }}</div>
                        @endif
                    </td>
                    <td style="padding:12px 22px;text-align:center;border:none;">
                        <span style="background:#f0f4ff;color:#4f46e5;padding:2px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">
                            {{ $vendor->po_count }}
                        </span>
                    </td>
                    <td style="padding:12px 22px;text-align:right;font-weight:600;color:#374151;border:none;">
                        {{ number_format($vendor->total_committed, 2) }}
                    </td>
                    <td style="padding:12px 22px;text-align:right;font-weight:600;color:#059669;border:none;">
                        {{ number_format($vendor->total_paid, 2) }}
                    </td>
                    <td style="padding:12px 22px;text-align:right;font-weight:700;border:none;color:{{ $vendor->total_outstanding > 0 ? '#dc2626' : '#059669' }};">
                        {{ number_format($vendor->total_outstanding, 2) }}
                    </td>
                    <td style="padding:12px 22px;border:none;min-width:140px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;background:#f1f5f9;border-radius:99px;height:7px;overflow:hidden;">
                                <div style="width:{{ $pct }}%;background:{{ $barColor }};height:100%;border-radius:99px;transition:width .3s;"></div>
                            </div>
                            <span style="font-size:.72rem;font-weight:700;color:{{ $barColor }};min-width:32px;">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td style="padding:12px 22px;border:none;">
                        <a href="{{ route('admin.reports.vendor-ap-ar') }}?vendor_id={{ $vendor->id }}"
                           class="btn btn-outline-primary btn-sm" style="font-size:.72rem;padding:3px 10px;">
                            View POs
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:#9ca3af;padding:44px;border:none;">
                        <i class="bi bi-building" style="font-size:2.2rem;display:block;margin-bottom:10px;color:#d1d5db;"></i>
                        No vendors with purchase orders found.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if ($vendors->isNotEmpty())
            <tfoot>
                <tr style="background:#1e293b;">
                    <td colspan="2" style="padding:13px 22px;text-align:right;font-weight:700;color:#94a3b8;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;border:none;">Totals</td>
                    <td style="padding:13px 22px;text-align:right;font-weight:800;color:#93c5fd;font-size:.95rem;border:none;">₹{{ number_format($grandCommitted, 2) }}</td>
                    <td style="padding:13px 22px;text-align:right;font-weight:800;color:#6ee7b7;font-size:.95rem;border:none;">₹{{ number_format($grandPaid, 2) }}</td>
                    <td style="padding:13px 22px;text-align:right;font-weight:800;color:#fca5a5;font-size:.95rem;border:none;">₹{{ number_format($grandOutstanding, 2) }}</td>
                    <td colspan="2" style="border:none;"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Individual Purchase Orders --}}
<div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden;">
    <div style="padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-weight:700;font-size:.92rem;color:#1e293b;">
            <i class="bi bi-receipt me-2" style="color:#3b82f6;"></i>Purchase Order Details
        </div>
        <div style="font-size:.78rem;color:#6b7280;">{{ $orders->total() }} order(s)</div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size:.83rem;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">PO #</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Vendor</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Order Date</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Amount (₹)</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:center;border:none;">Status</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Remaining (₹)</th>
                    <th style="padding:9px 22px;border:none;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                @php
                    $statusColors = [
                        'Draft'    => 'background:#f3f4f6;color:#374151;',
                        'Sent'     => 'background:#dbeafe;color:#1d4ed8;',
                        'Received' => 'background:#fef3c7;color:#d97706;',
                        'Paid'     => 'background:#d1fae5;color:#059669;',
                    ];
                    $sc = $statusColors[$order->status] ?? 'background:#f3f4f6;color:#374151;';
                    $remaining = $order->status === 'Paid' ? 0 : $order->total_amount;
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td style="padding:10px 22px;border:none;">
                        <span style="background:#f0f4ff;color:#4f46e5;padding:2px 8px;border-radius:5px;font-size:.75rem;font-weight:700;font-family:monospace;">
                            {{ $order->po_number }}
                        </span>
                    </td>
                    <td style="padding:10px 22px;font-weight:500;color:#374151;border:none;">{{ $order->vendor->name ?? '—' }}</td>
                    <td style="padding:10px 22px;color:#6b7280;border:none;">{{ $order->order_date->format('d M Y') }}</td>
                    <td style="padding:10px 22px;text-align:right;font-weight:600;color:#374151;border:none;">
                        {{ number_format($order->total_amount, 2) }}
                    </td>
                    <td style="padding:10px 22px;text-align:center;border:none;">
                        <span style="padding:3px 12px;border-radius:20px;font-size:.71rem;font-weight:700;{{ $sc }}">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td style="padding:10px 22px;text-align:right;font-weight:700;border:none;color:{{ $remaining > 0 ? '#dc2626' : '#059669' }};">
                        {{ $remaining > 0 ? number_format($remaining, 2) : '—' }}
                    </td>
                    <td style="padding:10px 22px;border:none;">
                        <a href="{{ route('admin.procurement.show', $order) }}"
                           class="btn btn-outline-secondary btn-sm" style="font-size:.72rem;padding:3px 10px;">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:#9ca3af;padding:44px;border:none;">
                        <i class="bi bi-inbox" style="font-size:2.2rem;display:block;margin-bottom:10px;color:#d1d5db;"></i>
                        No purchase orders found for the selected filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($orders->hasPages())
    <div style="padding:14px 22px;border-top:1px solid #f1f5f9;">
        {{ $orders->links() }}
    </div>
    @endif
</div>

@endsection
