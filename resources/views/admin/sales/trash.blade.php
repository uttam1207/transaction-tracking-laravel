@extends('layouts.app')
@section('title', 'Deleted Sales Invoices')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
    <li class="breadcrumb-item active">Trash</li>
@endsection

@section('content')
<div style="background:linear-gradient(135deg,#450a0a,#7f1d1d);border-radius:16px;padding:22px 28px;margin-bottom:24px;color:#fff;">
    <h5 class="mb-1 fw-bold" style="font-weight:800;">Deleted Sales Invoices <span style="background:rgba(255,255,255,.15);border-radius:6px;padding:2px 10px;font-size:.8rem;">{{ $sales->total() }}</span></h5>
    <p class="mb-0" style="opacity:.7;font-size:.82rem;">Soft-deleted invoices — ledger entries were reversed on delete. Restore to reverse that effect.</p>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.04);">
    <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#fef2f2;display:flex;align-items:center;gap:8px;justify-content:space-between;">
        <span style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#991b1b;">
            <i class="bi bi-trash3"></i> Trash
        </span>
        <a href="{{ route('admin.sales.index') }}" style="font-size:.78rem;color:#4f46e5;text-decoration:none;font-weight:600;">
            <i class="bi bi-arrow-left me-1"></i>Back to Sales
        </a>
    </div>

    @if($sales->isEmpty())
    <div style="padding:60px 20px;text-align:center;color:#9ca3af;">
        <i class="bi bi-trash" style="font-size:2.5rem;display:block;margin-bottom:12px;"></i>
        <div style="font-weight:600;">Trash is empty</div>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.83rem;">
            <thead style="background:#fef2f2;">
                <tr>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Invoice #</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Customer</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Item Type</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Amount</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Payment</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Sale Date</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Deleted At</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($sales as $sale)
            <tr style="border-bottom:1px solid #fef2f2;">
                <td style="padding:11px 16px;font-family:monospace;font-size:.78rem;color:#4f46e5;font-weight:600;">{{ $sale->invoice_number }}</td>
                <td style="padding:11px 16px;">{{ $sale->customer?->name ?? '—' }}</td>
                <td style="padding:11px 16px;">{{ $sale->item_type }}</td>
                <td style="padding:11px 16px;font-weight:600;">₹{{ number_format($sale->total_amount, 2) }}</td>
                <td style="padding:11px 16px;">
                    @php $pc = match($sale->payment_status) { 'Paid' => '#16a34a', 'Partial' => '#d97706', default => '#dc2626' }; @endphp
                    <span style="padding:2px 9px;border-radius:5px;font-size:.72rem;font-weight:600;color:{{ $pc }};background:{{ $pc }}22;">{{ $sale->payment_status }}</span>
                </td>
                <td style="padding:11px 16px;color:#374151;">{{ $sale->sale_date->format('d M Y') }}</td>
                <td style="padding:11px 16px;color:#9ca3af;font-size:.78rem;">{{ $sale->deleted_at->format('d M Y, H:i') }}</td>
                <td style="padding:11px 16px;text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                        <form method="POST" action="{{ route('admin.sales.restore', $sale->id) }}"
                              onsubmit="return confirm('Restore this invoice? Ledger effects will be re-applied.')">
                            @csrf
                            <button type="submit" style="font-size:.72rem;padding:4px 10px;border-radius:6px;border:1px solid #16a34a;background:#f0fdf4;color:#16a34a;cursor:pointer;font-weight:600;">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.sales.force-delete', $sale->id) }}"
                              onsubmit="return confirm('Permanently delete {{ $sale->invoice_number }}? Cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:.72rem;padding:4px 10px;border-radius:6px;border:1px solid #dc2626;background:#fef2f2;color:#dc2626;cursor:pointer;font-weight:600;">
                                <i class="bi bi-x-circle"></i> Delete Forever
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
    <div style="padding:14px 20px;border-top:1px solid #f3f4f6;">{{ $sales->links() }}</div>
    @endif
    @endif
</div>
@endsection
