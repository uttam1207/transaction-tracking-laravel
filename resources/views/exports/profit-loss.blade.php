<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #059669; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
.meta { padding: 0 20px 12px; font-size: 9px; color: #6b7280; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #059669; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.th-right { text-align: right; }
tbody tr:nth-child(even) { background: #f0fdf4; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
.section-header td { background: #d1fae5; font-weight: 700; font-size: 9px; color: #065f46; padding: 5px 8px; text-transform: uppercase; letter-spacing: .04em; }
.section-header-expense td { background: #fee2e2; font-weight: 700; font-size: 9px; color: #991b1b; padding: 5px 8px; text-transform: uppercase; letter-spacing: .04em; }
.totals td { background: #ecfdf5; font-weight: 700; border-top: 1px solid #059669; }
.net-profit td { background: #059669; color: #fff; font-weight: 700; font-size: 11px; border-top: 2px solid #065f46; }
.net-loss td { background: #dc2626; color: #fff; font-weight: 700; font-size: 11px; border-top: 2px solid #991b1b; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Profit & Loss Statement</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}
       @if($selectedPeriod) &mdash; Period: {{ $selectedPeriod->name }} @endif
       @if($dateFrom || $dateTo) &mdash; {{ $dateFrom ?? '' }} to {{ $dateTo ?? '' }} @endif
    </p>
</div>

@php
    $totalRevenue = collect($data['revenue'])->sum('balance');
    $totalExpense = collect($data['expenses'])->sum('balance');
    $netProfit = $totalRevenue - $totalExpense;
@endphp

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Account</th>
            <th class="th-right">Revenue (&#8377;)</th>
            <th class="th-right">Expense (&#8377;)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="section-header"><td colspan="4">Revenue</td></tr>
        @foreach($data['revenue'] as $r)
        <tr>
            <td>{{ $r->code }}</td>
            <td>{{ $r->name }}</td>
            <td class="text-right" style="color:#059669;font-weight:600;">{{ number_format($r->balance, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        <tr class="totals">
            <td colspan="2">Total Revenue</td>
            <td class="text-right" style="color:#059669;">&#8377;{{ number_format($totalRevenue, 2) }}</td>
            <td></td>
        </tr>

        <tr class="section-header-expense"><td colspan="4">Expenses</td></tr>
        @foreach($data['expenses'] as $e)
        <tr>
            <td>{{ $e->code }}</td>
            <td>{{ $e->name }}</td>
            <td></td>
            <td class="text-right" style="color:#dc2626;font-weight:600;">{{ number_format($e->balance, 2) }}</td>
        </tr>
        @endforeach
        <tr class="totals">
            <td colspan="2">Total Expenses</td>
            <td></td>
            <td class="text-right" style="color:#dc2626;">&#8377;{{ number_format($totalExpense, 2) }}</td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="{{ $netProfit >= 0 ? 'net-profit' : 'net-loss' }}">
            <td colspan="2">NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}</td>
            <td class="text-right">&#8377;{{ number_format(abs($netProfit), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>