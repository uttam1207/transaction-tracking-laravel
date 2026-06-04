<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cash Voucher — {{ $transaction->transaction_id }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DejaVu Sans',Arial,sans-serif; font-size:9px; color:#111; background:#fff; }

.page { border:1.5px solid #aaa; }

/* ── Company Header ──────────────────────────── */
.co-hdr        { display:table; width:100%; }
.co-left       { display:table-cell; width:62%; padding:10px 12px; vertical-align:top; border-right:1px solid #ccc; border-bottom:1px solid #ccc; }
.co-right      { display:table-cell; width:38%; padding:10px 12px; vertical-align:top; border-bottom:1px solid #ccc; }
.co-name       { font-size:15px; font-weight:800; color:#111; margin-bottom:7px; }
.co-row        { display:table; width:100%; margin-bottom:3px; }
.co-lbl        { display:table-cell; width:28%; font-size:7.5px; color:#555; white-space:nowrap; }
.co-val        { display:table-cell; font-size:8px; color:#111; border-bottom:1px solid #ddd; padding-bottom:1px; }
.logo-area     { font-size:16px; font-weight:900; color:#6b8060; text-align:right; margin-bottom:10px; letter-spacing:-.5px; }
.logo-sub      { font-size:6.5px; color:#8a9a80; text-align:right; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
.meta-row      { display:table; width:100%; margin-bottom:3px; }
.meta-lbl      { display:table-cell; width:38%; font-size:7.5px; color:#555; white-space:nowrap; }
.meta-val      { display:table-cell; font-size:8px; color:#111; border-bottom:1px solid #ddd; padding-bottom:1px; }

/* ── Voucher Title Bar ───────────────────────── */
.v-bar         { background:#6b8060; text-align:center; padding:7px 0; }
.v-bar-text    { font-size:15px; font-weight:800; color:#fff; letter-spacing:.5px; }

/* ── Items Table ─────────────────────────────── */
.items-tbl     { width:100%; border-collapse:collapse; }
.items-tbl th  { background:#6b8060; color:#fff; padding:5px 8px; font-size:8px; font-weight:700;
                 text-align:left; border:1px solid #5a6b50; }
.items-tbl th.r { text-align:right; }
.items-tbl td  { padding:5px 8px; border:1px solid #ddd; font-size:8.5px; vertical-align:top; }
.items-tbl td.r { text-align:right; }
.items-tbl td.c { text-align:center; }
.items-tbl .blank-row td { height:14px; }
.items-tbl .total-row td  { background:#f3f5f0; font-weight:700; border-top:1.5px solid #999; }

/* ── Bottom (words + totals) ─────────────────── */
.bottom        { display:table; width:100%; border-top:1px solid #ccc; }
.words-cell    { display:table-cell; width:55%; padding:10px 12px; vertical-align:top; border-right:1px solid #ccc; }
.totals-cell   { display:table-cell; width:45%; vertical-align:top; }
.totals-tbl    { width:100%; border-collapse:collapse; }
.totals-tbl td { padding:5px 10px; border-bottom:1px solid #eee; font-size:8.5px; }
.totals-tbl .th-row td { background:#6b8060; color:#fff; font-weight:700; font-size:8.5px; border-bottom:none; }
.totals-tbl .r { text-align:right; font-weight:700; }

/* ── Footer (terms + sign) ───────────────────── */
.footer        { display:table; width:100%; border-top:1px solid #ccc; min-height:90px; }
.terms-cell    { display:table-cell; width:55%; padding:10px 12px; vertical-align:top; border-right:1px solid #ccc; }
.sign-cell     { display:table-cell; width:45%; padding:10px 12px; vertical-align:bottom; text-align:center; }
.sign-line     { border-top:1.5px solid #111; padding-top:4px; font-size:7.5px; font-weight:700;
                 text-transform:uppercase; letter-spacing:.5px; margin-top:44px; }

/* ── Bottom bar ──────────────────────────────── */
.btm-bar       { background:#6b8060; padding:3px 10px; }
.btm-bar-txt   { font-size:7px; color:rgba(255,255,255,.7); text-align:right; }
</style>
</head>
<body>
@php
    $isPaid    = $transaction->status === 'success';
    $totalAmt  = number_format($transaction->net_amount, 2);
    $paidAmt   = $isPaid ? number_format($transaction->net_amount, 2) : '0.00';
    $balAmt    = $isPaid ? '0.00' : number_format($transaction->net_amount, 2);
    $partyName = $transaction->type === 'credit'
        ? ($transaction->sender_name ?? '—')
        : ($transaction->receiver_name ?? '—');
@endphp
<div class="page">

    {{-- ── Company Header ── --}}
    <div class="co-hdr">
        <div class="co-left">
            <div class="co-name">AS Dairy Dashboard</div>
            <div class="co-row"><span class="co-lbl">Phone no. :</span><span class="co-val">&nbsp;</span></div>
            <div class="co-row"><span class="co-lbl">Email :</span><span class="co-val">&nbsp;</span></div>
            <div class="co-row"><span class="co-lbl">GSTIN :</span><span class="co-val">&nbsp;</span></div>
            <div class="co-row"><span class="co-lbl">State :</span><span class="co-val">&nbsp;</span></div>
            <div class="co-row"><span class="co-lbl">Address 1 :</span><span class="co-val">&nbsp;</span></div>
            <div class="co-row"><span class="co-lbl">Address 2 :</span><span class="co-val">&nbsp;</span></div>
        </div>
        <div class="co-right">
            <div class="logo-area">AS Dairy</div>
            <div class="logo-sub">Management System</div>
            <div class="meta-row"><span class="meta-lbl">Date :</span><span class="meta-val">{{ \Carbon\Carbon::parse($transaction->processed_at ?? $transaction->created_at)->format('d/m/Y') }}</span></div>
            <div class="meta-row"><span class="meta-lbl">Voucher No. :</span><span class="meta-val">{{ $transaction->transaction_id }}</span></div>
            <div class="meta-row"><span class="meta-lbl">Payable to :</span><span class="meta-val">{{ $partyName }}</span></div>
        </div>
    </div>

    {{-- ── Title Bar ── --}}
    <div class="v-bar">
        <span class="v-bar-text">Cash Voucher</span>
    </div>

    {{-- ── Items Table ── --}}
    <table class="items-tbl">
        <thead>
            <tr>
                <th style="width:8%;">Serial no.</th>
                <th style="width:46%;">Particulars</th>
                <th style="width:28%;">Payment Mode</th>
                <th class="r" style="width:18%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="c">1</td>
                <td>{{ $transaction->description ?: ucfirst(str_replace('_',' ',$transaction->category ?? 'Transaction')) }}</td>
                <td>{{ ucfirst(str_replace('_',' ',$transaction->payment_method ?? 'Cash')) }}</td>
                <td class="r">{{ number_format($transaction->amount, 2) }}</td>
            </tr>
            @if($transaction->fee > 0)
            <tr>
                <td class="c">2</td>
                <td>Service Fee / Charge</td>
                <td>—</td>
                <td class="r" style="color:#dc2626;">- {{ number_format($transaction->fee, 2) }}</td>
            </tr>
            @endif
            {{-- Blank padding rows --}}
            @for($i = ($transaction->fee > 0 ? 3 : 2); $i <= 7; $i++)
            <tr class="blank-row"><td class="c">{{ $i }}</td><td></td><td></td><td></td></tr>
            @endfor
            {{-- Total row --}}
            <tr class="total-row">
                <td colspan="3" style="text-align:right;font-size:8.5px;padding-right:12px;">Total</td>
                <td class="r">{{ $totalAmt }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── Bottom: Words + Totals ── --}}
    <div class="bottom">
        <div class="words-cell">
            <div style="font-size:7.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#666;margin-bottom:5px;">Amount in Words</div>
            <div style="font-size:9px;font-weight:600;color:#111;">{{ $amountWords }}</div>
        </div>
        <div class="totals-cell">
            <table class="totals-tbl">
                <tr class="th-row">
                    <td colspan="2"><b>Total Amount</b></td>
                    <td class="r"><b>{{ $totalAmt }}</b></td>
                </tr>
                <tr>
                    <td colspan="2">Paid</td>
                    <td class="r">{{ $paidAmt }}</td>
                </tr>
                <tr>
                    <td colspan="2">Balance</td>
                    <td class="r">{{ $balAmt }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ── Footer: Terms + Signature ── --}}
    <div class="footer">
        <div class="terms-cell">
            <div style="font-size:7.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#666;margin-bottom:5px;">Terms &amp; Conditions</div>
        </div>
        <div class="sign-cell">
            <div class="sign-line">Authorized Signatory</div>
        </div>
    </div>

    {{-- ── Bottom Bar ── --}}
    <div class="btm-bar">
        <div class="btm-bar-txt">AS Dairy Dashboard &nbsp;|&nbsp; {{ now()->format('d M Y H:i') }}</div>
    </div>

</div>
</body>
</html>
