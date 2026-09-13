<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #b91c1c; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
.meta { padding: 0 20px 12px; font-size: 9px; color: #6b7280; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #b91c1c; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; }
tbody tr:nth-child(even) { background: #fff5f5; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
tfoot td { padding: 7px 8px; font-weight: 700; font-size: 10px; background: #fee2e2; border-top: 2px solid #b91c1c; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Health & Treatment Report</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>
<div class="meta">
    Period: {{ $dateFrom }} &mdash; {{ $dateTo }}
    &nbsp;|&nbsp; Type: {{ $typeFilter ?: 'All' }}
    &nbsp;|&nbsp; Total Records: {{ $records->count() }}
    &nbsp;|&nbsp; Total Cost: &#8377;{{ number_format($records->sum('cost'), 2) }}
</div>
<table>
    <thead>
        <tr>
            <th>Animal</th><th>Date</th><th>Type</th>
            <th>Diagnosis / Notes</th><th>Treatment</th>
            <th>Veterinarian</th><th class="text-right">Cost (&#8377;)</th><th>Next Due</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $r)
        <tr>
            <td><strong>{{ $r->animal?->tag_number ?? '—' }}</strong></td>
            <td>{{ $r->date ? \Carbon\Carbon::parse($r->date)->format('d/m/Y') : '—' }}</td>
            <td>{{ $r->record_type ?? '—' }}</td>
            <td>{{ \Illuminate\Support\Str::limit($r->diagnosis ?? $r->notes ?? '—', 35) }}</td>
            <td>{{ \Illuminate\Support\Str::limit($r->treatment ?? '—', 30) }}</td>
            <td>{{ $r->veterinarian ?? '—' }}</td>
            <td class="text-right">{{ number_format((float)($r->cost ?? 0), 2) }}</td>
            <td>{{ $r->next_due_date ? \Carbon\Carbon::parse($r->next_due_date)->format('d/m/Y') : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No records found.</td></tr>
        @endforelse
    </tbody>
    @if($records->count())
    <tfoot>
        <tr>
            <td colspan="6">Total ({{ $records->count() }} records)</td>
            <td class="text-right">&#8377;{{ number_format($records->sum('cost'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>