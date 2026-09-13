<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #0369a1; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
.meta { padding: 0 20px 12px; font-size: 9px; color: #6b7280; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #0369a1; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; }
tbody tr:nth-child(even) { background: #f0f9ff; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
tfoot td { padding: 7px 8px; font-weight: 700; font-size: 10px; background: #e0f2fe; border-top: 2px solid #0369a1; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Milk Production Report</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>
<div class="meta">
    Period: {{ $dateFrom }} &mdash; {{ $dateTo }}
    &nbsp;|&nbsp; Shift: {{ $shiftFilter ?: 'All' }}
    &nbsp;|&nbsp; Total Records: {{ $records->count() }}
    &nbsp;|&nbsp; Total Yield: {{ number_format($records->sum('quantity_liters'), 2) }} L
</div>
<table>
    <thead>
        <tr>
            <th>Date</th><th>Shift</th><th>Animal</th>
            <th class="text-right">Yield (L)</th><th>Fat %</th><th>SNF %</th>
            <th class="text-right">Rejected (L)</th><th>Quality</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $e)
        <tr>
            <td>{{ $e->date->format('d/m/Y') }}</td>
            <td>{{ $e->shift ?? '—' }}</td>
            <td>{{ $e->animal?->tag_number ?? '—' }}</td>
            <td class="text-right">{{ number_format((float)$e->quantity_liters, 2) }}</td>
            <td>{{ $e->fat_percentage ? number_format($e->fat_percentage,2).'%' : '—' }}</td>
            <td>{{ $e->snf_percentage ? number_format($e->snf_percentage,2).'%' : '—' }}</td>
            <td class="text-right">{{ number_format((float)($e->rejected_liters ?? 0), 2) }}</td>
            <td>{{ $e->quality_grade ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No records found.</td></tr>
        @endforelse
    </tbody>
    @if($records->count())
    <tfoot>
        <tr>
            <td colspan="3">Total ({{ $records->count() }} records)</td>
            <td class="text-right">{{ number_format($records->sum('quantity_liters'), 2) }} L</td>
            <td colspan="2">Avg Fat: {{ number_format($records->whereNotNull('fat_percentage')->avg('fat_percentage') ?? 0, 2) }}%</td>
            <td class="text-right">{{ number_format($records->sum('rejected_liters'), 2) }} L</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>