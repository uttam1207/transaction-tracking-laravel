@extends('layouts.app')
@section('title', 'Blacklist Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fraud-alerts.index') }}">Fraud Alerts</a></li>
    <li class="breadcrumb-item active">Blacklist</li>
@endsection

@section('content')

<a href="{{ route('admin.fraud-alerts.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Alerts</a>

<div class="page-hero" style="background:linear-gradient(135deg,#7f1d1d,#991b1b,#7f1d1d);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Blacklist Management</h4>
            <p>Block suspicious IPs, emails, accounts and more</p>
        </div>
        <button class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);"
            data-bs-toggle="modal" data-bs-target="#addBlacklistModal">
            <i class="bi bi-ban me-1"></i>Add to Blacklist
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach(['ip'=>'IP Addresses','email'=>'Emails','account'=>'Accounts','country'=>'Countries'] as $type => $label)
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:16px;border-top:4px solid #dc2626;">
            <div style="font-size:1.8rem;font-weight:800;color:#dc2626;line-height:1;">{{ $blacklists->where('type',$type)->count() }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="table-card">
    <div class="card-header" style="padding:0;">
        <ul class="nav" style="border-bottom:none;padding:0 16px;" id="blacklistTabs">
            @foreach(['ip'=>'IP Addresses','email'=>'Emails','account'=>'Accounts','country'=>'Countries','device'=>'Devices'] as $type => $label)
            <li class="nav-item">
                <a class="nav-link @if($loop->first) active @endif"
                    data-bs-toggle="tab" href="#tab-{{ $type }}"
                    style="font-size:.84rem;font-weight:600;padding:12px 16px;color:#6b7280;border:none;border-radius:0;border-bottom:2px solid transparent;">
                    {{ $label }}
                    <span style="background:{{ $blacklists->where('type',$type)->count() > 0 ? '#fef2f2' : '#f3f4f6' }};color:{{ $blacklists->where('type',$type)->count() > 0 ? '#dc2626' : '#9ca3af' }};padding:1px 6px;border-radius:10px;font-size:.7rem;font-weight:700;margin-left:5px;">
                        {{ $blacklists->where('type', $type)->count() }}
                    </span>
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="tab-content">
        @foreach(['ip'=>'IP Addresses','email'=>'Emails','account'=>'Accounts','country'=>'Countries','device'=>'Devices'] as $type => $label)
        <div class="tab-pane fade @if($loop->first) show active @endif" id="tab-{{ $type }}">
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Value</th>
                            <th>Reason</th>
                            <th>Added By</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blacklists->where('type', $type) as $item)
                        <tr>
                            <td style="font-family:monospace;font-size:.84rem;font-weight:700;color:#dc2626;">{{ $item->value }}</td>
                            <td style="font-size:.82rem;color:#6b7280;">{{ $item->reason ?? '—' }}</td>
                            <td style="font-size:.82rem;color:#374151;">{{ $item->addedBy->name ?? '—' }}</td>
                            <td style="font-size:.82rem;color:#9ca3af;">{{ $item->expires_at ? \Carbon\Carbon::parse($item->expires_at)->format('M d, Y') : 'Never' }}</td>
                            <td>
                                <span class="spill spill-{{ $item->is_active ? 'danger' : 'secondary' }}" style="font-size:.72rem;">
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="act-btn act-delete" onclick="removeBlacklist({{ $item->id }})" title="Remove">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6">
                            <div class="empty-state"><i class="bi bi-shield-check"></i><p>No {{ $label }} in blacklist</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addBlacklistModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-ban me-2"></i>Add to Blacklist</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.fraud-alerts.blacklist.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="flabel">Type <span class="req">*</span></label>
                            <select name="type" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="ip">IP Address</option>
                                <option value="email">Email</option>
                                <option value="account">Account</option>
                                <option value="country">Country Code</option>
                                <option value="device">Device ID</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Value <span class="req">*</span></label>
                            <input type="text" name="value" class="form-control" required
                                placeholder="e.g., 192.168.1.1 or user@example.com"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Reason</label>
                            <textarea name="reason" class="form-control" rows="2"
                                placeholder="Why is this being blacklisted?"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Expires At <span style="color:#9ca3af;font-size:.74rem;">(optional)</span></label>
                            <input type="datetime-local" name="expires_at" class="form-control"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-4" style="border-radius:9px;">
                        <i class="bi bi-ban me-1"></i>Add to Blacklist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function removeBlacklist(id) {
    APP.confirm('Remove this blacklist entry?', 'This item will no longer be blocked.', function() {
        fetch(`/admin/fraud-alerts/blacklist/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Removed from blacklist', 'success'); setTimeout(() => location.reload(), 1000); }
        });
    });
}
</script>
@endpush
