<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #065f46; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
.meta { padding: 0 20px 12px; font-size: 9px; color: #6b7280; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #065f46; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; }
tbody tr:nth-child(even) { background: #f0fdf4; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
tfoot td { padding: 7px 8px; font-weight: 700; font-size: 10px; background: #dcfce7; border-top: 2px solid #065f46; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
.badge-active { background: #d1fae5; color: #065f46; padding: 1px 5px; border-radius: 3px; }
.badge-sick { background: #fee2e2; color: #991b1b; padding: 1px 5px; border-radius: 3px; }
</style>
</head>
<body>
<div class="header">
    <h1>Animal & Herd Population Report</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>
<div class="meta">
    Total Animals: {{ $animals->count() }}
    &nbsp;|&nbsp; Active: {{ $animals->where('status', 'Active')->count() }}
    &nbsp;|&nbsp; Pregnant: {{ $animals->where('pregnancy_status', 'Pregnant')->count() }}
    &nbsp;|&nbsp; Sick: {{ $animals->where('health_status', 'Sick')->count() }}
</div>
<table>
    <thead>
        <tr>
            <th>Tag #</th><th>Type</th><th>Breed</th><th>Status</th>
            <th>Pregnancy</th><th>Health</th><th>Lactation #</th><th>DOB</th>
        </tr>
    </thead>
    <tbody>
        @forelse($animals as $a)
        <tr>
            <td><strong>{{ $a->tag_number ?? '—' }}</strong></td>
            <td>{{ $a->animal_type ?? '—' }}</td>
            <td>{{ $a->breed ?? '—' }}</td>
            <td>
                <span class="{{ $a->status === 'Active' ? 'badge-active' : 'badge-sick' }}">
                    {{ $a->status ?? '—' }}
                </span>
            </td>
            <td>{{ $a->pregnancy_status ?? '—' }}</td>
            <td>{{ $a->health_status ?? '—' }}</td>
            <td style="text-align:center;">{{ $a->lactation_number ?? 0 }}</td>
            <td>{{ $a->date_of_birth ? \Carbon\Carbon::parse($a->date_of_birth)->format('d/m/Y') : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No animals found.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="8">Total: {{ $animals->count() }} animals | Active: {{ $animals->where('status','Active')->count() }} | Milking Animals: {{ $animals->where('status','Active')->filter(fn($a) => ($a->lactation_number ?? 0) > 0)->count() }}</td>
        </tr>
    </tfoot>
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>