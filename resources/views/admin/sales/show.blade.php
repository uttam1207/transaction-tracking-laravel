@extends('layouts.app')
@section('title', 'Invoice — ' . $salesOrder->invoice_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
    <li class="breadcrumb-item active">{{ $salesOrder->invoice_number }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $salesOrder->invoice_number }}</h4>
            <p>{{ $salesOrder->sale_date->format('d F Y') }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.sales.print', $salesOrder) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-3">
                <i class="bi bi-printer me-1"></i>Print Invoice
            </a>
            <a href="{{ route('admin.sales.edit', $salesOrder) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.sales.destroy', $salesOrder) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-sm btn-danger px-4"
                    onclick="APP.confirm('Delete Invoice', 'Move this invoice to trash? The accounting entry will be reversed.', () => this.closest('form').submit())">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- Left: invoice detail --}}
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            {{-- Header band --}}
            <div style="background:linear-gradient(135deg,#059669,#16a34a);padding:24px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:54px;height:54px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-receipt-cutoff" style="font-size:1.45rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.2rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $salesOrder->invoice_number }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:2px;">
                            {{ $salesOrder->sale_date->format('d F Y') }}
                            &mdash; {{ $salesOrder->customer?->name ?? 'Walk-in Retail' }}
                        </div>
                    </div>
                    <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
                        @php
                            $pBadge = match($salesOrder->payment_status) {
                                'Paid'    => 'spill-success',
                                'Pending' => 'spill-warning',
                                'Partial' => 'spill-info',
                                default   => 'spill-secondary',
                            };
                        @endphp
                        <span class="spill {{ $pBadge }}" style="font-size:.8rem;">{{ $salesOrder->payment_status }}</span>
                        <div style="background:rgba(255,255,255,.18);border-radius:10px;padding:6px 14px;text-align:center;">
                            <div style="font-size:1.05rem;font-weight:800;color:#fff;">&#8377;{{ number_format($salesOrder->total_amount, 2) }}</div>
                            <div style="font-size:.63rem;color:rgba(255,255,255,.8);font-weight:600;">Invoice Total</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items table --}}
            <div class="p-4">
                <h6 class="form-section-label mb-3">Invoice Items</h6>
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.875rem;">
                        <thead style="background:#f8fafc;">
                            <tr style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;">
                                <th style="padding:9px 12px;width:36px;">#</th>
                                <th style="padding:9px 12px;">Item</th>
                                <th style="padding:9px 12px;">Description</th>
                                <th style="padding:9px 12px;text-align:right;">Qty</th>
                                <th style="padding:9px 12px;text-align:right;">Rate Details</th>
                                <th style="padding:9px 12px;text-align:right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $items = $salesOrder->items->isNotEmpty() ? $salesOrder->items : collect() @endphp

                            @if($items->isEmpty())
                                {{-- Legacy record with no items rows: show from parent fields --}}
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:10px 12px;color:#9ca3af;">1</td>
                                    <td style="padding:10px 12px;">
                                        <span class="spill spill-info" style="font-size:.78rem;">{{ $salesOrder->item_type }}</span>
                                    </td>
                                    <td style="padding:10px 12px;color:#6b7280;">—</td>
                                    <td style="padding:10px 12px;text-align:right;">{{ number_format($salesOrder->quantity, 2) }}</td>
                                    <td style="padding:10px 12px;text-align:right;color:#6b7280;font-size:.82rem;">
                                        @if($salesOrder->fat_percentage)
                                            Fat {{ $salesOrder->fat_percentage }}% × ₹{{ $salesOrder->fat_rate }}
                                        @else
                                            ₹{{ number_format($salesOrder->rate, 2) }}/unit
                                        @endif
                                    </td>
                                    <td style="padding:10px 12px;text-align:right;font-weight:700;color:#059669;">
                                        ₹{{ number_format($salesOrder->total_amount, 2) }}
                                    </td>
                                </tr>
                            @else
                                @foreach($items as $i => $item)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:10px 12px;color:#9ca3af;">{{ $i + 1 }}</td>
                                    <td style="padding:10px 12px;">
                                        <span class="spill spill-info" style="font-size:.78rem;">{{ $item->item_type }}</span>
                                    </td>
                                    <td style="padding:10px 12px;color:#6b7280;font-size:.83rem;">{{ $item->description ?: '—' }}</td>
                                    <td style="padding:10px 12px;text-align:right;">{{ number_format($item->quantity, 2) }}</td>
                                    <td style="padding:10px 12px;text-align:right;color:#6b7280;font-size:.82rem;">
                                        @if($item->fat_percentage)
                                            Fat {{ number_format($item->fat_percentage, 2) }}%
                                            × ₹{{ number_format($item->fat_rate, 2) }}
                                            <div style="font-size:.68rem;color:#9ca3af;">= ₹{{ number_format($item->rate, 2) }}/L eff.</div>
                                        @else
                                            ₹{{ number_format($item->rate, 2) }}/unit
                                        @endif
                                    </td>
                                    <td style="padding:10px 12px;text-align:right;font-weight:700;color:#059669;">
                                        ₹{{ number_format($item->amount, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr style="background:#f0fdf4;border-top:2px solid #d1fae5;">
                                <td colspan="5" class="text-end fw-bold pe-3" style="padding:12px 12px;color:#374151;">Grand Total</td>
                                <td style="padding:12px 12px;text-align:right;font-size:1.1rem;font-weight:800;color:#059669;">
                                    ₹{{ number_format($salesOrder->total_amount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: summary cards --}}
    <div class="col-lg-4 d-flex flex-column gap-3">

        {{-- Invoice Info --}}
        @php
            $isOverdue = $salesOrder->due_date
                && !in_array($salesOrder->payment_status, ['Paid'])
                && $salesOrder->due_date->lt(now()->startOfDay());
        @endphp
        <div class="card-glass p-3 {{ $isOverdue ? 'border border-danger-subtle' : '' }}">
            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px;">Invoice Info</div>
            <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size:.82rem;color:#6b7280;">Invoice #</span>
                    <span class="fw-bold" style="color:var(--primary);font-size:.85rem;">{{ $salesOrder->invoice_number }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size:.82rem;color:#6b7280;">Invoice Date</span>
                    <span class="fw-semibold" style="font-size:.85rem;">{{ $salesOrder->sale_date->format('d M Y') }}</span>
                </div>
                @if($salesOrder->due_date)
                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size:.82rem;color:#6b7280;">Due Date</span>
                    <span class="fw-semibold {{ $isOverdue ? 'text-danger' : '' }}" style="font-size:.85rem;">
                        {{ $salesOrder->due_date->format('d M Y') }}
                        @if($isOverdue)
                            <span style="font-size:.65rem;font-weight:700;display:block;color:#dc2626;">
                                OVERDUE {{ now()->startOfDay()->diffInDays($salesOrder->due_date) }}d
                            </span>
                        @endif
                    </span>
                </div>
                @endif
                @if($salesOrder->payment_terms)
                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size:.82rem;color:#6b7280;">Terms</span>
                    <span style="font-size:.82rem;color:#374151;">{{ $salesOrder->payment_terms }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size:.82rem;color:#6b7280;">Customer</span>
                    <span class="fw-semibold" style="font-size:.83rem;text-align:right;max-width:160px;">{{ $salesOrder->customer?->name ?? 'Walk-in Retail' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size:.82rem;color:#6b7280;">Line Items</span>
                    <span class="fw-semibold" style="font-size:.85rem;">{{ $items->count() ?: 1 }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span style="font-size:.82rem;color:#6b7280;">Created</span>
                    <span style="font-size:.8rem;color:#9ca3af;">{{ $salesOrder->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Payment Summary --}}
        <div class="card-glass p-3">
            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px;">Payment Summary</div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:.82rem;color:#6b7280;">Invoice Total</span>
                <span class="fw-bold" style="font-size:1rem;color:#1f2937;">₹{{ number_format($salesOrder->total_amount, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:.82rem;color:#6b7280;">Amount Paid</span>
                <span class="fw-semibold" style="font-size:.9rem;color:#059669;">₹{{ number_format($salesOrder->amount_paid, 2) }}</span>
            </div>
            @if($salesOrder->outstanding > 0)
            <div class="d-flex justify-content-between align-items-center pt-2" style="border-top:1px solid #fde68a;">
                <span style="font-size:.82rem;color:#b45309;font-weight:600;">Outstanding</span>
                <span class="fw-bold" style="font-size:.95rem;color:#d97706;">₹{{ number_format($salesOrder->outstanding, 2) }}</span>
            </div>
            @else
            <div class="d-flex justify-content-between align-items-center pt-2" style="border-top:1px solid #d1fae5;">
                <span style="font-size:.82rem;color:#059669;font-weight:600;">Fully Settled</span>
                <i class="bi bi-check-circle-fill" style="color:#059669;"></i>
            </div>
            @endif
            <div class="mt-3 text-center">
                @php
                    $pBadge = match($salesOrder->payment_status) {
                        'Paid'    => 'spill-success',
                        'Pending' => 'spill-warning',
                        'Partial' => 'spill-info',
                        default   => 'spill-secondary',
                    };
                @endphp
                <span class="spill {{ $pBadge }}" style="font-size:.85rem;padding:5px 18px;">{{ $salesOrder->payment_status }}</span>
            </div>
        </div>

        {{-- Accounting --}}
        @if($salesOrder->journal_entry_id)
        <div class="card-glass p-3">
            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px;">Accounting</div>
            <div class="d-flex justify-content-between align-items-center">
                <span style="font-size:.82rem;color:#6b7280;">Journal Entry</span>
                <a href="{{ route('admin.finance.journal.show', $salesOrder->journal_entry_id) }}" style="font-size:.83rem;font-weight:600;color:var(--primary);">
                    View JE <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="mt-2" style="background:#f0fdf4;border-radius:8px;padding:8px 12px;font-size:.78rem;color:#059669;">
                <i class="bi bi-check-circle-fill me-1"></i>Posted to General Ledger
            </div>
        </div>
        @endif

        {{-- Linked Transaction (auto-generated invoice) --}}
        @if($salesOrder->transaction_id)
        <div class="card-glass p-3" style="border:1.5px solid #e0e7ff;">
            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:12px;">
                <i class="bi bi-link-45deg me-1" style="color:#4f46e5;"></i>Linked Transaction
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:.82rem;color:#6b7280;">TXN ID</span>
                <a href="{{ route('admin.transactions.show', $salesOrder->transaction_id) }}"
                   style="font-size:.8rem;font-weight:700;color:#4f46e5;font-family:monospace;text-decoration:none;">
                    {{ $salesOrder->transaction?->transaction_id ?? '#'.$salesOrder->transaction_id }}
                    <i class="bi bi-arrow-right ms-1" style="font-family:sans-serif;"></i>
                </a>
            </div>
            <div style="background:#eef2ff;border-radius:8px;padding:7px 12px;font-size:.75rem;color:#4f46e5;">
                <i class="bi bi-info-circle me-1"></i>This invoice was auto-generated from a transaction
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
