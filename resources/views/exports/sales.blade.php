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
table { width: 100%; border-collapse: collapse; margin: 0 20px; width: calc(100% - 40px); }
thead tr { background: #059669; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
tbody tr:nth-child(even) { background: #f0fdf4; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
.badge-paid { background: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 4px; }
.badge-pending { background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; }
.badge-partial { background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; }
tfoot td { padding: 7px 8px; font-weight: 700; font-size: 10px; background: #ecfdf5; border-top: 2px solid #059669; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding-top: 8px; border-top: 1px solid #e5e7eb; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Sales Invoices Report</h1>
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
    Item Type: {{ isset($params['item_type']) && $params['item_type'] ? $params['item_type'] : 'All' }}
    &nbsp;|&nbsp;
    Total Records: {{ $records->count() }}
</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Invoice</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Item Type</th>
            <th class="text-right">Qty (L)</th>
            <th class="text-right">Fat%</th>
            <th class="text-right">Amount (&#8377;)</th>
            <th>Status</th>
            <th>JE</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $i => $s)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $s->invoice_number }}</td>
            <td>{{ $s->sale_date->format('d/m/Y') }}</td>
            <td>{{ $s->customer?->name ?? 'Retail Customer' }}</td>
            <td>{{ $s->item_type }}</td>
            <td class="text-right">{{ $s->quantity ?? '—' }}</td>
            <td class="text-right">{{ $s->fat_percentage ? $s->fat_percentage.'%' : '—' }}</td>
            <td class="text-right">{{ number_format((float)$s->total_amount, 2) }}</td>
            <td>
                <span class="badge-{{ strtolower($s->payment_status) }}">{{ $s->payment_status }}</span>
            </td>
            <td>{{ $s->journal_entry_id ? 'JE-'.$s->journal_entry_id : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="10" style="text-align:center;padding:20px;color:#9ca3af;">No records found.</td></tr>
        @endforelse
    </tbody>
    @if($records->count())
    <tfoot>
        <tr>
            <td colspan="7">Total ({{ $records->count() }} records)</td>
            <td class="text-right">&#8377;{{ number_format($records->sum('total_amount'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
    @endif
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>