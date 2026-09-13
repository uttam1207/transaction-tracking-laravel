<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #1d4ed8; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #1d4ed8; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.th-right { text-align: right; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
.section-asset td { background: #dbeafe; font-weight: 700; font-size: 9px; color: #1e40af; padding: 5px 8px; text-transform: uppercase; }
.section-liability td { background: #fee2e2; font-weight: 700; font-size: 9px; color: #991b1b; padding: 5px 8px; text-transform: uppercase; }
.section-equity td { background: #fef9c3; font-weight: 700; font-size: 9px; color: #854d0e; padding: 5px 8px; text-transform: uppercase; }
.subtotal td { font-weight: 700; background: #f8fafc; border-top: 1px solid #bfdbfe; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Balance Sheet</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>

@php
    $totalAssets      = collect($data['assets'])->sum('balance');
    $totalLiabilities = collect($data['liabilities'])->sum('balance');
    $totalEquity      = collect($data['equity'])->sum('balance');
@endphp

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Account</th>
            <th class="th-right">Balance (&#8377;)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="section-asset"><td colspan="3">Assets</td></tr>
        @foreach($data['assets'] as $a)
        <tr>
            <td>{{ $a->code }}</td>
            <td>{{ $a->name }}</td>
            <td class="text-right" style="color:#1d4ed8;font-weight:600;">{{ number_format($a->balance, 2) }}</td>
        </tr>
        @endforeach
        <tr class="subtotal">
            <td colspan="2">Total Assets</td>
            <td class="text-right" style="color:#1d4ed8;">&#8377;{{ number_format($totalAssets, 2) }}</td>
        </tr>

        <tr class="section-liability"><td colspan="3">Liabilities</td></tr>
        @foreach($data['liabilities'] as $l)
        <tr>
            <td>{{ $l->code }}</td>
            <td>{{ $l->name }}</td>
            <td class="text-right" style="color:#dc2626;font-weight:600;">{{ number_format($l->balance, 2) }}</td>
        </tr>
        @endforeach
        <tr class="subtotal">
            <td colspan="2">Total Liabilities</td>
            <td class="text-right" style="color:#dc2626;">&#8377;{{ number_format($totalLiabilities, 2) }}</td>
        </tr>

        <tr class="section-equity"><td colspan="3">Equity</td></tr>
        @foreach($data['equity'] as $e)
        <tr>
            <td>{{ $e->code }}</td>
            <td>{{ $e->name }}</td>
            <td class="text-right" style="color:#ca8a04;font-weight:600;">{{ number_format($e->balance, 2) }}</td>
        </tr>
        @endforeach
        <tr class="subtotal">
            <td colspan="2">Total Equity</td>
            <td class="text-right" style="color:#ca8a04;">&#8377;{{ number_format($totalEquity, 2) }}</td>
        </tr>
    </tbody>
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>