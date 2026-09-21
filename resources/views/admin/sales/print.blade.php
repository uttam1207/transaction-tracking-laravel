<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAX INVOICE — {{ $salesOrder->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #f5f5f5;
        }

        .invoice-page {
            background: #fff;
            max-width: 800px;
            margin: 20px auto;
            padding: 0;
            border: 1px solid #ddd;
        }

        /* ── Header ── */
        .inv-header {
            background: linear-gradient(135deg, #059669, #16a34a);
            color: #fff;
            padding: 28px 36px 22px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .firm-name    { font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
        .firm-tagline { font-size: 12px; opacity: .8; margin-top: 3px; }
        .firm-address { font-size: 11px; opacity: .75; margin-top: 6px; line-height: 1.6; }
        .inv-title-block { text-align: right; }
        .inv-title    { font-size: 18px; font-weight: 800; letter-spacing: .04em; }
        .inv-subtitle { font-size: 11px; opacity: .75; margin-top: 3px; }
        .inv-number   { font-size: 13px; font-weight: 700; margin-top: 8px; background: rgba(255,255,255,.2); padding: 4px 12px; border-radius: 6px; display: inline-block; }

        /* ── Status banner ── */
        .status-banner {
            padding: 7px 36px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-paid    { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-partial { background: #dbeafe; color: #1e40af; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .status-unbilled{ background: #f3f4f6; color: #374151; }

        /* ── Bill-to / info strip ── */
        .info-strip {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-block {
            flex: 1;
            padding: 16px 20px;
            border-right: 1px solid #e5e7eb;
        }
        .info-block:last-child { border-right: none; }
        .info-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: 5px; }
        .info-value { font-size: 13px; font-weight: 600; color: #1f2937; line-height: 1.5; }
        .info-value-sm { font-size: 12px; color: #374151; line-height: 1.5; }

        /* ── Items table ── */
        .items-wrap { padding: 20px 36px; }
        .items-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin-bottom: 10px; }

        table.items {
            width: 100%;
            border-collapse: collapse;
        }
        table.items thead th {
            background: #f8fafc;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b7280;
            padding: 9px 12px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }
        table.items thead th.text-right { text-align: right; }
        table.items tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            font-size: 12.5px;
        }
        table.items tbody tr:last-child td { border-bottom: none; }
        table.items .text-right { text-align: right; }

        .item-type-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 1px 7px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 700;
        }
        .rate-detail { font-size: 11px; color: #9ca3af; margin-top: 2px; }

        /* ── Totals ── */
        .totals-wrap {
            padding: 0 36px 20px;
            display: flex;
            justify-content: flex-end;
        }
        .totals-box {
            min-width: 280px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 16px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
        }
        .totals-row:last-child { border-bottom: none; }
        .totals-row.grand {
            background: #059669;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            border-bottom: none;
        }
        .totals-row.outstanding {
            background: #fef3c7;
            color: #92400e;
            font-weight: 700;
        }
        .totals-row.settled {
            background: #d1fae5;
            color: #065f46;
            font-weight: 700;
        }

        /* ── Notes ── */
        .notes-section {
            margin: 0 36px 24px;
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 12px;
            color: #4b5563;
            border-left: 3px solid #d1d5db;
        }
        .notes-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: 4px; }

        /* ── Footer ── */
        .inv-footer {
            border-top: 2px solid #e5e7eb;
            padding: 16px 36px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .footer-note { font-size: 11px; color: #9ca3af; line-height: 1.6; }
        .signature-block { text-align: right; }
        .signature-line { width: 160px; border-top: 1px solid #374151; margin: 32px 0 4px auto; }
        .signature-label { font-size: 11px; color: #6b7280; text-align: right; }

        /* ── Print actions (not printed) ── */
        .print-actions {
            max-width: 800px;
            margin: 0 auto 12px;
            display: flex;
            gap: 10px;
            padding: 0 4px;
        }
        .btn-print {
            background: #059669; color: #fff;
            border: none; border-radius: 8px;
            padding: 9px 22px; font-size: 13px; font-weight: 600;
            cursor: pointer;
        }
        .btn-back {
            background: #f3f4f6; color: #374151;
            border: 1.5px solid #e5e7eb; border-radius: 8px;
            padding: 9px 22px; font-size: 13px; font-weight: 600;
            text-decoration: none; cursor: pointer;
        }

        @media print {
            body { background: #fff; }
            .invoice-page { margin: 0; border: none; max-width: 100%; }
            .print-actions { display: none !important; }
        }
    </style>
</head>
<body>

@php
    $items       = $salesOrder->items->isNotEmpty() ? $salesOrder->items : collect();
    $outstanding = max(0, (float)$salesOrder->total_amount - (float)$salesOrder->amount_paid);
    $isOverdue   = $salesOrder->due_date
        && !in_array($salesOrder->payment_status, ['Paid'])
        && $salesOrder->due_date->lt(now()->startOfDay());
    $statusClass = $isOverdue ? 'status-overdue' : match($salesOrder->payment_status) {
        'Paid'     => 'status-paid',
        'Partial'  => 'status-partial',
        'Unbilled' => 'status-unbilled',
        default    => 'status-pending',
    };
    $statusLabel = $isOverdue ? 'OVERDUE' : strtoupper($salesOrder->payment_status);
@endphp

{{-- Print / Back buttons --}}
<div class="print-actions">
    <button class="btn-print" onclick="window.print()">
        &#128424; Print Invoice
    </button>
    <a href="{{ route('admin.sales.show', $salesOrder) }}" class="btn-back">
        &#8592; Back to Invoice
    </a>
</div>

<div class="invoice-page">

    {{-- Header --}}
    <div class="inv-header">
        <div>
            <div class="firm-name">AS DAIRY FARM</div>
            <div class="firm-tagline">Premium Dairy Products</div>
            <div class="firm-address">
                Village Asadia, District Varanasi<br>
                Uttar Pradesh — 221 XXX, India<br>
                GSTIN: 09XXXXX0000X1ZX &nbsp;|&nbsp; PAN: XXXXX0000X
            </div>
        </div>
        <div class="inv-title-block">
            <div class="inv-title">TAX INVOICE</div>
            <div class="inv-subtitle">Original for Recipient</div>
            <div class="inv-number">{{ $salesOrder->invoice_number }}</div>
        </div>
    </div>

    {{-- Status banner --}}
    <div class="status-banner {{ $statusClass }}">
        <span>Payment Status: {{ $statusLabel }}</span>
        @if($salesOrder->payment_terms)
            <span>Terms: {{ $salesOrder->payment_terms }}</span>
        @endif
        <span>{{ $salesOrder->sale_date->format('d F Y') }}</span>
    </div>

    {{-- Bill-to / dates strip --}}
    <div class="info-strip">
        <div class="info-block" style="flex:2;">
            <div class="info-label">Bill To</div>
            @if($salesOrder->customer)
                <div class="info-value">{{ $salesOrder->customer->name }}</div>
                @if($salesOrder->customer->phone)
                    <div class="info-value-sm">{{ $salesOrder->customer->phone }}</div>
                @endif
                @if($salesOrder->customer->address)
                    <div class="info-value-sm">{{ $salesOrder->customer->address }}</div>
                @endif
            @else
                <div class="info-value">Walk-in Customer</div>
                <div class="info-value-sm" style="color:#9ca3af;">Retail / Cash Sale</div>
            @endif
        </div>
        <div class="info-block">
            <div class="info-label">Invoice Date</div>
            <div class="info-value">{{ $salesOrder->sale_date->format('d M Y') }}</div>
            @if($salesOrder->due_date)
                <div class="info-label" style="margin-top:10px;">Due Date</div>
                <div class="info-value {{ $isOverdue ? '' : '' }}" style="{{ $isOverdue ? 'color:#dc2626;' : '' }}">
                    {{ $salesOrder->due_date->format('d M Y') }}
                    @if($isOverdue)
                        <span style="font-size:10px;font-weight:700;color:#dc2626;display:block;">OVERDUE</span>
                    @endif
                </div>
            @endif
        </div>
        <div class="info-block">
            <div class="info-label">Invoice No.</div>
            <div class="info-value" style="color:#059669;">{{ $salesOrder->invoice_number }}</div>
            <div class="info-label" style="margin-top:10px;">Item Category</div>
            <div class="info-value-sm">{{ $salesOrder->item_type }}</div>
        </div>
    </div>

    {{-- Items table --}}
    <div class="items-wrap">
        <div class="items-title">Invoice Items</div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width:30px;">#</th>
                    <th>Item / Description</th>
                    <th class="text-right" style="width:80px;">Qty</th>
                    <th class="text-right" style="width:120px;">Rate / Details</th>
                    <th class="text-right" style="width:100px;">Amount (&#8377;)</th>
                </tr>
            </thead>
            <tbody>
                @if($items->isEmpty())
                    {{-- Legacy single-item record --}}
                    <tr>
                        <td>1</td>
                        <td>
                            <span class="item-type-badge">{{ $salesOrder->item_type }}</span>
                        </td>
                        <td class="text-right">{{ number_format($salesOrder->quantity, 2) }}</td>
                        <td class="text-right">
                            @if($salesOrder->fat_percentage)
                                Fat {{ number_format($salesOrder->fat_percentage, 2) }}%
                                &times; &#8377;{{ number_format($salesOrder->fat_rate, 2) }}
                                <div class="rate-detail">= &#8377;{{ number_format($salesOrder->rate, 2) }}/L eff.</div>
                            @else
                                &#8377;{{ number_format($salesOrder->rate, 2) }}/unit
                            @endif
                        </td>
                        <td class="text-right" style="font-weight:700;">&#8377;{{ number_format($salesOrder->total_amount, 2) }}</td>
                    </tr>
                @else
                    @foreach($items as $idx => $item)
                    <tr>
                        <td style="color:#9ca3af;">{{ $idx + 1 }}</td>
                        <td>
                            <span class="item-type-badge">{{ $item->item_type }}</span>
                            @if($item->description)
                                <div style="font-size:12px;color:#4b5563;margin-top:3px;">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">
                            @if($item->fat_percentage)
                                Fat {{ number_format($item->fat_percentage, 2) }}%
                                &times; &#8377;{{ number_format($item->fat_rate, 2) }}
                                <div class="rate-detail">= &#8377;{{ number_format($item->rate, 2) }}/L eff.</div>
                            @else
                                &#8377;{{ number_format($item->rate, 2) }}/unit
                            @endif
                        </td>
                        <td class="text-right" style="font-weight:700;">&#8377;{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="totals-wrap">
        <div class="totals-box">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>&#8377;{{ number_format($salesOrder->total_amount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span style="color:#9ca3af;">Tax (GST)</span>
                <span style="color:#9ca3af;">Inclusive</span>
            </div>
            <div class="totals-row grand">
                <span>Invoice Total</span>
                <span>&#8377;{{ number_format($salesOrder->total_amount, 2) }}</span>
            </div>
            @if($salesOrder->payment_status !== 'Paid')
                <div class="totals-row">
                    <span>Amount Paid</span>
                    <span style="color:#059669;font-weight:600;">&#8377;{{ number_format($salesOrder->amount_paid, 2) }}</span>
                </div>
                <div class="totals-row {{ $isOverdue ? 'outstanding' : 'outstanding' }}">
                    <span>Balance Due</span>
                    <span style="{{ $isOverdue ? 'color:#991b1b;' : '' }}">&#8377;{{ number_format($outstanding, 2) }}</span>
                </div>
            @else
                <div class="totals-row settled">
                    <span>&#10003; Fully Paid</span>
                    <span>&#8377;{{ number_format($salesOrder->amount_paid, 2) }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer --}}
    <div class="inv-footer">
        <div class="footer-note">
            <div style="font-weight:600;margin-bottom:4px;">Terms &amp; Conditions</div>
            1. Goods once sold will not be taken back.<br>
            2. Interest @ 18% p.a. will be charged on delayed payments.<br>
            3. All disputes are subject to Varanasi jurisdiction.
            @if($salesOrder->payment_terms)
                <br>4. Payment terms: {{ $salesOrder->payment_terms }}.
            @endif
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <div class="signature-label">Authorised Signatory<br><strong>AS DAIRY FARM</strong></div>
        </div>
    </div>

    {{-- Watermark for paid --}}
    @if($salesOrder->payment_status === 'Paid')
    <div style="text-align:center;padding:6px 0 14px;font-size:11px;color:#9ca3af;border-top:1px solid #f1f5f9;">
        <span style="background:#d1fae5;color:#065f46;padding:3px 14px;border-radius:20px;font-weight:700;font-size:11px;">
            &#10003; PAID IN FULL — {{ $salesOrder->sale_date->format('d M Y') }}
        </span>
    </div>
    @endif

</div>

</body>
</html>
