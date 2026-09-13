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
tbody tr:nth-child(even) { background: #f5f3ff; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
tfoot td { padding: 7px 8px; font-weight: 700; font-size: 10px; background: #ede9fe; border-top: 2px solid #7c3aed; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Journal Entries Report</h1>
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
            <th>Entry #</th>
            <th>Date</th>
            <th>Type</th>
            <th>Reference</th>
            <th>Description</th>
            <th class="text-right">Debit (&#8377;)</th>
            <th class="text-right">Credit (&#8377;)</th>
            <th>Status</th>
            <th>Period</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $je)
        <tr>
            <td>{{ $je->entry_number }}</td>
            <td>{{ $je->entry_date->format('d/m/Y') }}</td>
            <td>{{ ucfirst($je->type) }}</td>
            <td>{{ $je->reference ?? '—' }}</td>
            <td>{{ \Illuminate\Support\Str::limit($je->description ?? '—', 40) }}</td>
            <td class="text-right">{{ number_format((float)$je->total_debit, 2) }}</td>
            <td class="text-right">{{ number_format((float)$je->total_credit, 2) }}</td>
            <td>{{ ucfirst($je->status) }}</td>
            <td>{{ $je->period?->name ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center;padding:20px;color:#9ca3af;">No records found.</td></tr>
        @endforelse
    </tbody>
    @if($records->count())
    <tfoot>
        <tr>
            <td colspan="5">Total ({{ $records->count() }} entries)</td>
            <td class="text-right">&#8377;{{ number_format($records->sum('total_debit'), 2) }}</td>
            <td class="text-right">&#8377;{{ number_format($records->sum('total_credit'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
    @endif
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>