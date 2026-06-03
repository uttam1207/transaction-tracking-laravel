<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Transaction Receipt — {{ $transaction->transaction_id }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'DejaVu Sans',Arial,sans-serif; font-size:10px; color:#111; background:#fff; padding:24px; }

    .header { display:table; width:100%; margin-bottom:20px; border-bottom:2px solid #1e1b4b; padding-bottom:14px; }
    .header-left { display:table-cell; vertical-align:middle; }
    .header-right { display:table-cell; text-align:right; vertical-align:middle; }
    .brand { font-size:18px; font-weight:800; color:#1e1b4b; letter-spacing:-.5px; }
    .brand-sub { font-size:8px; color:#6b7280; text-transform:uppercase; letter-spacing:1px; margin-top:2px; }
    .receipt-title { font-size:16px; font-weight:800; color:#1e1b4b; }
    .receipt-id { font-size:9px; color:#6b7280; margin-top:3px; font-family:monospace; }

    .status-banner {
        text-align:center; padding:12px; border-radius:8px; margin-bottom:20px;
        font-size:13px; font-weight:800; letter-spacing:.3px;
    }
    .status-success  { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
    .status-failed   { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }
    .status-pending  { background:#fef9c3; color:#ca8a04; border:1px solid #fde047; }
    .status-processing { background:#dbeafe; color:#2563eb; border:1px solid #bfdbfe; }
    .status-cancelled  { background:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb; }
    .status-reversed   { background:#ede9fe; color:#7c3aed; border:1px solid #ddd6fe; }

    .amount-section { text-align:center; padding:16px 0 20px; border-bottom:1px dashed #e5e7eb; margin-bottom:20px; }
    .amount-label { font-size:8px; color:#9ca3af; text-transform:uppercase; letter-spacing:.8px; margin-bottom:6px; }
    .amount-value { font-size:28px; font-weight:900; color:#1e1b4b; letter-spacing:-1px; }
    .amount-type-credit .amount-value { color:#15803d; }
    .amount-type-debit  .amount-value { color:#dc2626; }
    .amount-meta { font-size:8.5px; color:#6b7280; margin-top:4px; }

    .details-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    .details-table tr:nth-child(even) td { background:#f8fafc; }
    .details-table td { padding:7px 10px; border:1px solid #e5e7eb; vertical-align:top; }
    .details-table .lbl { width:35%; font-size:8px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#9ca3af; }
    .details-table .val { font-size:9.5px; color:#111; font-weight:500; }
    .details-table .val.mono { font-family:monospace; color:#4f46e5; }

    .parties { display:table; width:100%; margin-bottom:20px; border-collapse:separate; border-spacing:10px; }
    .party-cell { display:table-cell; width:48%; background:#f8fafc; border:1px solid #e5e7eb; border-radius:6px; padding:10px 12px; vertical-align:top; }
    .party-role { font-size:7.5px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; margin-bottom:4px; }
    .party-name { font-size:10px; font-weight:700; color:#111; margin-bottom:3px; }
    .party-meta-line { font-size:8.5px; color:#6b7280; margin-top:2px; }

    .section-title { font-size:8px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; margin-bottom:8px; padding-bottom:4px; border-bottom:1px solid #f3f4f6; }

    .timeline { margin-bottom:20px; }
    .tl-row { display:table; width:100%; margin-bottom:6px; }
    .tl-dot-cell { display:table-cell; width:20px; vertical-align:top; padding-top:2px; }
    .tl-dot { width:8px; height:8px; border-radius:50%; background:#7c3aed; display:inline-block; }
    .tl-content { display:table-cell; vertical-align:top; }
    .tl-action { font-size:8.5px; font-weight:700; color:#111; }
    .tl-meta { font-size:8px; color:#9ca3af; }

    .footer { text-align:center; font-size:7.5px; color:#9ca3af; border-top:1px solid #e5e7eb; padding-top:10px; margin-top:20px; }
    .pill { display:inline-block; padding:2px 8px; border-radius:10px; font-size:7.5px; font-weight:700; }
    .pill-success { background:#dcfce7; color:#15803d; }
    .pill-debit { background:#fee2e2; color:#dc2626; }
    .pill-credit { background:#dcfce7; color:#15803d; }
    .watermark { color:#f0f0f0; font-size:60px; font-weight:900; position:fixed; top:35%; left:15%; transform:rotate(-35deg); z-index:-1; letter-spacing:2px; }
</style>
</head>
<body>

@if($transaction->status === 'success')
<div class="watermark">PAID</div>
@endif

{{-- Header --}}
<div class="header">
    <div class="header-left">
        <div class="brand">AS Dairy Dashboard</div>
        <div class="brand-sub">Pro &nbsp;·&nbsp; Financial Management System</div>
    </div>
    <div class="header-right">
        <div class="receipt-title">TRANSACTION RECEIPT</div>
        <div class="receipt-id">{{ $transaction->transaction_id }}</div>
        <div style="font-size:8px; color:#6b7280; margin-top:2px;">{{ $transaction->created_at->format('d M Y, H:i:s') }}</div>
    </div>
</div>

{{-- Status Banner --}}
<div class="status-banner status-{{ $transaction->status }}">
    {{ strtoupper($transaction->status) }}
    @if($transaction->is_flagged) &nbsp;&bull;&nbsp; ⚑ FLAGGED FOR FRAUD REVIEW @endif
</div>

{{-- Amount --}}
<div class="amount-section amount-type-{{ $transaction->type }}">
    <div class="amount-label">Net Amount</div>
    <div class="amount-value">₹{{ number_format($transaction->net_amount, 2) }}</div>
    <div class="amount-meta">
        Gross: ₹{{ number_format($transaction->amount, 2) }}
        &nbsp;&bull;&nbsp;
        Fee: ₹{{ number_format($transaction->fee, 2) }}
        &nbsp;&bull;&nbsp;
        <span class="pill pill-{{ $transaction->type }}">{{ ucfirst($transaction->type) }}</span>
    </div>
</div>

{{-- Transaction Details --}}
<div class="section-title">Transaction Details</div>
<table class="details-table">
    <tr>
        <td class="lbl">Transaction ID</td>
        <td class="val mono">{{ $transaction->transaction_id }}</td>
        <td class="lbl">Date & Time</td>
        <td class="val">{{ $transaction->created_at->format('d M Y, H:i:s') }}</td>
    </tr>
    <tr>
        <td class="lbl">Category</td>
        <td class="val">{{ ucfirst($transaction->category) }}</td>
        <td class="lbl">Payment Method</td>
        <td class="val">{{ str_replace('_',' ',ucfirst($transaction->payment_method ?? 'N/A')) }}</td>
    </tr>
    <tr>
        <td class="lbl">Currency</td>
        <td class="val">{{ $transaction->currency }}</td>
        <td class="lbl">Reference</td>
        <td class="val mono">{{ $transaction->reference ?? '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Risk Score</td>
        <td class="val">{{ $transaction->risk_score }}/100</td>
        <td class="lbl">Processed At</td>
        <td class="val">{{ $transaction->processed_at ? $transaction->processed_at->format('d M Y, H:i') : '—' }}</td>
    </tr>
    @if($transaction->description)
    <tr>
        <td class="lbl">Description</td>
        <td class="val" colspan="3">{{ $transaction->description }}</td>
    </tr>
    @endif
</table>

{{-- Transfer Parties --}}
<div class="section-title">Transfer Parties</div>
<div class="parties">
    <div class="party-cell">
        <div class="party-role">Sender</div>
        <div class="party-name">{{ $transaction->sender_name ?? 'N/A' }}</div>
        @if($transaction->sender_mobile)<div class="party-meta-line">{{ $transaction->sender_mobile }}</div>@endif
        @if($transaction->sender_company)<div class="party-meta-line">{{ $transaction->sender_company }}</div>@endif
        @if($transaction->sender_account)<div class="party-meta-line">A/C: {{ $transaction->sender_account }}</div>@endif
        @if($transaction->sender_bank)<div class="party-meta-line">{{ $transaction->sender_bank }}</div>@endif
    </div>
    <div class="party-cell">
        <div class="party-role">Receiver</div>
        <div class="party-name">{{ $transaction->receiver_name ?? 'N/A' }}</div>
        @if($transaction->receiver_mobile)<div class="party-meta-line">{{ $transaction->receiver_mobile }}</div>@endif
        @if($transaction->receiver_company)<div class="party-meta-line">{{ $transaction->receiver_company }}</div>@endif
        @if($transaction->receiver_address)<div class="party-meta-line">{{ $transaction->receiver_address }}</div>@endif
        @if($transaction->receiver_account)<div class="party-meta-line">A/C: {{ $transaction->receiver_account }}</div>@endif
        @if($transaction->receiver_bank)<div class="party-meta-line">{{ $transaction->receiver_bank }}</div>@endif
    </div>
</div>

{{-- Account Owner --}}
@php $extOwner = $transaction->metadata['external_owner'] ?? null; @endphp
@if($transaction->user || $extOwner)
<div class="section-title">Account Owner</div>
<table class="details-table" style="margin-bottom:20px;">
    <tr>
        <td class="lbl">Name</td>
        <td class="val">{{ $extOwner ? $extOwner['name'] : $transaction->user->name }}</td>
        <td class="lbl">Type</td>
        <td class="val">{{ $extOwner ? 'External Person' : ucfirst(str_replace('_',' ',$transaction->user->role)) }}</td>
    </tr>
    @if($extOwner && !empty($extOwner['mobile']))
    <tr>
        <td class="lbl">Mobile</td>
        <td class="val" colspan="3">{{ $extOwner['mobile'] }}</td>
    </tr>
    @endif
    @if(!$extOwner && $transaction->user)
    <tr>
        <td class="lbl">Email</td>
        <td class="val" colspan="3">{{ $transaction->user->email }}</td>
    </tr>
    @endif
</table>
@endif

{{-- Activity Log --}}
@if($transaction->logs->count())
<div class="section-title">Activity Timeline</div>
<div class="timeline">
    @foreach($transaction->logs as $log)
    <div class="tl-row">
        <div class="tl-dot-cell"><div class="tl-dot"></div></div>
        <div class="tl-content">
            <div class="tl-action">
                {{ ucwords(str_replace('_',' ',$log->action)) }}
                @if($log->from_status && $log->to_status)
                    &nbsp;{{ $log->from_status }} → {{ $log->to_status }}
                @endif
            </div>
            <div class="tl-meta">
                {{ $log->performer?->name ?? 'System' }}
                &bull; {{ $log->created_at->format('d M Y, H:i') }}
                @if($log->notes) &bull; {{ $log->notes }} @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="footer">
    <strong>AS Dairy Dashboard</strong> &mdash; This is a computer-generated receipt and does not require a physical signature.
    &mdash; Printed on {{ now()->format('d M Y H:i') }}
    &mdash; Confidential
</div>

</body>
</html>
