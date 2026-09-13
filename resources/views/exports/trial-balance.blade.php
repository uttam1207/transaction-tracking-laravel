<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #7c3aed; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
.meta { padding: 0 20px 12px; font-size: 9px; color: #6b7280; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #7c3aed; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.th-right { text-align: right; }
tbody tr:nth-child(even) { background: #f5f3ff; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
tfoot td { padding: 8px 8px; font-weight: 700; font-size: 10px; background: #ede9fe; border-top: 2px solid #7c3aed; }
.balanced { background: #d1fae5; color: #065f46; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 9px; }
.unbalanced { background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 9px; }
.status-bar { padding: 8px 20px; margin-bottom: 12px; font-size: 9px; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Trial Balance</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}
       @if($selectedPeriod) &mdash; Period: {{ $selectedPeriod->name }} @endif
    </p>
</div>
<div class="status-bar">
    Balance Status:
    <span class="{{ $balanced ? 'balanced' : 'unbalanced' }}">
        {{ $balanced ? 'BALANCED' : 'OUT OF BALANCE' }}
    </span>
    &nbsp;&mdash;&nbsp;
    Total Debit: <strong>&#8377;{{ number_format($totalDebit, 2) }}</strong>
    &nbsp;&nbsp;
    Total Credit: <strong>&#8377;{{ number_format($totalCredit, 2) }}</strong>
</div>
<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Account Name</th>
            <th>Type</th>
            <th class="th-right">Debit (&#8377;)</th>
            <th class="th-right">Credit (&#8377;)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $r)
        <tr>
            <td>{{ $r->code }}</td>
            <td>{{ $r->name }}</td>
            <td>{{ ucfirst($r->type) }}</td>
            <td class="text-right">{{ $r->total_debit > 0 ? number_format($r->total_debit, 2) : '—' }}</td>
            <td class="text-right">{{ $r->total_credit > 0 ? number_format($r->total_credit, 2) : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:20px;color:#9ca3af;">No data available.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">TOTAL</td>
            <td class="text-right">&#8377;{{ number_format($totalDebit, 2) }}</td>
            <td class="text-right">&#8377;{{ number_format($totalCredit, 2) }}</td>
        </tr>
    </tfoot>
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>