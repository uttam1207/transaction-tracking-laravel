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
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; }
tbody tr:nth-child(even) { background: #f5f3ff; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
tfoot td { padding: 7px 8px; font-weight: 700; font-size: 10px; background: #ede9fe; border-top: 2px solid #7c3aed; }
.yes { color: #059669; font-weight: 700; }
.no { color: #dc2626; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Breeding & Fertility Report</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>
@php
    $totalAI = $records->count();
    $pregnant = $records->where('is_pregnant', true)->count();
    $rate = $totalAI > 0 ? round($pregnant / $totalAI * 100, 1) : 0;
@endphp
<div class="meta">
    Period: {{ $dateFrom }} &mdash; {{ $dateTo }}
    &nbsp;|&nbsp; Total AI: {{ $totalAI }}
    &nbsp;|&nbsp; Confirmed Pregnant: {{ $pregnant }}
    &nbsp;|&nbsp; Success Rate: {{ $rate }}%
</div>
<table>
    <thead>
        <tr>
            <th>Animal Tag</th><th>AI Date</th><th>Bull/Semen</th>
            <th>Status</th><th>Pregnant</th>
            <th>Expected Calving</th><th>Actual Calving</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $r)
        <tr>
            <td><strong>{{ $r->animal?->tag_number ?? '—' }}</strong></td>
            <td>{{ $r->ai_date ? \Carbon\Carbon::parse($r->ai_date)->format('d/m/Y') : '—' }}</td>
            <td>{{ $r->bull_semen ?? '—' }}</td>
            <td>{{ $r->status ?? '—' }}</td>
            <td class="{{ $r->is_pregnant ? 'yes' : 'no' }}">{{ $r->is_pregnant ? 'Yes' : 'No' }}</td>
            <td>{{ $r->expected_calving_date ? \Carbon\Carbon::parse($r->expected_calving_date)->format('d/m/Y') : '—' }}</td>
            <td>{{ $r->actual_calving_date ? \Carbon\Carbon::parse($r->actual_calving_date)->format('d/m/Y') : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:20px;color:#9ca3af;">No records found.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">Total: {{ $totalAI }} AI records</td>
            <td>{{ $pregnant }} pregnant ({{ $rate }}%)</td>
            <td colspan="2">Calved: {{ $records->whereNotNull('actual_calving_date')->count() }}</td>
        </tr>
    </tfoot>
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>