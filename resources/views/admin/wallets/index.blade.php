@extends('layouts.app')
@section('title', 'Wallet Management')
@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
    <div class="page-hero-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="page-hero-title"><i class="bi bi-wallet2 me-2"></i>Wallet Management</div>
                <div class="page-hero-sub">Super Admin — manage user wallets and fund allocation</div>
            </div>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="info-card text-center" style="border-top:4px solid #7c3aed;padding:18px;">
            <div style="font-size:1.7rem;font-weight:800;color:#7c3aed;">{{ number_format($stats['total']) }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Total Wallets</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-card text-center" style="border-top:4px solid #16a34a;padding:18px;">
            <div style="font-size:1.7rem;font-weight:800;color:#16a34a;">₹{{ number_format($stats['total_balance'], 2) }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Total Balance</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-card text-center" style="border-top:4px solid #16a34a;padding:18px;">
            <div style="font-size:1.7rem;font-weight:800;color:#16a34a;">{{ number_format($stats['active']) }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Active</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="info-card text-center" style="border-top:4px solid #dc2626;padding:18px;">
            <div style="font-size:1.7rem;font-weight:800;color:#dc2626;">{{ number_format($stats['frozen']) }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Frozen</div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="table-card-hdr">
        <div class="table-card-title"><i class="bi bi-wallet2 me-2"></i>All User Wallets</div>
    </div>
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($wallets as $wallet)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $wallet->user->avatarUrl }}" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                            <div>
                                <div style="font-weight:600;font-size:.88rem;">{{ $wallet->user->name }}</div>
                                <div style="font-size:.74rem;color:#9ca3af;">{{ $wallet->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="spill spill-info" style="font-size:.72rem;">{{ ucfirst(str_replace('_',' ',$wallet->user->role)) }}</span></td>
                    <td>
                        <span style="font-weight:700;font-size:.95rem;color:{{ $wallet->balance > 0 ? '#16a34a' : '#6b7280' }};">
                            ₹{{ number_format($wallet->balance, 2) }}
                        </span>
                    </td>
                    <td>
                        @if($wallet->status === 'active')
                            <span class="spill spill-success">Active</span>
                        @else
                            <span class="spill spill-danger">Frozen</span>
                        @endif
                    </td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $wallet->updated_at->format('d M Y, h:i A') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.wallets.show', $wallet->user_id) }}" class="act-btn act-view" title="View History"><i class="bi bi-eye"></i></a>
                            <button class="act-btn act-green" title="Add Money"
                                onclick="openAddMoney({{ $wallet->id }}, '{{ addslashes($wallet->user->name) }}', '{{ $wallet->balance }}')"
                                @if($wallet->status === 'frozen') disabled @endif>
                                <i class="bi bi-plus-circle"></i>
                            </button>
                            <form action="{{ route('admin.wallets.toggleFreeze', $wallet->id) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="act-btn {{ $wallet->status === 'active' ? 'act-delete' : 'act-edit' }}"
                                    title="{{ $wallet->status === 'active' ? 'Freeze Wallet' : 'Unfreeze Wallet' }}">
                                    <i class="bi bi-{{ $wallet->status === 'active' ? 'lock' : 'unlock' }}"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state"><i class="bi bi-wallet2"></i><p>No wallets found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($wallets->hasPages())
    <div class="pagination-wrap">{{ $wallets->links('pagination::bootstrap-5') }}</div>
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
