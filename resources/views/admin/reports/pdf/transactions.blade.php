<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Transaction Report</title>
<style>
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color: #111; margin: 0; padding: 16px; }
    h1 { font-size: 15px; font-weight: 700; margin: 0 0 2px; color: #1e1b4b; }
    .meta { font-size: 9px; color: #6b7280; margin-bottom: 14px; }
    .summary-box {
        display: table; width: 100%; border-collapse: collapse;
        margin-bottom: 14px; background: #f8fafc;
        border: 1px solid #e5e7eb; border-radius: 6px;
    }
    .sum-cell { display: table-cell; padding: 10px 14px; border-right: 1px solid #e5e7eb; }
    .sum-cell:last-child { border-right: none; }
    .sum-label { font-size: 7.5px; color: #9ca3af; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
    .sum-value { font-size: 13px; font-weight: 800; color: #1e1b4b; }
    .sum-value.green { color: #15803d; }
    .sum-value.red   { color: #dc2626; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1e1b4b; color: #fff; font-size: 8px; font-weight: 700; text-transform: uppercase;
         letter-spacing: .4px; padding: 7px 6px; text-align: left; white-space: nowrap; }
    td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; font-size: 8.5px; vertical-align: middle; }
    tr:nth-child(even) td { background: #f8fafc; }
    .pill { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 7.5px; font-weight: 700; }
    .pill-success    { background: #dcfce7; color: #15803d; }
    .pill-failed     { background: #fee2e2; color: #dc2626; }
    .pill-pending    { background: #fef9c3; color: #ca8a04; }
    .pill-processing { background: #dbeafe; color: #2563eb; }
    .pill-cancelled  { background: #f3f4f6; color: #6b7280; }
    .pill-reversed   { background: #ede9fe; color: #7c3aed; }
    .pill-credit     { background: #dcfce7; color: #15803d; }
    .pill-debit      { background: #fee2e2; color: #dc2626; }
    .flag { background: #fee2e2; color: #dc2626; padding: 1px 5px; border-radius: 4px; font-size: 7px; font-weight: 700; }
    .footer { margin-top: 16px; font-size: 7.5px; color: #9ca3af; text-align: center;
              border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>

<h1>Transaction Report</h1>
<p class="meta">
    Generated: {{ now()->format('d M Y, H:i') }}
    &nbsp;&bull;&nbsp; Total records: {{ count($data) }}
    &nbsp;&bull;&nbsp; AS Dairy Dashboard
</p>

@php
    $totalAmt     = $data->sum('amount');
    $successAmt   = $data->where('status', 'success')->sum('amount');
    $failedCount  = $data->where('status', 'failed')->count();
    $flaggedCount = $data->where('is_flagged', true)->count();
@endphp

<div class="summary-box">
    <div class="sum-cell">
        <div class="sum-label">Total Transactions</div>
        <div class="sum-value">{{ number_format(count($data)) }}</div>
    </div>
    <div class="sum-cell">
        <div class="sum-label">Total Amount</div>
        <div class="sum-value">₹{{ number_format($totalAmt, 2) }}</div>
    </div>
    <div class="sum-cell">
        <div class="sum-label">Success Amount</div>
        <div class="sum-value green">₹{{ number_format($successAmt, 2) }}</div>
    </div>
    <div class="sum-cell">
        <div class="sum-label">Failed</div>
        <div class="sum-value red">{{ $failedCount }}</div>
    </div>
    <div class="sum-cell">
        <div class="sum-label">Flagged</div>
        <div class="sum-value red">{{ $flaggedCount }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Transaction ID</th>
            <th>User</th>
            <th>Category</th>
            <th>Type</th>
            <th>Amount (₹)</th>
            <th>Net (₹)</th>
            <th>Status</th>
            <th>Risk</th>
            <th>Flagged</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $i => $t)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td style="font-family: monospace; font-size: 7.5px; color: #4f46e5;">{{ $t->transaction_id }}</td>
            <td>{{ $t->user?->name ?? $t->sender_name ?? 'N/A' }}</td>
            <td>{{ ucfirst($t->category) }}</td>
            <td><span class="pill pill-{{ $t->type }}">{{ ucfirst($t->type) }}</span></td>
            <td>{{ number_format($t->amount, 2) }}</td>
            <td style="font-weight: 700;">{{ number_format($t->net_amount, 2) }}</td>
            <td><span class="pill pill-{{ $t->status }}">{{ ucfirst($t->status) }}</span></td>
            <td>{{ $t->risk_score }}</td>
            <td>{{ $t->is_flagged ? 'Yes' : '—' }}</td>
            <td style="white-space: nowrap;">{{ $t->created_at->format('d M Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    AS Dairy Dashboard &mdash; Confidential Report &mdash; Exported {{ now()->format('d M Y H:i') }}
</div>

</body>
</html>
