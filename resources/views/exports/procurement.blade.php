<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #2563eb; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
.meta { padding: 0 20px 12px; font-size: 9px; color: #6b7280; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #2563eb; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
tbody tr:nth-child(even) { background: #eff6ff; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
tfoot td { padding: 7px 8px; font-weight: 700; font-size: 10px; background: #dbeafe; border-top: 2px solid #2563eb; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Purchase Orders Report</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>
<div class="meta">
    Period:
    {{ isset($params['date_from']) && $params['date_from'] ? $params['date_from'] : 'All' }}
    &mdash;
    {{ isset($params['date_to']) && $params['date_to'] ? $params['date_to'] : 'All' }}
    &nbsp;|&nbsp;
    Status: {{ isset($params['status']) && $params['status'] ? $params['status'] : 'All' }}
    &nbsp;|&nbsp;
    Total Records: {{ $records->count() }}
</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>PO Number</th>
            <th>Order Date</th>
            <th>Vendor</th>
            <th>Description</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Amount (&#8377;)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $po)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $po->po_number }}</td>
            <td>{{ $po->order_date ? \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') : '—' }}</td>
            <td>{{ $po->vendor?->name ?? '—' }}</td>
            <td>{{ \Illuminate\Support\Str::limit($po->description ?? '—', 40) }}</td>
            <td class="text-right">{{ $po->quantity ?? '—' }}</td>
            <td class="text-right">{{ number_format((float)$po->total_amount, 2) }}</td>
            <td>{{ $po->status }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No records found.</td></tr>
        @endforelse
    </tbody>
    @if($records->count())
    <tfoot>
        <tr>
            <td colspan="6">Total ({{ $records->count() }} records)</td>
            <td class="text-right">&#8377;{{ number_format($records->sum('total_amount'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>