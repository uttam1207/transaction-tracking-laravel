@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">System Settings</h4>
        <p class="text-muted mb-0">Configure application preferences</p>
    </div>
</div>

<div class="row g-4">
    {{-- Settings Nav --}}
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="list-group list-group-flush rounded">
                @php
                    $groups = [
                        'general' => ['icon' => 'gear', 'label' => 'General'],
                        'notification' => ['icon' => 'bell', 'label' => 'Notifications'],
                        'security' => ['icon' => 'shield-lock', 'label' => 'Security'],
                        'fraud' => ['icon' => 'exclamation-triangle', 'label' => 'Fraud Detection'],
                        'smtp' => ['icon' => 'envelope', 'label' => 'Email (SMTP)'],
                    ];
                    $currentGroup = $group ?? 'general';
                @endphp
                @foreach($groups as $key => $g)
                <a href="{{ route('admin.settings.index', $key) }}"
                    class="list-group-item list-group-item-action d-flex align-items-center gap-2
                    {{ $currentGroup === $key ? 'active' : '' }}">
                    <i class="bi bi-{{ $g['icon'] }}"></i>
                    {{ $g['label'] }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Settings Form --}}
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="mb-0">
                    <i class="bi bi-{{ $groups[$currentGroup]['icon'] ?? 'gear' }} me-2"></i>
                    {{ $groups[$currentGroup]['label'] ?? ucfirst($currentGroup) }} Settings
                </h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.settings.update', $currentGroup) }}" method="POST">
                    @csrf

                    @if($currentGroup === 'general')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Application Name</label>
                            <input type="text" name="app_name" class="form-control"
                                value="{{ old('app_name', $settings['app_name'] ?? 'Transaction Monitor') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Currency</label>
                            <select name="default_currency" class="form-select">
                                @foreach(['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)'] as $val => $label)
                                    <option value="{{ $val }}" @selected(($settings['default_currency'] ?? 'USD') === $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Timezone</label>
                            <select name="timezone" class="form-select">
                                @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" @selected(($settings['timezone'] ?? 'UTC') === $tz)>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Items Per Page</label>
                            <select name="per_page" class="form-select">
                                @foreach([10, 15, 25, 50, 100] as $pp)
                                    <option value="{{ $pp }}" @selected(($settings['per_page'] ?? 15) == $pp)>{{ $pp }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Company Address</label>
                            <textarea name="company_address" class="form-control" rows="2">{{ $settings['company_address'] ?? '' }}</textarea>
                        </div>
                    </div>

                    @elseif($currentGroup === 'notification')
                    <div class="row g-3">
                        @foreach([
                            'notify_fraud_alert' => 'Send notifications for new fraud alerts',
                            'notify_failed_transaction' => 'Notify on failed transactions',
                            'notify_new_employee' => 'Notify when new employee registers',
                            'notify_task_assigned' => 'Notify employees on task assignment',
                            'notify_leave_request' => 'Notify managers on leave requests',
                        ] as $key => $label)
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1"
                                    @checked(($settings[$key] ?? '1') === '1')>
                                <label class="form-check-label">{{ $label }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @elseif($currentGroup === 'security')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Max Login Attempts</label>
                            <input type="number" name="max_login_attempts" class="form-control"
                                value="{{ $settings['max_login_attempts'] ?? 5 }}" min="3" max="20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lockout Duration (minutes)</label>
                            <input type="number" name="lockout_duration" class="form-control"
                                value="{{ $settings['lockout_duration'] ?? 5 }}" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Session Timeout (minutes)</label>
                            <input type="number" name="session_timeout" class="form-control"
                                value="{{ $settings['session_timeout'] ?? 120 }}" min="15">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="require_2fa" value="1"
                                    @checked(($settings['require_2fa'] ?? '0') === '1')>
                                <label class="form-check-label">Require 2FA for admin accounts</label>
                            </div>
                        </div>
                    </div>

                    @elseif($currentGroup === 'fraud')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">High Amount Threshold ($)</label>
                            <input type="number" name="fraud_high_amount" class="form-control"
                                value="{{ $settings['fraud_high_amount'] ?? 10000 }}" min="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Critical Amount Threshold ($)</label>
                            <input type="number" name="fraud_critical_amount" class="form-control"
                                value="{{ $settings['fraud_critical_amount'] ?? 50000 }}" min="1000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Velocity Check (transactions/hour)</label>
                            <input type="number" name="fraud_velocity_limit" class="form-control"
                                value="{{ $settings['fraud_velocity_limit'] ?? 5 }}" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duplicate Detection Window (minutes)</label>
                            <input type="number" name="fraud_duplicate_window" class="form-control"
                                value="{{ $settings['fraud_duplicate_window'] ?? 10 }}" min="1">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="auto_block_high_risk" value="1"
                                    @checked(($settings['auto_block_high_risk'] ?? '0') === '1')>
                                <label class="form-check-label">Auto-block transactions with risk score > 90</label>
                            </div>
                        </div>
                    </div>

                    @elseif($currentGroup === 'smtp')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control"
                                value="{{ $settings['smtp_host'] ?? 'smtp.mailtrap.io' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-control"
                                value="{{ $settings['smtp_port'] ?? 587 }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP Username</label>
                            <input type="text" name="smtp_username" class="form-control"
                                value="{{ $settings['smtp_username'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP Password</label>
                            <input type="password" name="smtp_password" class="form-control"
                                placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Email</label>
                            <input type="email" name="smtp_from_address" class="form-control"
                                value="{{ $settings['smtp_from_address'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value="tls" @selected(($settings['smtp_encryption'] ?? 'tls') === 'tls')>TLS</option>
                                <option value="ssl" @selected(($settings['smtp_encryption'] ?? '') === 'ssl')>SSL</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-info" onclick="testSmtp()">
                                <i class="bi bi-send-check me-1"></i>Test SMTP Connection
                            </button>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function testSmtp() {
    fetch('/admin/settings/test-smtp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        }
    }).then(r => r.json()).then(data => {
        APP.toast(data.message || 'SMTP test done', data.success ? 'success' : 'danger');
    }).catch(() => APP.toast('SMTP test failed', 'danger'));
}
</script>
@endpush
