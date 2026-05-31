@extends('layouts.app')
@section('title', 'Company Wallet')
@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
    <div class="page-hero-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="page-hero-title"><i class="bi bi-wallet2 me-2"></i>Company Wallet</div>
                <div class="page-hero-sub">Central company wallet — all transactions flow through this</div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" onclick="openAddMoney()"
                    class="btn btn-sm"
                    style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:9px;"
                    @if($wallet->status === 'frozen') disabled @endif>
                    <i class="bi bi-plus-circle me-1"></i>Add Money
                </button>
                <form action="{{ route('admin.wallets.toggleFreeze', $wallet->id) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm"
                        style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:9px;">
                        <i class="bi bi-{{ $wallet->status === 'active' ? 'lock' : 'unlock' }} me-1"></i>
                        {{ $wallet->status === 'active' ? 'Freeze' : 'Unfreeze' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;font-size:.85rem;" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Balance + Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #7c3aed;padding:28px 20px;">
            <div style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Current Balance</div>
            <div style="font-size:2.8rem;font-weight:800;color:#7c3aed;line-height:1;">₹{{ number_format($wallet->balance, 2) }}</div>
            <div class="mt-2">
                @if($wallet->status === 'active')
                    <span class="spill spill-success">Active</span>
                @else
                    <span class="spill spill-danger">Frozen</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #16a34a;padding:28px 20px;">
            <div style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Total Credited</div>
            <div style="font-size:2rem;font-weight:800;color:#16a34a;line-height:1;">₹{{ number_format($stats['total_credited'], 2) }}</div>
            <div style="font-size:.78rem;color:#9ca3af;margin-top:6px;">All credits incl. top-ups</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #dc2626;padding:28px 20px;">
            <div style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Total Debited</div>
            <div style="font-size:2rem;font-weight:800;color:#dc2626;line-height:1;">₹{{ number_format($stats['total_debited'], 2) }}</div>
            <div style="font-size:.78rem;color:#9ca3af;margin-top:6px;">{{ number_format($stats['txn_count']) }} total entries</div>
        </div>
    </div>
</div>

{{-- Transaction History --}}
<div class="table-card">
    <div class="table-card-hdr">
        <div class="table-card-title"><i class="bi bi-clock-history me-2"></i>Wallet Transaction History</div>
    </div>
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance After</th>
                    <th>Description</th>
                    <th>Reference</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td style="font-size:.82rem;color:#6b7280;white-space:nowrap;">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
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
                    <td style="font-size:.85rem;color:#374151;max-width:200px;">{{ $txn->description }}</td>
                    <td style="font-size:.78rem;color:#9ca3af;font-family:monospace;">{{ $txn->reference ?? '—' }}</td>
                    <td>
                        <div style="font-size:.82rem;font-weight:600;">{{ $txn->performer?->name ?? 'System' }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;">{{ ucfirst(str_replace('_',' ',$txn->performer?->role ?? '')) }}</div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
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

{{-- Add Money Modal --}}
<div class="modal fade" id="addMoneyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;border-radius:12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add Money to Company Wallet</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMoneyForm" action="{{ route('admin.wallets.addMoney', $wallet->id) }}" method="POST">
                @csrf
                <div class="modal-body" style="padding:24px;">
                    @if($errors->any())
                    <div class="alert alert-danger" style="border-radius:9px;font-size:.82rem;padding:10px 14px;">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        {{ $errors->first() }}
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="flabel">Current Balance</label>
                        <input type="text" class="form-control" readonly
                               value="₹{{ number_format($wallet->balance, 2) }}"
                               style="border-radius:9px;border:1.5px solid #e5e7eb;background:#f9fafb;font-weight:700;color:#7c3aed;">
                    </div>
                    <div class="mb-3">
                        <label class="flabel">Amount to Add (₹) <span class="req">*</span></label>
                        <input type="number" name="amount" class="form-control {{ $errors->has('amount') ? 'is-invalid' : '' }}"
                               min="1" step="0.01" placeholder="0.00" required
                               value="{{ old('amount') }}"
                               style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                    <div class="mb-1">
                        <label class="flabel">Description <span class="req">*</span></label>
                        <input type="text" name="description" class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                               placeholder="e.g. Monthly fund allocation, Capital injection…" required
                               value="{{ old('description') }}"
                               style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">
                        <i class="bi bi-plus-circle me-1"></i>Add Money
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openAddMoney() {
    new bootstrap.Modal(document.getElementById('addMoneyModal')).show();
}

// Auto-reopen modal if there were validation errors
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('addMoneyModal')).show();
});
@endif
</script>
@endpush
