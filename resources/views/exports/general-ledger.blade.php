<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #0d9488; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
.meta { padding: 0 20px 12px; font-size: 9px; color: #6b7280; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #0d9488; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.th-right { text-align: right; }
tbody tr:nth-child(even) { background: #f0fdfa; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
.debit-col { color: #dc2626; font-weight: 600; }
.credit-col { color: #059669; font-weight: 600; }
tfoot td { padding: 8px 8px; font-weight: 700; font-size: 10px; background: #ccfbf1; border-top: 2px solid #0d9488; }
.opening-row td { background: #f0fdfa; font-style: italic; color: #6b7280; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>General Ledger &mdash; {{ $account->name }} ({{ $account->code }})</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}
       @if($dateFrom || $dateTo) &mdash; {{ $dateFrom ?? '' }} to {{ $dateTo ?? '' }} @endif
    </p>
</div>
<div class="meta">
    Account: <strong>{{ $account->name }}</strong> &nbsp;|&nbsp;
    Code: <strong>{{ $account->code }}</strong> &nbsp;|&nbsp;
    Type: <strong>{{ ucfirst($account->type) }}</strong> &nbsp;|&nbsp;
    Opening Balance: <strong>&#8377;{{ number_format(abs($data['opening_balance']), 2) }} {{ $data['opening_balance'] >= 0 ? 'Dr' : 'Cr' }}</strong>
</div>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Entry #</th>
            <th>Description</th>
            <th class="th-right">Debit (&#8377;)</th>
            <th class="th-right">Credit (&#8377;)</th>
            <th class="th-right">Balance (&#8377;)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="opening-row">
            <td>—</td>
            <td>—</td>
            <td>Opening Balance</td>
            <td></td>
            <td></td>
            <td class="text-right">{{ number_format(abs($data['opening_balance']), 2) }} {{ $data['opening_balance'] >= 0 ? 'Dr' : 'Cr' }}</td>
        </tr>
        @forelse($data['rows'] as $row)
        <tr>
            <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
            <td>{{ $row['entry_number'] }}</td>
            <td>{{ \Illuminate\Support\Str::limit($row['description'] ?? '—', 50) }}</td>
            <td class="text-right debit-col">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}</td>
            <td class="text-right credit-col">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}</td>
            <td class="text-right">{{ number_format(abs($row['balance']), 2) }} {{ $row['balance'] >= 0 ? 'Dr' : 'Cr' }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:20px;color:#9ca3af;">No transactions in selected period.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">Closing Balance</td>
            <td class="text-right debit-col">&#8377;{{ number_format(collect($data['rows'])->sum('debit'), 2) }}</td>
            <td class="text-right credit-col">&#8377;{{ number_format(collect($data['rows'])->sum('credit'), 2) }}</td>
            <td class="text-right">&#8377;{{ number_format(abs($data['closing_balance']), 2) }} {{ $data['closing_balance'] >= 0 ? 'Dr' : 'Cr' }}</td>
        </tr>
    </tfoot>
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>