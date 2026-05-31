@extends('layouts.app')
@section('title', 'Settings')

@section('content')

<div class="page-hero">
    <div style="position:relative;z-index:1;">
        <h4>System Settings</h4>
        <p>Configure application preferences and integrations</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius:10px;border:none;background:#dcfce7;color:#166534;">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@php
    $groups = [
        'general'      => ['icon' => 'gear',                 'label' => 'General'],
        'notification' => ['icon' => 'bell',                 'label' => 'Notifications'],
        'security'     => ['icon' => 'shield-lock',          'label' => 'Security'],
        'fraud'        => ['icon' => 'exclamation-triangle', 'label' => 'Fraud Detection'],
        'smtp'         => ['icon' => 'envelope',             'label' => 'Email (SMTP)'],
    ];
    $currentGroup = $group ?? 'general';
@endphp

<div class="row g-4">
    {{-- Sidebar Nav --}}
    <div class="col-lg-3">
        <div style="background:#fff;border-radius:14px;border:1.5px solid #f0f0f5;overflow:hidden;">
            @foreach($groups as $key => $g)
            <a href="{{ route('admin.settings.index', $key) }}"
                style="display:flex;align-items:center;gap:10px;padding:12px 16px;text-decoration:none;border-bottom:1px solid #f3f4f6;font-size:.85rem;font-weight:{{ $currentGroup === $key ? '700' : '500' }};color:{{ $currentGroup === $key ? '#4f46e5' : '#374151' }};background:{{ $currentGroup === $key ? '#f0f4ff' : 'transparent' }};">
                <i class="bi bi-{{ $g['icon'] }}" style="font-size:1rem;width:18px;flex-shrink:0;"></i>
                {{ $g['label'] }}
                @if($currentGroup === $key)
                <i class="bi bi-chevron-right ms-auto" style="font-size:.7rem;opacity:.5;"></i>
                @endif
            </a>
            @endforeach
        </div>
    </div>

    {{-- Settings Form --}}
    <div class="col-lg-9">
        <div class="form-section">
            <div class="form-section-hdr">
                <i class="bi bi-{{ $groups[$currentGroup]['icon'] ?? 'gear' }} me-2"></i>
                {{ $groups[$currentGroup]['label'] ?? ucfirst($currentGroup) }} Settings
            </div>
            <div class="form-section-body">
                <form action="{{ route('admin.settings.update', $currentGroup) }}" method="POST">
                    @csrf

                    @if($currentGroup === 'general')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Application Name</label>
                            <input type="text" name="app_name" class="form-control"
                                value="{{ old('app_name', $settings['app_name'] ?? 'Transaction Monitor') }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Default Currency</label>
                            <select name="default_currency" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach(['INR'=>'INR (₹)','USD'=>'USD ($)','EUR'=>'EUR (€)','GBP'=>'GBP (£)'] as $val => $label)
                                    <option value="{{ $val }}" @selected(($settings['default_currency'] ?? 'INR') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Default Timezone</label>
                            <select name="timezone" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" @selected(($settings['timezone'] ?? 'UTC') === $tz)>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Items Per Page</label>
                            <select name="per_page" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach([10,15,25,50,100] as $pp)
                                    <option value="{{ $pp }}" @selected(($settings['per_page'] ?? 15) == $pp)>{{ $pp }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Company Address</label>
                            <textarea name="company_address" class="form-control" rows="2"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;">{{ $settings['company_address'] ?? '' }}</textarea>
                        </div>
                    </div>

                    @elseif($currentGroup === 'notification')
                    <div class="row g-3">
                        @foreach([
                            'notify_fraud_alert'        => 'Send notifications for new fraud alerts',
                            'notify_failed_transaction' => 'Notify on failed transactions',
                            'notify_new_employee'       => 'Notify when new employee registers',
                            'notify_task_assigned'      => 'Notify employees on task assignment',
                            'notify_leave_request'      => 'Notify managers on leave requests',
                        ] as $key => $label)
                        <div class="col-12">
                            <div class="form-check form-switch" style="padding-left:2.5em;">
                                <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1"
                                    @checked(($settings[$key] ?? '1') === '1') style="cursor:pointer;">
                                <label class="form-check-label" style="font-size:.85rem;color:#374151;">{{ $label }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @elseif($currentGroup === 'security')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="flabel">Max Login Attempts</label>
                            <input type="number" name="max_login_attempts" class="form-control"
                                value="{{ $settings['max_login_attempts'] ?? 5 }}" min="3" max="20"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Lockout Duration (minutes)</label>
                            <input type="number" name="lockout_duration" class="form-control"
                                value="{{ $settings['lockout_duration'] ?? 5 }}" min="1"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Session Timeout (minutes)</label>
                            <input type="number" name="session_timeout" class="form-control"
                                value="{{ $settings['session_timeout'] ?? 120 }}" min="15"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch" style="padding-left:2.5em;">
                                <input class="form-check-input" type="checkbox" name="require_2fa" value="1"
                                    @checked(($settings['require_2fa'] ?? '0') === '1') style="cursor:pointer;">
                                <label class="form-check-label" style="font-size:.85rem;color:#374151;">Require 2FA for admin accounts</label>
                            </div>
                        </div>
                    </div>

                    @elseif($currentGroup === 'fraud')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">High Amount Threshold ($)</label>
                            <input type="number" name="fraud_high_amount" class="form-control"
                                value="{{ $settings['fraud_high_amount'] ?? 10000 }}" min="100"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Critical Amount Threshold ($)</label>
                            <input type="number" name="fraud_critical_amount" class="form-control"
                                value="{{ $settings['fraud_critical_amount'] ?? 50000 }}" min="1000"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Velocity Check (transactions/hour)</label>
                            <input type="number" name="fraud_velocity_limit" class="form-control"
                                value="{{ $settings['fraud_velocity_limit'] ?? 5 }}" min="1"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Duplicate Detection Window (minutes)</label>
                            <input type="number" name="fraud_duplicate_window" class="form-control"
                                value="{{ $settings['fraud_duplicate_window'] ?? 10 }}" min="1"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch" style="padding-left:2.5em;">
                                <input class="form-check-input" type="checkbox" name="auto_block_high_risk" value="1"
                                    @checked(($settings['auto_block_high_risk'] ?? '0') === '1') style="cursor:pointer;">
                                <label class="form-check-label" style="font-size:.85rem;color:#374151;">Auto-block transactions with risk score &gt; 90</label>
                            </div>
                        </div>
                    </div>

                    @elseif($currentGroup === 'smtp')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control"
                                value="{{ $settings['smtp_host'] ?? 'smtp.mailtrap.io' }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-control"
                                value="{{ $settings['smtp_port'] ?? 587 }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">SMTP Username</label>
                            <input type="text" name="smtp_username" class="form-control"
                                value="{{ $settings['smtp_username'] ?? '' }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">SMTP Password</label>
                            <input type="password" name="smtp_password" class="form-control"
                                placeholder="Leave blank to keep current"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">From Email</label>
                            <input type="email" name="smtp_from_address" class="form-control"
                                value="{{ $settings['smtp_from_address'] ?? '' }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Encryption</label>
                            <select name="smtp_encryption" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="tls" @selected(($settings['smtp_encryption'] ?? 'tls') === 'tls')>TLS</option>
                                <option value="ssl" @selected(($settings['smtp_encryption'] ?? '') === 'ssl')>SSL</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="testSmtp()" style="border-radius:8px;">
                                <i class="bi bi-send-check me-1"></i>Test SMTP Connection
                            </button>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <button type="submit" class="btn btn-sm btn-primary-grad px-4">
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
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }).then(r => r.json()).then(data => {
        APP.toast(data.message || 'SMTP test done', data.success ? 'success' : 'error');
    }).catch(() => APP.toast('SMTP test failed', 'error'));
}
</script>
@endpush
