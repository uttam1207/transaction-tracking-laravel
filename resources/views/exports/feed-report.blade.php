<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
.header { background: #78350f; color: #fff; padding: 14px 20px; margin-bottom: 16px; }
.header h1 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
.header p { font-size: 10px; opacity: .85; }
.meta { padding: 0 20px 12px; font-size: 9px; color: #6b7280; }
table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
thead tr { background: #78350f; color: #fff; }
thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; }
tbody tr:nth-child(even) { background: #fef9c3; }
tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9px; }
.text-right { text-align: right; }
tfoot td { padding: 7px 8px; font-weight: 700; font-size: 10px; background: #fef08a; border-top: 2px solid #78350f; }
.ok { color: #059669; font-weight: 700; }
.low { color: #dc2626; font-weight: 700; }
.footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 16px; padding: 8px 20px 0; }
</style>
</head>
<body>
<div class="header">
    <h1>Feed Requirement & Stock Report</h1>
    <p>ASDairy Farm Management &mdash; Generated {{ now()->format('d M Y, h:i A') }}</p>
</div>
<div class="meta">
    Total Animals: {{ $data['total_animals'] }}
    &nbsp;|&nbsp; Total Daily Feed Required: {{ number_format($totalDailyFeed, 2) }} kg/day
    &nbsp;|&nbsp; Weekly: {{ number_format($totalDailyFeed * 7, 2) }} kg
    &nbsp;|&nbsp; Monthly: {{ number_format($totalDailyFeed * 30, 2) }} kg
</div>
<table>
    <thead>
        <tr>
            <th>Feed Type</th>
            <th class="text-right">Daily Need (kg)</th>
            <th class="text-right">Weekly (kg)</th>
            <th class="text-right">Monthly (kg)</th>
            <th class="text-right">Stock (kg)</th>
            <th class="text-right">Days Remaining</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($stockComparison as $s)
        <tr>
            <td><strong>{{ $s['feed_type'] ?? '—' }}</strong></td>
            <td class="text-right">{{ number_format($s['daily_need'] ?? 0, 2) }}</td>
            <td class="text-right">{{ number_format(($s['daily_need'] ?? 0) * 7, 2) }}</td>
            <td class="text-right">{{ number_format(($s['daily_need'] ?? 0) * 30, 2) }}</td>
            <td class="text-right">{{ number_format($s['current_stock'] ?? 0, 2) }}</td>
            <td class="text-right">{{ $s['days_remaining'] ?? '—' }}</td>
            <td class="{{ ($s['status'] ?? '') === 'OK' ? 'ok' : 'low' }}">{{ $s['status'] ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:20px;color:#9ca3af;">No feed data available.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="text-right">{{ number_format($totalDailyFeed, 2) }} kg</td>
            <td class="text-right">{{ number_format($totalDailyFeed * 7, 2) }} kg</td>
            <td class="text-right">{{ number_format($totalDailyFeed * 30, 2) }} kg</td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
</table>
<div class="footer">ASDairy ERP &bull; Confidential &bull; {{ now()->format('d/m/Y') }}</div>
</body>
</html>