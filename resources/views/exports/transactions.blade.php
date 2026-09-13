<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #4f46e5; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
.meta { padding: 0 20px 12px; font-size: 9px; color: #6b7280; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #4f46e5; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
tbody tr:nth-child(even) { background: #eef2ff; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
.credit { color: #059669; font-weight: 700; }
.debit { color: #dc2626; font-weight: 700; }
tfoot td { padding: 7px 8px; font-weight: 700; font-size: 10px; background: #e0e7ff; border-top: 2px solid #4f46e5; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Transactions Report</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>
<div class="meta">
    Period:
    {{ isset($params['date_from']) && $params['date_from'] ? $params['date_from'] : 'All' }}
    &mdash;
    {{ isset($params['date_to']) && $params['date_to'] ? $params['date_to'] : 'All' }}
    &nbsp;|&nbsp;
    Type: {{ isset($params['type']) && $params['type'] ? $params['type'] : 'All' }}
    &nbsp;|&nbsp;
    Status: {{ isset($params['status']) && $params['status'] ? $params['status'] : 'All' }}
    &nbsp;|&nbsp;
    Total Records: {{ $records->count() }}
</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Reference</th>
            <th>Description</th>
            <th>Type</th>
            <th>Category</th>
            <th class="text-right">Amount (&#8377;)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $t)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $t->created_at->format('d/m/Y') }}</td>
            <td>{{ $t->reference ?? '—' }}</td>
            <td>{{ \Illuminate\Support\Str::limit($t->description ?? '—', 45) }}</td>
            <td class="{{ $t->type === 'credit' ? 'credit' : 'debit' }}">{{ ucfirst($t->type) }}</td>
            <td>{{ $t->category ?? '—' }}</td>
            <td class="text-right {{ $t->type === 'credit' ? 'credit' : 'debit' }}">{{ number_format((float)$t->amount, 2) }}</td>
            <td>{{ ucfirst($t->status ?? '—') }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No records found.</td></tr>
        @endforelse
    </tbody>
    @if($records->count())
    <tfoot>
        <tr>
            <td colspan="6">Total Credits</td>
            <td class="text-right credit">&#8377;{{ number_format($records->where('type','credit')->sum('amount'), 2) }}</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="6">Total Debits</td>
            <td class="text-right debit">&#8377;{{ number_format($records->where('type','debit')->sum('amount'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>