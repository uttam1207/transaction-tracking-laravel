<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cash Voucher — {{ $transaction->transaction_id }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DejaVu Sans',Arial,sans-serif; font-size:9px; color:#111; background:#fff; }

.page { padding:18px 22px; border:2.5px solid #111; position:relative; }

/* ── Header ─────────────────────────────────── */
.hdr { display:table; width:100%; padding-bottom:10px; border-bottom:2px solid #111; margin-bottom:10px; }
.hdr-l { display:table-cell; vertical-align:middle; }
.hdr-r { display:table-cell; vertical-align:middle; text-align:right; }
.co-name { font-size:17px; font-weight:800; letter-spacing:-.5px; color:#111; }
.co-sub  { font-size:6.5px; color:#666; text-transform:uppercase; letter-spacing:1px; margin-top:2px; }
.v-title { font-size:13px; font-weight:900; text-transform:uppercase; letter-spacing:.5px; color:#111; }
.v-sub   { font-size:7px; color:#666; margin-top:3px; }

/* ── Meta row ────────────────────────────────── */
.meta { display:table; width:100%; margin-bottom:10px; }
.meta-l { display:table-cell; vertical-align:bottom; width:50%; }
.meta-c { display:table-cell; vertical-align:middle; text-align:center; width:20%; }
.meta-r { display:table-cell; vertical-align:bottom; width:30%; text-align:right; }
.lbl-xs { font-size:6.5px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#777; display:block; margin-bottom:2px; }
.val-md  { font-size:10.5px; font-weight:700; color:#111; }
.val-mono{ font-family:'DejaVu Sans Mono',monospace; font-size:9.5px; font-weight:700; color:#4f46e5; }

.badge { display:inline-block; padding:2px 10px; border-radius:3px; font-size:7.5px; font-weight:800; text-transform:uppercase; letter-spacing:.8px; }
.badge-cr { background:#dcfce7; color:#15803d; border:1px solid #86efac; }
.badge-dr { background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; }

/* ── Field rows ──────────────────────────────── */
.f-row { border-bottom:1px solid #aaa; padding:3px 2px 5px; margin-bottom:7px; }
.f-row .lbl { font-size:6.5px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#777; }
.f-row .val { font-size:10px; font-weight:600; color:#111; margin-top:3px; min-height:13px; }

/* ── Amount box ──────────────────────────────── */
.amt-box { border:2px solid #111; padding:8px 12px; text-align:center; margin:8px 0; }
.amt-lbl { font-size:7px; text-transform:uppercase; letter-spacing:.5px; color:#666; margin-bottom:4px; }
.amt-val { font-size:24px; font-weight:900; letter-spacing:-1px; color:#111; }
.amt-meta{ font-size:7.5px; color:#777; margin-top:3px; }

/* ── Words row ───────────────────────────────── */
.words-row { border:1px solid #bbb; padding:6px 8px; margin-bottom:8px; background:#fafafa; }

/* ── Info table ──────────────────────────────── */
.info-tbl { width:100%; border-collapse:collapse; margin-bottom:10px; }
.info-tbl td { padding:4px 7px; border:1px solid #ddd; font-size:8.5px; }
.info-tbl .k  { font-size:7px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#777; width:22%; }

/* ── Signature section ───────────────────────── */
.sig-wrap { display:table; width:100%; margin-top:18px; }
.sig-cell { display:table-cell; text-align:center; padding:0 6px; }
.sig-line { border-top:1.5px solid #111; margin-top:30px; padding-top:4px; font-size:7px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#555; }

/* ── Footer ──────────────────────────────────── */
.footer { margin-top:10px; border-top:1px dashed #ccc; padding-top:5px; text-align:center; font-size:6.5px; color:#aaa; }

/* ── Watermark ───────────────────────────────── */
.wm { position:fixed; bottom:80px; right:30px; font-size:52px; font-weight:900;
      color:rgba(0,0,0,.04); transform:rotate(-28deg); letter-spacing:4px; text-transform:uppercase; }
</style>
</head>
<body>
<div class="wm">VOUCHER</div>
<div class="page">

    {{-- Header --}}
    <div class="hdr">
        <div class="hdr-l">
            <div class="co-name">AS Dairy Dashboard</div>
            <div class="co-sub">Dairy Management System</div>
        </div>
        <div class="hdr-r">
            <div class="v-title">
                {{ $transaction->type === 'credit' ? 'Cash Receipt Voucher' : 'Cash Payment Voucher' }}
            </div>
            <div class="v-sub">
                {{ $transaction->type === 'credit' ? 'Money received from party' : 'Money paid to party' }}
            </div>
        </div>
    </div>

    {{-- Meta row --}}
    <div class="meta">
        <div class="meta-l">
            <span class="lbl-xs">Voucher No.</span>
            <span class="val-mono">{{ $transaction->transaction_id }}</span>
        </div>
        <div class="meta-c">
            <span class="badge {{ $transaction->type === 'credit' ? 'badge-cr' : 'badge-dr' }}">
                {{ $transaction->type === 'credit' ? 'Receipt' : 'Payment' }}
            </span>
        </div>
        <div class="meta-r">
            <span class="lbl-xs">Date</span>
            <span class="val-md">
                {{ \Carbon\Carbon::parse($transaction->processed_at ?? $transaction->created_at)->format('d M Y') }}
            </span>
        </div>
    </div>

    {{-- Paid To / Received From --}}
    @php
        $partyLabel = $transaction->type === 'credit' ? 'Received From' : 'Paid To';
        $partyName  = $transaction->type === 'credit'
            ? ($transaction->sender_name   ?? '—')
            : ($transaction->receiver_name ?? '—');
        $partyCo    = $transaction->type === 'credit'
            ? ($transaction->sender_company   ?? '')
            : ($transaction->receiver_company ?? '');
        $partyMob   = $transaction->type === 'credit'
            ? ($transaction->sender_mobile   ?? '')
            : ($transaction->receiver_mobile ?? '');
        $partyBank  = $transaction->type === 'credit'
            ? ($transaction->sender_bank   ?? '')
            : ($transaction->receiver_bank ?? '');
    @endphp

    <div class="f-row">
        <div class="lbl">{{ $partyLabel }}</div>
        <div class="val">{{ $partyName }}
            @if($partyCo) &nbsp;<span style="font-size:8px;color:#666;">({{ $partyCo }})</span>@endif
        </div>
        @if($partyMob || $partyBank)
        <div style="font-size:7.5px;color:#888;margin-top:2px;">
            @if($partyMob)&#9742; {{ $partyMob }} &nbsp;@endif
            @if($partyBank)&#127983; {{ $partyBank }}@endif
        </div>
        @endif
    </div>

    {{-- Amount box --}}
    <div class="amt-box">
        <div class="amt-lbl">{{ $transaction->currency }} Amount</div>
        <div class="amt-val">{{ number_format($transaction->net_amount, 2) }}</div>
        @if($transaction->fee > 0)
        <div class="amt-meta">
            Gross Amount: {{ number_format($transaction->amount, 2) }}
            &nbsp;|&nbsp; Fee Deducted: {{ number_format($transaction->fee, 2) }}
        </div>
        @endif
    </div>

    {{-- Amount in Words --}}
    <div class="words-row">
        <span style="font-size:7px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#777;">Amount in Words: </span>
        <span style="font-size:9px;font-weight:600;color:#111;">{{ $amountWords }}</span>
    </div>

    {{-- Particulars --}}
    <div class="f-row" style="min-height:28px;">
        <div class="lbl">Particulars / Narration</div>
        <div class="val">{{ $transaction->description ?: '—' }}</div>
    </div>

    {{-- Info table --}}
    <table class="info-tbl">
        <tr>
            <td class="k">Category</td>
            <td>{{ ucfirst(str_replace('_', ' ', $transaction->category ?? '—')) }}</td>
            <td class="k">Payment Mode</td>
            <td>{{ ucfirst(str_replace('_', ' ', $transaction->payment_method ?? 'Cash')) }}</td>
        </tr>
        <tr>
            <td class="k">Status</td>
            <td>{{ ucfirst($transaction->status) }}</td>
            <td class="k">Reference</td>
            <td>{{ $transaction->reference ?? '—' }}</td>
        </tr>
        @if($transaction->user)
        <tr>
            <td class="k">Account</td>
            <td colspan="3">{{ $transaction->user->name }} &nbsp;({{ $transaction->user->email }})</td>
        </tr>
        @endif
    </table>

    {{-- Signatures --}}
    <div class="sig-wrap">
        <div class="sig-cell"><div class="sig-line">Prepared By</div></div>
        <div class="sig-cell"><div class="sig-line">Checked By</div></div>
        <div class="sig-cell">
            <div class="sig-line">{{ $transaction->type === 'credit' ? "Receiver's Signature" : "Payee's Signature" }}</div>
        </div>
    </div>

    <div class="footer">
        System-generated voucher &nbsp;|&nbsp; AS Dairy Dashboard &nbsp;|&nbsp;
        Printed on {{ now()->format('d M Y, H:i') }}
    </div>
</div>
</body>
</html>
