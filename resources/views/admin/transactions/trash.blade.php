@extends('layouts.app')
@section('title', 'Deleted Transactions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transactions</a></li>
    <li class="breadcrumb-item active">Trash</li>
@endsection

@section('content')
<div style="background:linear-gradient(135deg,#450a0a,#7f1d1d);border-radius:16px;padding:22px 28px;margin-bottom:24px;color:#fff;position:relative;overflow:hidden;">
    <div style="position:relative;z-index:1;">
        <h5 class="mb-1 fw-bold" style="font-weight:800;">Deleted Transactions <span style="background:rgba(255,255,255,.15);border-radius:6px;padding:2px 10px;font-size:.8rem;">{{ $transactions->total() }}</span></h5>
        <p class="mb-0" style="opacity:.7;font-size:.82rem;">Soft-deleted records — wallet &amp; ledger were reversed on delete. Restore to reverse that effect.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.04);">
    <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#fef2f2;display:flex;align-items:center;gap:8px;justify-content:space-between;">
        <span style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#991b1b;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-trash3" style="color:#dc2626;"></i> Trash
        </span>
        <a href="{{ route('admin.transactions.index') }}" style="font-size:.78rem;color:#4f46e5;text-decoration:none;font-weight:600;">
            <i class="bi bi-arrow-left me-1"></i>Back to Transactions
        </a>
    </div>

    @if($transactions->isEmpty())
    <div style="padding:60px 20px;text-align:center;color:#9ca3af;">
        <i class="bi bi-trash" style="font-size:2.5rem;display:block;margin-bottom:12px;"></i>
        <div style="font-weight:600;">Trash is empty</div>
        <div style="font-size:.82rem;margin-top:4px;">No deleted transactions found.</div>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.83rem;">
            <thead style="background:#fef2f2;">
                <tr>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Txn ID</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Type</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Amount</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Status</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Sender</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;">Deleted At</th>
                    <th style="padding:11px 16px;font-weight:700;font-size:.72rem;text-transform:uppercase;color:#991b1b;border:none;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($transactions as $tx)
            <tr style="border-bottom:1px solid #fef2f2;">
                <td style="padding:11px 16px;">
                    <span style="font-family:monospace;font-size:.78rem;color:#4f46e5;font-weight:600;">{{ $tx->transaction_id }}</span>
                </td>
                <td style="padding:11px 16px;">
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:5px;font-size:.72rem;font-weight:600;
                        {{ $tx->type === 'credit' ? 'background:#dcfce7;color:#166534;' : 'background:#fee2e2;color:#991b1b;' }}">
                        <i class="bi {{ $tx->type === 'credit' ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle' }}"></i>
                        {{ ucfirst($tx->type) }}
                    </span>
                </td>
                <td style="padding:11px 16px;font-weight:600;">₹{{ number_format($tx->net_amount, 2) }}</td>
                <td style="padding:11px 16px;">
                    <span style="padding:2px 9px;border-radius:5px;font-size:.72rem;font-weight:600;background:#f3f4f6;color:#6b7280;">
                        {{ ucfirst($tx->status) }}
                    </span>
                </td>
                <td style="padding:11px 16px;color:#374151;">{{ $tx->sender_name ?? '—' }}</td>
                <td style="padding:11px 16px;color:#9ca3af;font-size:.78rem;">
                    {{ $tx->deleted_at->format('d M Y, H:i') }}
                </td>
                <td style="padding:11px 16px;text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                        <form method="POST" action="{{ route('admin.transactions.restore', $tx->id) }}"
                              onsubmit="return confirm('Restore this transaction? Wallet and ledger effects will be re-applied.')">
                            @csrf
                            <button type="submit"
                                style="font-size:.72rem;padding:4px 10px;border-radius:6px;border:1px solid #16a34a;background:#f0fdf4;color:#16a34a;cursor:pointer;font-weight:600;">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.transactions.force-delete', $tx->id) }}"
                              onsubmit="return confirm('Permanently delete {{ $tx->transaction_id }}? This CANNOT be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                style="font-size:.72rem;padding:4px 10px;border-radius:6px;border:1px solid #dc2626;background:#fef2f2;color:#dc2626;cursor:pointer;font-weight:600;">
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
    @if($transactions->hasPages())
    <div style="padding:14px 20px;border-top:1px solid #f3f4f6;">
        {{ $transactions->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
