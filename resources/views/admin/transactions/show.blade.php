@extends('layouts.app')
@section('title', 'Transaction — '.$transaction->transaction_id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transactions</a></li>
    <li class="breadcrumb-item active">{{ $transaction->transaction_id }}</li>
@endsection

@push('styles')
<style>
.detail-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.detail-hero::before {
    content: ''; position: absolute; top: -50px; right: -30px;
    width: 180px; height: 180px; background: rgba(255,255,255,.06); border-radius: 50%;
}
.tx-id-hero {
    font-family: 'JetBrains Mono','Fira Code',monospace;
    font-size: 1.1rem; font-weight: 800; letter-spacing: -.3px;
}
.hero-status-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px; font-size: .8rem; font-weight: 700;
    backdrop-filter: blur(8px);
}
.hero-status-pill.success  { background: rgba(34,197,94,.2);  color: #86efac; border: 1px solid rgba(34,197,94,.3); }
.hero-status-pill.failed   { background: rgba(239,68,68,.2);  color: #fca5a5; border: 1px solid rgba(239,68,68,.3); }
.hero-status-pill.pending  { background: rgba(234,179,8,.2);  color: #fde047; border: 1px solid rgba(234,179,8,.3); }
.hero-status-pill.processing { background: rgba(59,130,246,.2); color: #93c5fd; border: 1px solid rgba(59,130,246,.3); }
.hero-status-pill.cancelled  { background: rgba(107,114,128,.2); color: #d1d5db; border: 1px solid rgba(107,114,128,.3); }
.hero-status-pill.reversed   { background: rgba(139,92,246,.2); color: #c4b5fd; border: 1px solid rgba(139,92,246,.3); }
.hero-flagged-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(239,68,68,.25); color: #fca5a5; border: 1px solid rgba(239,68,68,.35);
    padding: 6px 14px; border-radius: 20px; font-size: .8rem; font-weight: 700;
    backdrop-filter: blur(8px);
}

.info-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    margin-bottom: 20px;
    overflow: hidden;
}
.info-card-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f3f4f6;
    font-size: .8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #6b7280;
    background: #f9fafb;
    display: flex;
    align-items: center;
    gap: 8px;
}
.info-card-header i { color: #4f46e5; font-size: .9rem; }
.info-card-body { padding: 20px; }

.detail-label { font-size: .72rem; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.detail-val   { font-size: .9rem; color: #111827; font-weight: 500; }
.detail-item  { margin-bottom: 16px; }
.detail-item:last-child { margin-bottom: 0; }

.amount-big { font-size: 2rem; font-weight: 800; color: #16a34a; letter-spacing: -.5px; line-height: 1; }
.amount-type-debit .amount-big { color: #dc2626; }

.party-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
}
.party-box .party-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #9ca3af; margin-bottom: 6px; }
.party-box .party-name  { font-size: .9rem; font-weight: 700; color: #111827; }
.party-box .party-meta  { font-size: .75rem; color: #6b7280; margin-top: 2px; }

.risk-gauge-wrap { text-align: center; padding: 8px 0 16px; }
.risk-score-num  { font-size: 3rem; font-weight: 900; line-height: 1; }
.risk-score-num.low  { color: #16a34a; }
.risk-score-num.mid  { color: #d97706; }
.risk-score-num.high { color: #dc2626; }
.risk-bar-full { height: 10px; border-radius: 6px; background: #e5e7eb; overflow: hidden; margin: 10px 0 6px; }
.risk-bar-fill { height: 100%; border-radius: 6px; }
.risk-bar-fill.low  { background: linear-gradient(90deg, #22c55e, #4ade80); }
.risk-bar-fill.mid  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.risk-bar-fill.high { background: linear-gradient(90deg, #ef4444, #f87171); }
.risk-level-badge {
    display: inline-flex; align-items: center;
    padding: 5px 14px; border-radius: 20px; font-size: .78rem; font-weight: 700;
}
.risk-level-badge.low  { background: #dcfce7; color: #16a34a; }
.risk-level-badge.mid  { background: #fef3c7; color: #92400e; }
.risk-level-badge.high { background: #fee2e2; color: #dc2626; }

.timeline-item { display: flex; gap: 14px; padding-bottom: 20px; position: relative; }
.timeline-item:last-child { padding-bottom: 0; }
.timeline-item:not(:last-child)::before {
    content: ''; position: absolute; left: 15px; top: 32px; bottom: 0;
    width: 2px; background: #f3f4f6;
}
.tl-dot {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: #ede9fe; color: #7c3aed; font-size: .85rem;
    position: relative; z-index: 1;
}
.tl-action { font-size: .85rem; font-weight: 700; color: #111827; }
.tl-meta   { font-size: .75rem; color: #9ca3af; margin-top: 2px; }
.tl-badge  { display: inline-flex; align-items: center; gap: 4px; }

.update-status-card .form-select, .update-status-card .form-control {
    border-radius: 8px; font-size: .85rem; border: 1.5px solid #e5e7eb;
}
.update-status-card .form-select:focus, .update-status-card .form-control:focus {
    border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.1);
}

.fraud-alert-item {
    background: #fff8f0;
    border: 1px solid #fed7aa;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 10px;
}
.fraud-alert-item:last-child { margin-bottom: 0; }
.fa-severity { display: inline-flex; padding: 2px 8px; border-radius: 6px; font-size: .7rem; font-weight: 700; }
.fa-severity.critical { background: #fee2e2; color: #dc2626; }
.fa-severity.high     { background: #fed7aa; color: #c2410c; }
.fa-severity.medium   { background: #fef3c7; color: #92400e; }
.fa-severity.low      { background: #dcfce7; color: #16a34a; }

.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: .82rem; color: #6b7280; text-decoration: none;
    padding: 6px 12px; border-radius: 8px; border: 1px solid #e5e7eb;
    background: #fff; font-weight: 600;
    transition: background .15s, color .15s;
}
.back-btn:hover { background: #f3f4f6; color: #374151; }
</style>
@endpush

@section('content')

{{-- Back + Hero --}}
<div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
    <a href="{{ route('admin.transactions.index') }}" class="back-btn">
        <i class="bi bi-arrow-left"></i>Back to Transactions
    </a>
    <a href="{{ route('admin.transactions.edit', $transaction) }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:.82rem;font-weight:600;
              padding:6px 14px;border-radius:8px;border:1px solid #fcd34d;
              background:#fef3c7;color:#d97706;text-decoration:none;transition:background .15s;"
       onmouseover="this.style.background='#fde68a'"
       onmouseout="this.style.background='#fef3c7'">
        <i class="bi bi-pencil-square"></i>Edit
    </a>
    <a href="{{ route('admin.transactions.receipt', $transaction) }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:.82rem;font-weight:600;
              padding:6px 14px;border-radius:8px;border:1px solid #c4b5fd;
              background:#ede9fe;color:#7c3aed;text-decoration:none;transition:background .15s;"
       onmouseover="this.style.background='#ddd6fe'"
       onmouseout="this.style.background='#ede9fe'">
        <i class="bi bi-file-earmark-pdf"></i>Download Receipt
    </a>
</div>

<div class="detail-hero">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.72rem; opacity:.6; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px;">Transaction</div>
            <div class="tx-id-hero">{{ $transaction->transaction_id }}</div>
            <div style="font-size:.8rem; opacity:.65; margin-top:4px;">
                <i class="bi bi-clock me-1"></i>{{ $transaction->created_at->format('M d, Y · H:i:s') }}
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="hero-status-pill {{ $transaction->status }}">
                <span style="width:7px;height:7px;border-radius:50%;background:currentColor;"></span>
                {{ ucfirst($transaction->status) }}
            </span>
            @if($transaction->is_flagged)
                <span class="hero-flagged-pill"><i class="bi bi-flag-fill"></i>Flagged for Fraud</span>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left Column --}}
    <div class="col-lg-8">

        {{-- Amount + Core Details --}}
        <div class="info-card">
            <div class="info-card-header"><i class="bi bi-credit-card"></i>Transaction Details</div>
            <div class="info-card-body">
                <div class="row g-0 mb-4 pb-4" style="border-bottom:1px solid #f3f4f6;">
                    <div class="col-auto {{ $transaction->type == 'debit' ? 'amount-type-debit' : '' }}">
                        <div class="detail-label">Net Amount</div>
                        <div class="amount-big">₹{{ number_format($transaction->net_amount, 2) }}</div>
                        <div style="margin-top:6px; display:flex; gap:8px; align-items:center;">
                            <span style="background:{{ $transaction->type=='debit'?'#fee2e2':'#dcfce7' }};color:{{ $transaction->type=='debit'?'#dc2626':'#16a34a' }};padding:2px 8px;border-radius:6px;font-size:.72rem;font-weight:700;">{{ ucfirst($transaction->type) }}</span>
                            <span style="font-size:.78rem;color:#9ca3af;">Fee: ₹{{ number_format($transaction->fee, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="detail-label">Gross Amount</div>
                        <div class="detail-val fw-bold">₹{{ number_format($transaction->amount, 2) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Category</div>
                        <div class="detail-val">{{ ucfirst($transaction->category) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-val">{{ str_replace('_',' ', ucfirst($transaction->payment_method ?? 'N/A')) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Reference</div>
                        <div class="detail-val" style="font-family:monospace;font-size:.8rem;">{{ $transaction->reference ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">IP Address</div>
                        <div class="detail-val" style="font-family:monospace;font-size:.8rem;">{{ $transaction->ip_address ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Country</div>
                        <div class="detail-val">{{ $transaction->country ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Device ID</div>
                        <div class="detail-val" style="font-family:monospace;font-size:.78rem;">{{ $transaction->device_id ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Processed At</div>
                        <div class="detail-val" style="font-size:.82rem;">{{ $transaction->processed_at ? \Carbon\Carbon::parse($transaction->processed_at)->format('M d, H:i') : 'N/A' }}</div>
                    </div>
                </div>

                @if($transaction->description)
                <div class="mt-3 pt-3" style="border-top:1px solid #f3f4f6;">
                    <div class="detail-label">Description</div>
                    <div class="detail-val">{{ $transaction->description }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Sender / Receiver --}}
        <div class="info-card">
            <div class="info-card-header"><i class="bi bi-arrow-left-right"></i>Transfer Parties</div>
            <div class="info-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="party-box">
                            <div class="party-label"><i class="bi bi-box-arrow-up-right me-1"></i>Sender</div>
                            <div class="party-name">{{ $transaction->sender_name ?? 'N/A' }}</div>
                            @if($transaction->sender_mobile)<div class="party-meta"><i class="bi bi-phone me-1"></i>{{ $transaction->sender_mobile }}</div>@endif
                            @if($transaction->sender_company)<div class="party-meta"><i class="bi bi-building me-1"></i>{{ $transaction->sender_company }}</div>@endif
                            <div class="party-meta">Account: {{ $transaction->sender_account ?? 'N/A' }}</div>
                            <div class="party-meta">Bank: {{ $transaction->sender_bank ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="party-box">
                            <div class="party-label"><i class="bi bi-box-arrow-in-down-right me-1"></i>Receiver</div>
                            <div class="party-name">{{ $transaction->receiver_name ?? 'N/A' }}</div>
                            @if($transaction->receiver_mobile)<div class="party-meta"><i class="bi bi-phone me-1"></i>{{ $transaction->receiver_mobile }}</div>@endif
                            @if($transaction->receiver_company)<div class="party-meta"><i class="bi bi-building me-1"></i>{{ $transaction->receiver_company }}</div>@endif
                            @if($transaction->receiver_address)<div class="party-meta"><i class="bi bi-geo-alt me-1"></i>{{ $transaction->receiver_address }}</div>@endif
                            <div class="party-meta">Account: {{ $transaction->receiver_account ?? 'N/A' }}</div>
                            <div class="party-meta">Bank: {{ $transaction->receiver_bank ?? 'N/A' }}</div>
                        </div>
                    </div>
                    {{-- Account Owner (company user or external person) --}}
                    @php
                        $extOwner = $transaction->metadata['external_owner'] ?? null;
                    @endphp
                    @if($transaction->user || $extOwner)
                    <div class="col-md-6">
                        <div class="party-box" style="border-color:#c4b5fd;background:#f5f3ff;">
                            <div class="party-label" style="color:#7c3aed;"><i class="bi bi-person-circle me-1"></i>Account Owner</div>
                            @if($extOwner)
                                <div class="party-name">{{ $extOwner['name'] }}</div>
                                @if(!empty($extOwner['mobile']))<div class="party-meta"><i class="bi bi-phone me-1"></i>{{ $extOwner['mobile'] }}</div>@endif
                                @if(!empty($extOwner['company']))<div class="party-meta"><i class="bi bi-building me-1"></i>{{ $extOwner['company'] }}</div>@endif
                                @if(!empty($extOwner['address']))<div class="party-meta"><i class="bi bi-geo-alt me-1"></i>{{ $extOwner['address'] }}</div>@endif
                                <div class="party-meta" style="color:#a78bfa;">External Person</div>
                            @elseif($transaction->user)
                                <div class="party-name">{{ $transaction->user->name }}</div>
                                <div class="party-meta">{{ $transaction->user->email }}</div>
                                <div class="party-meta" style="color:#7c3aed;">{{ ucfirst(str_replace('_',' ',$transaction->user->role)) }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Attachments --}}
        @if($transaction->attachments && count($transaction->attachments) > 0)
        <div class="info-card">
            <div class="info-card-header"><i class="bi bi-paperclip"></i>Attachments ({{ count($transaction->attachments) }})</div>
            <div class="info-card-body">
                <div class="row g-2">
                    @foreach($transaction->attachments as $att)
                    @php
                        $filename = $att['original'] ?? basename($att['path']);
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $isPdf = $ext === 'pdf';
                        $icon = $isPdf ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-image-fill';
                        $iconColor = $isPdf ? '#dc2626' : '#2563eb';
                    @endphp
                    <div class="col-md-6">
                        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;display:flex;align-items:center;gap:10px;">
                            <i class="bi {{ $icon }}" style="font-size:1.5rem;color:{{ $iconColor }};flex-shrink:0;"></i>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:.82rem;font-weight:600;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $filename }}
                                </div>
                                <div style="font-size:.72rem;color:#9ca3af;margin-top:2px;">
                                    {{ ucfirst($att['type'] ?? 'file') }}
                                    @if(isset($att['uploaded_at'])) &bull; {{ \Carbon\Carbon::parse($att['uploaded_at'])->format('d M Y') }} @endif
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $att['path']) }}" target="_blank"
                               style="display:inline-flex;align-items:center;justify-content:center;
                                      width:30px;height:30px;border-radius:7px;
                                      background:#ede9fe;color:#7c3aed;text-decoration:none;flex-shrink:0;"
                               title="Open attachment">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Timeline --}}
        <div class="info-card">
            <div class="info-card-header"><i class="bi bi-clock-history"></i>Activity Timeline</div>
            <div class="info-card-body">
                @forelse($transaction->logs as $log)
                <div class="timeline-item">
                    <div class="tl-dot"><i class="bi bi-arrow-right-circle"></i></div>
                    <div>
                        <div class="tl-action">{{ ucwords(str_replace('_', ' ', $log->action)) }}</div>
                        @if($log->from_status && $log->to_status)
                        <div class="tl-badge mt-1">
                            <span style="background:#f3f4f6;color:#374151;padding:2px 8px;border-radius:5px;font-size:.72rem;font-weight:600;">{{ $log->from_status }}</span>
                            <i class="bi bi-arrow-right mx-1" style="color:#9ca3af;font-size:.7rem;"></i>
                            <span style="background:#ede9fe;color:#7c3aed;padding:2px 8px;border-radius:5px;font-size:.72rem;font-weight:600;">{{ $log->to_status }}</span>
                        </div>
                        @endif
                        @if($log->notes)<div style="font-size:.78rem;color:#6b7280;margin-top:3px;">{{ $log->notes }}</div>@endif
                        <div class="tl-meta"><i class="bi bi-person me-1"></i>{{ $log->performer?->name ?? 'System' }} &bull; {{ $log->created_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
                @empty
                <p style="font-size:.85rem;color:#9ca3af;text-align:center;padding:16px 0;margin:0;">No activity recorded yet</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Right Column --}}
    <div class="col-lg-4">

        {{-- Risk Score --}}
        <div class="info-card">
            @php $riskClass = $transaction->risk_score >= 70 ? 'high' : ($transaction->risk_score >= 40 ? 'mid' : 'low'); @endphp
            <div class="info-card-header"><i class="bi bi-shield-check"></i>Risk Assessment</div>
            <div class="info-card-body risk-gauge-wrap">
                <div class="risk-score-num {{ $riskClass }}">{{ $transaction->risk_score }}</div>
                <div style="font-size:.75rem;color:#9ca3af;margin-top:4px;">out of 100</div>
                <div class="risk-bar-full">
                    <div class="risk-bar-fill {{ $riskClass }}" style="width:{{ $transaction->risk_score }}%"></div>
                </div>
                <span class="risk-level-badge {{ $riskClass }}">
                    <i class="bi bi-{{ $riskClass=='high'?'exclamation-triangle-fill':($riskClass=='mid'?'dash-circle-fill':'check-circle-fill') }} me-1"></i>
                    {{ ucfirst($transaction->risk_level) }} Risk
                </span>
            </div>
        </div>

        {{-- Update Status --}}
        <div class="info-card update-status-card">
            <div class="info-card-header"><i class="bi bi-pencil-square"></i>Update Status</div>
            <div class="info-card-body">
                <div class="mb-3">
                    <label style="font-size:.75rem;font-weight:600;color:#374151;display:block;margin-bottom:6px;">New Status</label>
                    <select id="newStatus" class="form-select">
                        @foreach(['pending','processing','success','failed','cancelled','reversed'] as $s)
                            <option value="{{ $s }}" {{ $transaction->status==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label style="font-size:.75rem;font-weight:600;color:#374151;display:block;margin-bottom:6px;">Notes</label>
                    <textarea id="statusNotes" class="form-control" rows="2" placeholder="Optional notes…" style="resize:none;"></textarea>
                </div>
                <button class="btn btn-primary btn-sm w-100" onclick="updateStatus()">
                    <i class="bi bi-check-lg me-1"></i>Update Status
                </button>
            </div>
        </div>

        {{-- Fraud Alerts --}}
        @if($transaction->fraudAlerts->count())
        <div class="info-card">
            <div class="info-card-header" style="background:#fff8f0;border-bottom-color:#fed7aa;">
                <i class="bi bi-shield-exclamation" style="color:#ea580c;"></i>
                <span style="color:#c2410c;">Fraud Alerts ({{ $transaction->fraudAlerts->count() }})</span>
            </div>
            <div class="info-card-body">
                @foreach($transaction->fraudAlerts as $alert)
                <div class="fraud-alert-item">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                        <div style="font-size:.83rem;font-weight:700;color:#111827;">{{ $alert->alert_type }}</div>
                        <span class="fa-severity {{ $alert->severity }}">{{ ucfirst($alert->severity) }}</span>
                    </div>
                    <div style="font-size:.78rem;color:#6b7280;">{{ $alert->description }}</div>
                    <div style="font-size:.72rem;color:#9ca3af;margin-top:4px;">Risk score: <strong>{{ $alert->risk_score }}</strong></div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
function updateStatus() {
    const status = document.getElementById('newStatus').value;
    const notes  = document.getElementById('statusNotes').value;
    APP.ajax('/admin/transactions/{{ $transaction->id }}/status', 'POST', { status, notes })
        .done(res => {
            if (res.success) { APP.toast('Status updated!'); setTimeout(() => location.reload(), 1000); }
            else { APP.toast(res.message || 'Update failed', 'error'); }
        })
        .fail(xhr => {
            const msg = xhr.responseJSON?.message || 'Status update failed';
            APP.toast(msg, 'error');
        });
}
</script>
@endpush
