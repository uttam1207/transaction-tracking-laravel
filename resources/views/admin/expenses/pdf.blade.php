<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Expense Report — AS Dairy</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 20px; }
    h1 { font-size: 18px; color: #4f46e5; margin-bottom: 4px; }
    .meta { color: #64748b; font-size: 10px; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th { background: #4f46e5; color: #fff; padding: 7px 10px; text-align: left; font-size: 10px; }
    td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
    tr:nth-child(even) td { background: #f8fafc; }
    .total-row td { font-weight: bold; background: #ede9fe; border-top: 2px solid #4f46e5; }
    .badge-paid { color: #16a34a; } .badge-pending { color: #d97706; }
</style>
</head>
<body>
<h1>AS Dairy — Expense Report</h1>
<div class="meta">Generated: {{ now()->format('d M Y H:i') }}</div>

<table>
    <thead>
        <tr>
            <th>#</th><th>Date</th><th>Category</th><th>Description</th>
            <th>Amount</th><th>Method</th><th>Status</th><th>Vendor</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expenses as $e)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $e->expense_date->format('d-m-Y') }}</td>
            <td>{{ $e->category?->name }}</td>
            <td>{{ $e->description }}</td>
            <td>&#8377;{{ number_format($e->amount, 2) }}</td>
            <td>{{ $e->payment_method_label }}</td>
            <td class="badge-{{ $e->payment_status }}">{{ ucfirst($e->payment_status) }}</td>
            <td>{{ $e->vendor_payee ?? '—' }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="4" style="text-align:right;">TOTAL</td>
            <td>&#8377;{{ number_format($total, 2) }}</td>
            <td colspan="3"></td>
        </tr>
    </tbody>
</table>
</body>
</html>
