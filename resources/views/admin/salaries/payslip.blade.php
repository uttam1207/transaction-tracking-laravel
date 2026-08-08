<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip — {{ $salary->employee?->user?->name }} — {{ $salary->month_label }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size:12px; color:#1a1a2e; background:#fff; padding:30px; }
        .header { text-align:center; border-bottom:2px solid #0d9488; padding-bottom:14px; margin-bottom:20px; }
        .header h1 { font-size:20px; font-weight:800; color:#0d9488; letter-spacing:-0.5px; }
        .header h2 { font-size:13px; font-weight:600; color:#374151; margin-top:2px; }
        .header p  { font-size:11px; color:#6b7280; }
        .slip-title { text-align:center; font-size:14px; font-weight:700; color:#fff; background:#0d9488; padding:6px 0; border-radius:6px; margin-bottom:18px; }
        .meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px 24px; margin-bottom:18px; }
        .meta-item { }
        .meta-label { font-size:10px; font-weight:700; text-transform:uppercase; color:#9ca3af; letter-spacing:0.05em; }
        .meta-value { font-size:12px; font-weight:600; color:#111827; margin-top:1px; }
        table { width:100%; border-collapse:collapse; margin-bottom:14px; }
        th { background:#f1f5f9; font-size:10px; font-weight:700; text-transform:uppercase; color:#6b7280; padding:7px 10px; text-align:left; }
        td { padding:7px 10px; border-bottom:1px solid #f1f5f9; font-size:12px; }
        .amount { text-align:right; font-weight:600; }
        .total-row td { font-weight:700; background:#f8fafc; border-top:1.5px solid #0d9488; }
        .net-box { background:linear-gradient(135deg,#0d9488,#0891b2); color:#fff; padding:14px 18px; border-radius:8px; text-align:center; margin-top:10px; }
        .net-box .label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; opacity:0.8; }
        .net-box .amount { font-size:22px; font-weight:800; letter-spacing:-0.5px; text-align:center; color:#fff; }
        .footer { margin-top:30px; border-top:1px solid #e5e7eb; padding-top:12px; font-size:10px; color:#9ca3af; text-align:center; }
        .sign-row { display:flex; justify-content:space-between; margin-top:40px; }
        .sign-box { text-align:center; border-top:1px solid #374151; padding-top:6px; width:150px; font-size:10px; color:#6b7280; }
        @media print {
            body { padding:15px; }
            .no-print { display:none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom:15px;text-align:right;">
    <button onclick="window.print()" style="padding:6px 16px;background:#0d9488;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px;">
        🖨️ Print Payslip
    </button>
    <a href="{{ route('admin.salaries.show', $salary) }}" style="margin-left:8px;padding:6px 16px;border:1px solid #d1d5db;border-radius:6px;text-decoration:none;color:#374151;font-size:12px;">Back</a>
</div>

<div class="header">
    <h1>ASDairy Farm ERP</h1>
    <h2>Anoo Village, Damoh District, Madhya Pradesh</h2>
    <p>Contact: admin@asdairy.in | GST: XXXXXXXXXXXX</p>
</div>

<div class="slip-title">SALARY SLIP — {{ strtoupper($salary->month_label) }}</div>

<div class="meta-grid">
    <div class="meta-item">
        <div class="meta-label">Employee Name</div>
        <div class="meta-value">{{ $salary->employee?->user?->name ?? '—' }}</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Employee ID</div>
        <div class="meta-value">{{ $salary->employee?->employee_id ?? '—' }}</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Department</div>
        <div class="meta-value">{{ $salary->employee?->department?->name ?? '—' }}</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Designation</div>
        <div class="meta-value">{{ $salary->employee?->designation ?? '—' }}</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Pay Period</div>
        <div class="meta-value">{{ $salary->month_label }}</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Payment Mode</div>
        <div class="meta-value">{{ $salary->payment_mode ?? 'Pending' }}</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Days Worked</div>
        <div class="meta-value" style="color:#059669;">{{ $salary->days_worked }} days</div>
    </div>
    <div class="meta-item">
        <div class="meta-label">Days Absent</div>
        <div class="meta-value" style="color:#dc2626;">{{ $salary->days_absent }} days</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th colspan="2">Earnings</th>
            <th colspan="2">Deductions</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Basic Salary</td>
            <td class="amount">₹{{ number_format($salary->basic_salary, 2) }}</td>
            <td>Provident Fund (PF)</td>
            <td class="amount" style="color:#dc2626;">₹{{ number_format($salary->pf_deduction, 2) }}</td>
        </tr>
        <tr>
            <td>HRA</td>
            <td class="amount">₹{{ number_format($salary->hra, 2) }}</td>
            <td>Income Tax (TDS)</td>
            <td class="amount" style="color:#dc2626;">₹{{ number_format($salary->tax_deduction, 2) }}</td>
        </tr>
        <tr>
            <td>Other Allowances</td>
            <td class="amount">₹{{ number_format($salary->other_allowances, 2) }}</td>
            <td>Other Deductions</td>
            <td class="amount" style="color:#dc2626;">₹{{ number_format($salary->other_deductions, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td><strong>Gross Salary</strong></td>
            <td class="amount">₹{{ number_format($salary->gross_salary, 2) }}</td>
            <td><strong>Total Deductions</strong></td>
            <td class="amount" style="color:#dc2626;">₹{{ number_format($salary->pf_deduction + $salary->tax_deduction + $salary->other_deductions, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="net-box">
    <div class="label">Net Pay (Take Home)</div>
    <div class="amount">₹{{ number_format($salary->net_salary, 2) }}</div>
</div>

@if($salary->remarks)
<p style="margin-top:12px;font-size:11px;color:#6b7280;"><strong>Remarks:</strong> {{ $salary->remarks }}</p>
@endif

<div class="sign-row">
    <div class="sign-box">Employee Signature</div>
    <div class="sign-box">HR / Admin Signature</div>
    <div class="sign-box">Authorized Signatory</div>
</div>

<div class="footer">
    <p>This is a computer-generated payslip and does not require a physical signature.</p>
    <p>Generated on {{ now()->format('d M Y, h:i A') }} · ASDairy Farm ERP</p>
</div>

</body>
</html>
