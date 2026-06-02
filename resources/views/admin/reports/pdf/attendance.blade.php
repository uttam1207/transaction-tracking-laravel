<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Attendance Report</title>
<style>
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color: #111; margin: 0; padding: 16px; }
    h1 { font-size: 15px; font-weight: 700; margin: 0 0 2px; color: #1e1b4b; }
    .meta { font-size: 9px; color: #6b7280; margin-bottom: 14px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1e1b4b; color: #fff; font-size: 8px; font-weight: 700; text-transform: uppercase;
         letter-spacing: .4px; padding: 7px 8px; text-align: left; white-space: nowrap; }
    td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 8.5px; vertical-align: middle; }
    tr:nth-child(even) td { background: #f8fafc; }
    .pill { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 7.5px; font-weight: 700; }
    .pill-present  { background: #dcfce7; color: #15803d; }
    .pill-absent   { background: #fee2e2; color: #dc2626; }
    .pill-late     { background: #fef9c3; color: #ca8a04; }
    .pill-leave    { background: #dbeafe; color: #2563eb; }
    .footer { margin-top: 16px; font-size: 7.5px; color: #9ca3af; text-align: center;
              border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>

<h1>Attendance Report</h1>
<p class="meta">
    Generated: {{ now()->format('d M Y, H:i') }}
    &nbsp;&bull;&nbsp; Month: {{ now()->format('F Y') }}
    &nbsp;&bull;&nbsp; Total records: {{ count($data) }}
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Employee</th>
            <th>Department</th>
            <th>Date</th>
            <th>Status</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Work Hours</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $record)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $record->employee?->user?->name ?? 'N/A' }}</td>
            <td>{{ $record->employee?->department?->name ?? 'N/A' }}</td>
            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</td>
            <td><span class="pill pill-{{ $record->status }}">{{ ucfirst($record->status) }}</span></td>
            <td>{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '—' }}</td>
            <td>{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '—' }}</td>
            <td>{{ $record->work_hours ? number_format($record->work_hours, 1) . 'h' : '—' }}</td>
            <td>{{ $record->notes ?? '' }}</td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center; padding: 20px; color: #9ca3af;">No attendance records found</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    TxMonitor Pro &mdash; Confidential Report &mdash; Exported {{ now()->format('d M Y H:i') }}
</div>

</body>
</html>
