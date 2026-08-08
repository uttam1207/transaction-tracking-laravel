@extends('layouts.app')
@section('title', 'Fraud Alert #'.$fraudAlert->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fraud-alerts.index') }}">Fraud Alerts</a></li>
    <li class="breadcrumb-item active">#{{ $fraudAlert->id }}</li>
@endsection

@section('content')
@php
    $sev = $fraudAlert->severity ?? 'medium';
    $st  = $fraudAlert->status  ?? 'open';
    $rs  = $fraudAlert->risk_score ?? 0;
    $riskCls = $rs >= 70 ? 'danger' : ($rs >= 40 ? 'warning' : 'success');
    $sevColors = ['critical'=>'#dc2626','high'=>'#c2410c','medium'=>'#d97706','low'=>'#16a34a'];
@endphp

<div class="page-hero" style="background:linear-gradient(135deg,#7f1d1d,#991b1b,#dc2626);">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.7rem;opacity:.6;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Fraud Alert</div>
            <h4 style="font-size:1.25rem;">#{{ $fraudAlert->id }} — {{ ucwords(str_replace('_',' ',$fraudAlert->alert_type)) }}</h4>
            <p>Detected {{ $fraudAlert->created_at->format('M d, Y H:i') }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="spill spill-{{ $sev=='critical'||$sev=='high'?'danger':($sev=='medium'?'warning':'success') }}" style="font-size:.8rem;padding:5px 14px;">{{ ucfirst($sev) }} Severity</span>
            <span class="spill spill-{{ $st }}" style="font-size:.8rem;padding:5px 14px;">{{ ucfirst(str_replace('_',' ',$st)) }}</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#statusModal">
                <i class="bi bi-arrow-repeat me-1"></i>Update Status
            </button>
            <button class="btn btn-sm" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:8px;font-weight:600;" data-bs-toggle="modal" data-bs-target="#assignModal">
                <i class="bi bi-person-check me-1"></i>Assign
            </button>
            <a href="{{ route('admin.fraud-alerts.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-weight:600;">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">

        {{-- Risk Score --}}
        <div class="card-glass overflow-hidden mb-3">
            <div style="background:linear-gradient(135deg,#dc2626,#b91c1c);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-exclamation" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Risk Assessment</span>
                </div>
            </div>
            <div class="p-4 text-center">
                <div style="font-size:3rem;font-weight:900;line-height:1;color:{{ $riskCls=='danger'?'#dc2626':($riskCls=='warning'?'#d97706':'#16a34a') }};">{{ $rs }}</div>
                <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">out of 100</div>
                <div style="height:10px;border-radius:6px;background:#e5e7eb;overflow:hidden;margin:12px 0 8px;">
                    <div style="height:100%;width:{{ $rs }}%;background:{{ $riskCls=='danger'?'linear-gradient(90deg,#ef4444,#f87171)':($riskCls=='warning'?'linear-gradient(90deg,#f59e0b,#fbbf24)':'linear-gradient(90deg,#22c55e,#4ade80)') }};border-radius:6px;"></div>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="card-glass overflow-hidden mb-3">
            <div style="background:linear-gradient(135deg,#374151,#1f2937);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Alert Summary</span>
                </div>
            </div>
            <div class="p-3">
                <dl class="dl mb-0">
                    <dt>Alert Type</dt><dd>{{ ucwords(str_replace('_',' ',$fraudAlert->alert_type)) }}</dd>
                    <dt>Severity</dt><dd><span class="spill spill-{{ $sev=='critical'||$sev=='high'?'danger':($sev=='medium'?'warning':'success') }}">{{ ucfirst($sev) }}</span></dd>
                    <dt>Status</dt><dd><span class="spill spill-{{ $st }}">{{ ucfirst(str_replace('_',' ',$st)) }}</span></dd>
                    <dt>Assigned To</dt><dd>{{ $fraudAlert->assignedTo->name ?? 'Unassigned' }}</dd>
                    <dt>Detected At</dt><dd>{{ $fraudAlert->created_at->format('M d, Y H:i') }}</dd>
                    @if($fraudAlert->resolved_at)
                    <dt>Resolved At</dt><dd>{{ \Carbon\Carbon::parse($fraudAlert->resolved_at)->format('M d, Y H:i') }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @if($fraudAlert->resolution_notes)
        <div class="card-glass overflow-hidden">
            <div style="background:linear-gradient(135deg,#16a34a,#059669);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Resolution Notes</span>
                </div>
            </div>
            <div class="p-4">
                <p style="font-size:.85rem;color:#374151;line-height:1.6;margin:0;">{{ $fraudAlert->resolution_notes }}</p>
            </div>
        </div>
        @endif

    </div>

    <div class="col-lg-8">

        {{-- Description --}}
        <div class="card-glass overflow-hidden mb-3">
            <div style="background:linear-gradient(135deg,#374151,#1f2937);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-text-paragraph" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Description</span>
                </div>
            </div>
            <div class="p-4">
                <p style="font-size:.88rem;color:#374151;line-height:1.7;margin:0;">{{ $fraudAlert->description }}</p>
            </div>
        </div>

        {{-- Related Transaction --}}
        @if($fraudAlert->transaction)
        @php $tx = $fraudAlert->transaction; @endphp
        <div class="card-glass overflow-hidden mb-3">
            <div style="background:linear-gradient(135deg,#2563eb,#4f46e5);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left-right" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                        <span style="font-size:.82rem;font-weight:700;color:#fff;">Related Transaction</span>
                    </div>
                    <a href="{{ route('admin.transactions.show', $tx) }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:7px;font-size:.75rem;padding:4px 12px;">
                        View <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <dl class="dl mb-0"><dt>Transaction ID</dt><dd style="font-family:monospace;color:#4f46e5;">{{ $tx->transaction_id }}</dd></dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="dl mb-0"><dt>Amount</dt><dd style="font-weight:700;">{{ $tx->currency }} {{ number_format($tx->amount,2) }}</dd></dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="dl mb-0"><dt>Sender</dt><dd>{{ $tx->sender_name }} <span style="color:#9ca3af;">({{ $tx->sender_account }})</span></dd></dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="dl mb-0"><dt>Receiver</dt><dd>{{ $tx->receiver_name }} <span style="color:#9ca3af;">({{ $tx->receiver_account }})</span></dd></dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="dl mb-0"><dt>Country</dt><dd>{{ $tx->country ?? 'N/A' }}</dd></dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="dl mb-0"><dt>IP Address</dt><dd style="font-family:monospace;font-size:.8rem;">{{ $tx->ip_address ?? 'N/A' }}</dd></dl>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Metadata --}}
        @if($fraudAlert->metadata)
        <div class="card-glass overflow-hidden">
            <div style="background:linear-gradient(135deg,#374151,#1f2937);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-code-slash" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Detection Metadata</span>
                </div>
            </div>
            <div class="p-4">
                <pre style="margin:0;font-size:.78rem;background:#f8fafc;border:1px solid #e5e7eb;border-radius:9px;padding:14px;overflow-x:auto;color:#374151;">{{ json_encode($fraudAlert->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Status Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Update Alert Status</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.fraud-alerts.status', $fraudAlert) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="flabel">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="open" @selected($fraudAlert->status==='open')>Open</option>
                            <option value="investigating" @selected($fraudAlert->status==='investigating')>Investigating</option>
                            <option value="resolved" @selected($fraudAlert->status==='resolved')>Resolved</option>
                            <option value="false_positive" @selected($fraudAlert->status==='false_positive')>False Positive</option>
                        </select>
                    </div>
                    <div>
                        <label class="flabel">Resolution Notes</label>
                        <textarea name="resolution_notes" class="form-control" rows="3" placeholder="Add notes…" style="border-radius:9px;border:1.5px solid #e5e7eb;">{{ $fraudAlert->resolution_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Assign Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Assign Alert</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.fraud-alerts.assign', $fraudAlert) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="flabel">Assign To</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select User</option>
                        @foreach($admins ?? [] as $admin)
                            <option value="{{ $admin->id }}" @selected($fraudAlert->assigned_to==$admin->id)>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
