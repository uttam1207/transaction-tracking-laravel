@extends('layouts.app')
@section('title', 'My Wallet')
@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
    <div class="page-hero-body">
        <div class="page-hero-title"><i class="bi bi-wallet2 me-2"></i>My Wallet</div>
        <div class="page-hero-sub">Your wallet balance and transaction history</div>
    </div>
</div>

{{-- Balance Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #7c3aed;padding:28px 20px;">
            <div style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Available Balance</div>
            <div style="font-size:2.6rem;font-weight:800;color:#7c3aed;line-height:1;">₹{{ number_format($wallet->balance, 2) }}</div>
            <div class="mt-2">
                @if($wallet->status === 'active')
                    <span class="spill spill-success">Active</span>
                @else
                    <span class="spill spill-danger">Frozen — Contact Admin</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #16a34a;padding:28px 20px;">
            <div style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Total Received</div>
            <div style="font-size:2rem;font-weight:800;color:#16a34a;line-height:1;">
                ₹{{ number_format($transactions->where('type','credit')->sum('amount'), 2) }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #f59e0b;padding:28px 20px;">
            <div style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Total Transactions</div>
            <div style="font-size:2rem;font-weight:800;color:#f59e0b;line-height:1;">{{ $transactions->total() }}</div>
        </div>
    </div>
</div>

{{-- Transaction History --}}
<div class="table-card">
    <div class="table-card-hdr">
        <div class="table-card-title"><i class="bi bi-clock-history me-2"></i>Transaction History</div>
    </div>
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance After</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                    <td>
                        @if($txn->type === 'credit')
                            <span class="spill spill-success"><i class="bi bi-arrow-down-circle me-1"></i>Credit</span>
                        @else
                            <span class="spill spill-danger"><i class="bi bi-arrow-up-circle me-1"></i>Debit</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:700;color:{{ $txn->type === 'credit' ? '#16a34a' : '#dc2626' }};">
                            {{ $txn->type === 'credit' ? '+' : '-' }}₹{{ number_format($txn->amount, 2) }}
                        </span>
                    </td>
                    <td style="font-weight:600;">₹{{ number_format($txn->balance_after, 2) }}</td>
                    <td style="font-size:.85rem;color:#374151;">{{ $txn->description }}</td>
                </tr>
                @empty
                <tr><td colspan="5">
                    <div class="empty-state"><i class="bi bi-wallet2"></i><p>No transactions yet</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="pagination-wrap">{{ $transactions->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

@endsection
