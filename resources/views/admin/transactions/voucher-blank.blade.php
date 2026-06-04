<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cash Voucher — Blank Template</title>
<style>
@page { margin: 6mm; }
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
.co-val        { display:table-cell; font-size:8px; color:#111; border-bottom:1px dotted #aaa; padding-bottom:1px; }
.logo-area     { font-size:16px; font-weight:900; color:#6b8060; text-align:right; margin-bottom:10px; letter-spacing:-.5px; }
.logo-sub      { font-size:6.5px; color:#8a9a80; text-align:right; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
.meta-row      { display:table; width:100%; margin-bottom:3px; }
.meta-lbl      { display:table-cell; width:38%; font-size:7.5px; color:#555; white-space:nowrap; }
.meta-val      { display:table-cell; font-size:8px; color:#111; border-bottom:1px dotted #aaa; padding-bottom:1px; min-width:80px; }

/* ── Voucher Title Bar ───────────────────────── */
.v-bar         { background:#6b8060; text-align:center; padding:7px 0; }
.v-bar-text    { font-size:15px; font-weight:800; color:#fff; letter-spacing:.5px; }

/* ── Items Table ─────────────────────────────── */
.items-tbl     { width:100%; border-collapse:collapse; }
.items-tbl th  { background:#6b8060; color:#fff; padding:5px 8px; font-size:8px; font-weight:700;
                 text-align:left; border:1px solid #5a6b50; }
.items-tbl th.r { text-align:right; }
.items-tbl td  { padding:5px 8px; border:1px solid #ddd; font-size:8.5px; height:18px; vertical-align:middle; }
.items-tbl td.r { text-align:right; }
.items-tbl td.c { text-align:center; }
.items-tbl .total-row td { background:#f3f5f0; font-weight:700; border-top:1.5px solid #999; }

/* ── Bottom ──────────────────────────────────── */
.bottom        { display:table; width:100%; border-top:1px solid #ccc; }
.words-cell    { display:table-cell; width:55%; padding:10px 12px; vertical-align:top; border-right:1px solid #ccc; }
.totals-cell   { display:table-cell; width:45%; vertical-align:top; }
.totals-tbl    { width:100%; border-collapse:collapse; }
.totals-tbl td { padding:5px 10px; border-bottom:1px solid #eee; font-size:8.5px; }
.totals-tbl .th-row td { background:#6b8060; color:#fff; font-weight:700; font-size:8.5px; border-bottom:none; }
.totals-tbl .r { text-align:right; }
.blank-line    { border-bottom:1px dotted #999; min-width:60px; display:inline-block; }

/* ── Footer ──────────────────────────────────── */
.footer        { display:table; width:100%; border-top:1px solid #ccc; min-height:80px; }
.terms-cell    { display:table-cell; width:28%; padding:10px 12px; vertical-align:top; border-right:1px solid #ccc; }
.sign3-cell    { display:table-cell; width:24%; padding:10px 8px; vertical-align:bottom; text-align:center; }
.sign-line     { border-top:1.5px solid #111; padding-top:4px; font-size:7.5px; font-weight:700;
                 text-transform:uppercase; letter-spacing:.5px; margin-top:50px; }

/* ── Bottom bar ──────────────────────────────── */
.btm-bar       { background:#6b8060; padding:3px 10px; }
.btm-bar-txt   { font-size:7px; color:rgba(255,255,255,.7); text-align:right; }

</style>
</head>
<body>

{{-- ════════════════ ORIGINAL COPY ════════════════ --}}
<div class="page">

    <div class="co-hdr">
        <div class="co-left">
            <div class="co-name">AS Dairy</div>
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
<div class="meta-row"><span class="meta-lbl">Date :</span><span class="meta-val">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
            <div class="meta-row"><span class="meta-lbl">Voucher No. :</span><span class="meta-val">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
            <div class="meta-row"><span class="meta-lbl">Payable to :</span><span class="meta-val">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
        </div>
    </div>

    <div class="v-bar"><span class="v-bar-text">Cash Voucher</span></div>

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
            @for($i = 1; $i <= 7; $i++)
            <tr><td class="c">{{ $i }}</td><td></td><td></td><td></td></tr>
            @endfor
            <tr class="total-row">
                <td colspan="3" style="text-align:right;font-size:8.5px;padding-right:12px;">Total</td>
                <td class="r"></td>
            </tr>
        </tbody>
    </table>

    <div class="bottom">
        <div class="words-cell">
            <div style="font-size:7.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#666;margin-bottom:6px;">Amount in Words</div>
            <div style="border-bottom:1px dotted #aaa;min-height:14px;margin-bottom:5px;">&nbsp;</div>
            <div style="border-bottom:1px dotted #aaa;min-height:14px;">&nbsp;</div>
        </div>
        <div class="totals-cell">
            <table class="totals-tbl">
                <tr class="th-row"><td colspan="2"><b>Total Amount</b></td><td class="r"></td></tr>
                <tr><td colspan="2">Paid</td><td class="r"></td></tr>
                <tr><td colspan="2">Balance</td><td class="r"></td></tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <div class="terms-cell">
            <div style="font-size:7.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#666;margin-bottom:5px;">Terms &amp; Conditions</div>
        </div>
        <div class="sign3-cell" style="border-right:1px solid #ccc;">
            <div class="sign-line">Prepared By</div>
        </div>
        <div class="sign3-cell" style="border-right:1px solid #ccc;">
            <div class="sign-line">Payment By</div>
        </div>
        <div class="sign3-cell">
            <div class="sign-line">Approved By</div>
        </div>
    </div>

    <div class="btm-bar"><div class="btm-bar-txt">AS Dairy &nbsp;|&nbsp; Cash Voucher</div></div>
</div>

</body>
</html>
