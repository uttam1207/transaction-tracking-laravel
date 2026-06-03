<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Transactions Export</title>
<style>
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color: #111; margin: 0; padding: 16px; }
    h1 { font-size: 15px; font-weight: 700; margin: 0 0 2px; color: #1e1b4b; }
    .meta { font-size: 9px; color: #6b7280; margin-bottom: 14px; }
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
    .flag            { background: #fee2e2; color: #dc2626; padding: 1px 5px; border-radius: 4px; font-size: 7px; font-weight: 700; }
    .footer { margin-top: 16px; font-size: 7.5px; color: #9ca3af; text-align: center;
              border-top: 1px solid #e5e7eb; padding-top: 8px; }
    .summary-row td { font-weight: 700; background: #f0f4ff !important; font-size: 9px; }
</style>
</head>
<body>

<h1>Transactions Export Report</h1>
<p class="meta">
    Generated: {{ now()->format('d M Y, H:i') }}
    &nbsp;&bull;&nbsp; Total records: {{ count($transactions) }}
    &nbsp;&bull;&nbsp; Exported by: AS Dairy Dashboard
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Transaction ID</th>
            <th>User / Account</th>
            <th>Category</th>
            <th>Type</th>
            <th>Amount (₹)</th>
            <th>Fee (₹)</th>
            <th>Net (₹)</th>
            <th>Currency</th>
            <th>Status</th>
            <th>Risk</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalAmount = 0;
            $totalFee = 0;
            $totalNet = 0;
        @endphp
        @foreach($transactions as $i => $t)
        @php
            $totalAmount += $t->amount;
            $totalFee += $t->fee;
            $totalNet += $t->net_amount;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td style="font-family: monospace; font-size: 7.5px; color: #4f46e5;">
                {{ $t->transaction_id }}
                @if($t->is_flagged) <span class="flag">⚑</span> @endif
            </td>
            <td>{{ $t->user?->name ?? $t->sender_name ?? 'N/A' }}</td>
            <td>{{ ucfirst($t->category) }}</td>
            <td><span class="pill pill-{{ $t->type }}">{{ ucfirst($t->type) }}</span></td>
            <td>{{ number_format($t->amount, 2) }}</td>
            <td>{{ number_format($t->fee, 2) }}</td>
            <td style="font-weight: 700;">{{ number_format($t->net_amount, 2) }}</td>
            <td>{{ $t->currency }}</td>
            <td><span class="pill pill-{{ $t->status }}">{{ ucfirst($t->status) }}</span></td>
            <td>{{ $t->risk_score }}</td>
            <td style="white-space: nowrap;">{{ $t->created_at->format('d M Y') }}</td>
        </tr>
        @endforeach
        <tr class="summary-row">
            <td colspan="5">TOTALS</td>
            <td>{{ number_format($totalAmount, 2) }}</td>
            <td>{{ number_format($totalFee, 2) }}</td>
            <td>{{ number_format($totalNet, 2) }}</td>
            <td colspan="4"></td>
        </tr>
    </tbody>
</table>

<div class="footer">
    AS Dairy Dashboard &mdash; Confidential Document &mdash; Do not distribute &mdash; Exported {{ now()->format('d M Y H:i') }}
</div>

</body>
</html>
