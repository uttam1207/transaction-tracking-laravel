@extends('layouts.app')

@section('title', 'Blacklist Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.fraud-alerts.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Alerts
        </a>
        <h4 class="mb-0 fw-bold mt-1">Blacklist Management</h4>
    </div>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addBlacklistModal">
        <i class="bi bi-plus-circle me-1"></i>Add to Blacklist
    </button>
</div>

<div class="row g-3 mb-4">
    @foreach(['ip' => 'IP Addresses', 'email' => 'Emails', 'account' => 'Accounts', 'country' => 'Countries'] as $type => $label)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-danger">
                {{ $blacklists->where('type', $type)->count() }}
            </div>
            <div class="text-muted">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <ul class="nav nav-tabs card-header-tabs" id="blacklistTabs">
            @foreach(['ip' => 'IP Addresses', 'email' => 'Emails', 'account' => 'Accounts', 'country' => 'Countries', 'device' => 'Devices'] as $type => $label)
            <li class="nav-item">
                <a class="nav-link @if($loop->first) active @endif"
                    data-bs-toggle="tab" href="#tab-{{ $type }}">
                    {{ $label }}
                    <span class="badge bg-danger ms-1">
                        {{ $blacklists->where('type', $type)->count() }}
                    </span>
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            @foreach(['ip' => 'IP Addresses', 'email' => 'Emails', 'account' => 'Accounts', 'country' => 'Countries', 'device' => 'Devices'] as $type => $label)
            <div class="tab-pane fade @if($loop->first) show active @endif" id="tab-{{ $type }}">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
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
                                <td><code>{{ $item->value }}</code></td>
                                <td>{{ $item->reason ?? '—' }}</td>
                                <td>{{ $item->addedBy->name ?? '—' }}</td>
                                <td>{{ $item->expires_at ? \Carbon\Carbon::parse($item->expires_at)->format('M d, Y') : 'Never' }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->is_active ? 'danger' : 'secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary"
                                        onclick="removeBlacklist({{ $item->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No {{ $label }} in blacklist
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Add Blacklist Modal --}}
<div class="modal fade" id="addBlacklistModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-ban me-2"></i>Add to Blacklist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.fraud-alerts.blacklist.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="ip">IP Address</option>
                            <option value="email">Email</option>
                            <option value="account">Account</option>
                            <option value="country">Country Code</option>
                            <option value="device">Device ID</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Value</label>
                        <input type="text" name="value" class="form-control"
                            placeholder="e.g., 192.168.1.1 or user@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="2"
                            placeholder="Why is this being blacklisted?"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expires At <small class="text-muted">(optional)</small></label>
                        <input type="datetime-local" name="expires_at" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
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
    APP.confirm('Remove this blacklist entry?', function() {
        fetch(`/admin/fraud-alerts/blacklist/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                APP.toast('Removed from blacklist', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        });
    });
}
</script>
@endpush
