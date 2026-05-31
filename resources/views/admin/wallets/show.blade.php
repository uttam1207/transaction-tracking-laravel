@extends('layouts.app')
@section('title', 'Wallet — ' . $user->name)
@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
    <div class="page-hero-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ $user->avatarUrl }}" class="rounded-circle" width="52" height="52" style="object-fit:cover;border:3px solid rgba(255,255,255,.4);">
                <div>
                    <div class="page-hero-title">{{ $user->name }}'s Wallet</div>
                    <div class="page-hero-sub">{{ $user->email }} &nbsp;·&nbsp; {{ ucfirst(str_replace('_',' ',$user->role)) }}</div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:9px;"
                    onclick="openAddMoney({{ $wallet->id }}, '{{ addslashes($user->name) }}', '{{ $wallet->balance }}')"
                    @if($wallet->status === 'frozen') disabled @endif>
                    <i class="bi bi-plus-circle me-1"></i>Add Money
                </button>
                <a href="{{ route('admin.wallets.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:9px;">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Balance Card --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #7c3aed;padding:24px;">
            <div style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Current Balance</div>
            <div style="font-size:2.4rem;font-weight:800;color:#7c3aed;line-height:1;">₹{{ number_format($wallet->balance, 2) }}</div>
            <div class="mt-2">
                @if($wallet->status === 'active')
                    <span class="spill spill-success">Active</span>
                @else
                    <span class="spill spill-danger">Frozen</span>
                @endif
            </div>
            <form action="{{ route('admin.wallets.toggleFreeze', $wallet->id) }}" method="POST" class="mt-3">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm {{ $wallet->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" style="border-radius:8px;font-size:.78rem;">
                    <i class="bi bi-{{ $wallet->status === 'active' ? 'lock' : 'unlock' }} me-1"></i>
                    {{ $wallet->status === 'active' ? 'Freeze Wallet' : 'Unfreeze Wallet' }}
                </button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #16a34a;padding:24px;">
            <div style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Total Credited</div>
            <div style="font-size:2rem;font-weight:800;color:#16a34a;line-height:1;">
                ₹{{ number_format($transactions->where('type','credit')->sum('amount'), 2) }}
            </div>
            <div style="font-size:.8rem;color:#6b7280;margin-top:6px;">{{ $transactions->where('type','credit')->count() }} credit(s)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="border-top:4px solid #dc2626;padding:24px;">
            <div style="font-size:.82rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Total Debited</div>
            <div style="font-size:2rem;font-weight:800;color:#dc2626;line-height:1;">
                ₹{{ number_format($transactions->where('type','debit')->sum('amount'), 2) }}
            </div>
            <div style="font-size:.8rem;color:#6b7280;margin-top:6px;">{{ $transactions->where('type','debit')->count() }} debit(s)</div>
        </div>
    </div>
</div>

{{-- Transactions --}}
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
                    <th>Added By</th>
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
                    <td>
                        <div style="font-size:.82rem;">{{ $txn->performer?->name ?? '—' }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;">{{ ucfirst(str_replace('_',' ',$txn->performer?->role ?? '')) }}</div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state"><i class="bi bi-clock-history"></i><p>No transactions yet</p></div>
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
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add Money to Wallet</h6>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMoneyForm" method="POST">
                @csrf
                <div class="modal-body" style="padding:24px;">
                    <div class="mb-3">
                        <label class="flabel">User</label>
                        <input type="text" id="addMoneyUser" class="form-control" readonly style="border-radius:9px;border:1.5px solid #e5e7eb;background:#f9fafb;">
                    </div>
                    <div class="mb-3">
                        <label class="flabel">Current Balance</label>
                        <input type="text" id="addMoneyCurrent" class="form-control" readonly style="border-radius:9px;border:1.5px solid #e5e7eb;background:#f9fafb;">
                    </div>
                    <div class="mb-3">
                        <label class="flabel">Amount to Add (₹) <span class="req">*</span></label>
                        <input type="number" name="amount" class="form-control" min="1" step="0.01" placeholder="0.00" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                    <div class="mb-1">
                        <label class="flabel">Description <span class="req">*</span></label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Salary credit, Bonus..." required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4"><i class="bi bi-plus-circle me-1"></i>Add Money</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openAddMoney(walletId, userName, currentBalance) {
    document.getElementById('addMoneyUser').value = userName;
    document.getElementById('addMoneyCurrent').value = '₹' + parseFloat(currentBalance).toLocaleString('en-IN', {minimumFractionDigits:2});
    document.getElementById('addMoneyForm').action = `/admin/wallets/${walletId}/add-money`;
    new bootstrap.Modal(document.getElementById('addMoneyModal')).show();
}
</script>
@endpush
