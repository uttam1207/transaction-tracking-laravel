<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cash Voucher — Blank Template</title>
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

/* ── Dotted blank lines ──────────────────────── */
.blank { display:block; border-bottom:1.5px dotted #999; min-height:14px; width:100%; }
.blank-short { display:inline-block; border-bottom:1.5px dotted #999; min-height:14px; }
.lbl-xs { font-size:6.5px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#777; display:block; margin-bottom:3px; }

/* ── Meta row ────────────────────────────────── */
.meta { display:table; width:100%; margin-bottom:10px; }
.meta-l { display:table-cell; vertical-align:bottom; width:40%; }
.meta-c { display:table-cell; vertical-align:middle; text-align:center; width:30%; }
.meta-r { display:table-cell; vertical-align:bottom; width:30%; text-align:right; }

/* ── Type checkboxes ─────────────────────────── */
.type-opts { font-size:8px; color:#333; }
.type-opts span { margin-right:8px; }

/* ── Field rows ──────────────────────────────── */
.f-row { margin-bottom:8px; }
.f-row .lbl { font-size:6.5px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#777; margin-bottom:3px; }
.f-row .val { border-bottom:1px solid #aaa; min-height:16px; width:100%; }

/* ── Amount box ──────────────────────────────── */
.amt-box { border:2px solid #111; padding:10px 12px; text-align:center; margin:8px 0; }
.amt-lbl { font-size:7px; text-transform:uppercase; letter-spacing:.5px; color:#666; margin-bottom:6px; }
.amt-line { border-bottom:2px dotted #555; min-height:26px; margin:0 20px; }
.amt-sym  { font-size:16px; font-weight:900; float:left; color:#111; margin-top:4px; margin-right:4px; }

/* ── Words row ───────────────────────────────── */
.words-row { border:1px solid #bbb; padding:6px 8px; margin-bottom:8px; }
.words-line { border-bottom:1px dotted #aaa; min-height:14px; margin-top:4px; }

/* ── Info table ──────────────────────────────── */
.info-tbl { width:100%; border-collapse:collapse; margin-bottom:10px; }
.info-tbl td { padding:5px 7px; border:1px solid #ddd; font-size:8.5px; }
.info-tbl .k  { font-size:7px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#777; width:22%; }
.info-tbl .blank-td { border-bottom:1px dotted #aaa; min-height:14px; }

/* ── Narration area ──────────────────────────── */
.narr-box { border:1px solid #bbb; padding:6px 8px; min-height:36px; margin-bottom:10px; }
.narr-line { border-bottom:1px dotted #ccc; min-height:14px; margin-bottom:4px; }

/* ── Signature section ───────────────────────── */
.sig-wrap { display:table; width:100%; margin-top:20px; }
.sig-cell { display:table-cell; text-align:center; padding:0 6px; }
.sig-line { border-top:1.5px solid #111; margin-top:32px; padding-top:4px; font-size:7px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#555; }

/* ── Footer ──────────────────────────────────── */
.footer { margin-top:10px; border-top:1px dashed #ccc; padding-top:5px; text-align:center; font-size:6.5px; color:#aaa; }

/* ── Second copy divider ─────────────────────── */
.copy-divider { border-top:2px dashed #999; margin:18px 0; text-align:center; }
.copy-divider span { font-size:7px; color:#aaa; background:#fff; padding:0 8px; position:relative; top:-5px; }

/* ── Watermark ───────────────────────────────── */
.wm { position:fixed; bottom:80px; right:30px; font-size:52px; font-weight:900;
      color:rgba(0,0,0,.04); transform:rotate(-28deg); letter-spacing:4px; text-transform:uppercase; }
</style>
</head>
<body>
<div class="wm">VOUCHER</div>

{{-- ═══ ORIGINAL COPY ════════════════════════════════════════════════ --}}
<div class="page">
    {{-- Header --}}
    <div class="hdr">
        <div class="hdr-l">
            <div class="co-name">AS Dairy Dashboard</div>
            <div class="co-sub">Dairy Management System</div>
        </div>
        <div class="hdr-r">
            <div class="v-title">Cash Voucher</div>
            <div class="v-sub">Original Copy</div>
        </div>
    </div>

    {{-- Meta row --}}
    <div class="meta">
        <div class="meta-l">
            <span class="lbl-xs">Voucher No.</span>
            <span class="blank-short" style="width:130px;">&nbsp;</span>
        </div>
        <div class="meta-c">
            <div class="lbl-xs" style="text-align:center;">Voucher Type</div>
            <div class="type-opts" style="text-align:center;">
                <span>&#9633; Payment</span>
                <span>&#9633; Receipt</span>
            </div>
        </div>
        <div class="meta-r">
            <span class="lbl-xs">Date</span>
            <span class="blank-short" style="width:90px;">&nbsp;</span>
        </div>
    </div>

    {{-- Received From / Paid To --}}
    <div class="f-row">
        <div class="lbl">Received From / Paid To</div>
        <div class="val">&nbsp;</div>
    </div>
    <div style="display:table;width:100%;margin-bottom:8px;">
        <div style="display:table-cell;width:50%;padding-right:8px;">
            <span class="lbl-xs">Company / Firm Name</span>
            <span class="blank">&nbsp;</span>
        </div>
        <div style="display:table-cell;width:50%;padding-left:8px;">
            <span class="lbl-xs">Mobile / Contact</span>
            <span class="blank">&nbsp;</span>
        </div>
    </div>

    {{-- Amount box --}}
    <div class="amt-box">
        <div class="amt-lbl">Amount (in Figures)</div>
        <div style="position:relative;">
            <span class="amt-sym">₹</span>
            <div class="amt-line">&nbsp;</div>
        </div>
    </div>

    {{-- Amount in words --}}
    <div class="words-row">
        <span class="lbl-xs">Amount in Words (Rupees)</span>
        <div class="words-line">&nbsp;</div>
        <div class="words-line" style="margin-top:2px;">&nbsp;</div>
    </div>

    {{-- Particulars / Narration --}}
    <div class="lbl-xs" style="margin-bottom:3px;">Particulars / Narration</div>
    <div class="narr-box">
        <div class="narr-line">&nbsp;</div>
        <div class="narr-line">&nbsp;</div>
        <div class="narr-line" style="border:none;">&nbsp;</div>
    </div>

    {{-- Info table --}}
    <table class="info-tbl">
        <tr>
            <td class="k">Category</td>
            <td><span class="blank">&nbsp;</span></td>
            <td class="k">Payment Mode</td>
            <td>
                <span style="font-size:7.5px;">&#9633; Cash &nbsp; &#9633; Cheque &nbsp; &#9633; Online &nbsp; &#9633; UPI</span>
            </td>
        </tr>
        <tr>
            <td class="k">Reference / Cheque No.</td>
            <td><span class="blank">&nbsp;</span></td>
            <td class="k">Bank / Branch</td>
            <td><span class="blank">&nbsp;</span></td>
        </tr>
    </table>

    {{-- Signatures --}}
    <div class="sig-wrap">
        <div class="sig-cell"><div class="sig-line">Prepared By</div></div>
        <div class="sig-cell"><div class="sig-line">Checked / Approved By</div></div>
        <div class="sig-cell"><div class="sig-line">Receiver's / Payee's Sign</div></div>
    </div>

    <div class="footer">
        AS Dairy Dashboard &nbsp;|&nbsp; Cash Voucher Template &nbsp;|&nbsp; For internal use only
    </div>
</div>

{{-- ═══ DUPLICATE COPY (same on one page) ════════════════════════════ --}}
<div class="copy-divider">
    <span>— CUT HERE — DUPLICATE COPY —</span>
</div>

<div class="page">
    <div class="hdr">
        <div class="hdr-l">
            <div class="co-name">AS Dairy Dashboard</div>
            <div class="co-sub">Dairy Management System</div>
        </div>
        <div class="hdr-r">
            <div class="v-title">Cash Voucher</div>
            <div class="v-sub">Duplicate Copy</div>
        </div>
    </div>

    <div class="meta">
        <div class="meta-l">
            <span class="lbl-xs">Voucher No.</span>
            <span class="blank-short" style="width:130px;">&nbsp;</span>
        </div>
        <div class="meta-c">
            <div class="lbl-xs" style="text-align:center;">Voucher Type</div>
            <div class="type-opts" style="text-align:center;">
                <span>&#9633; Payment</span>
                <span>&#9633; Receipt</span>
            </div>
        </div>
        <div class="meta-r">
            <span class="lbl-xs">Date</span>
            <span class="blank-short" style="width:90px;">&nbsp;</span>
        </div>
    </div>

    <div class="f-row">
        <div class="lbl">Received From / Paid To</div>
        <div class="val">&nbsp;</div>
    </div>
    <div style="display:table;width:100%;margin-bottom:8px;">
        <div style="display:table-cell;width:50%;padding-right:8px;">
            <span class="lbl-xs">Company / Firm Name</span>
            <span class="blank">&nbsp;</span>
        </div>
        <div style="display:table-cell;width:50%;padding-left:8px;">
            <span class="lbl-xs">Mobile / Contact</span>
            <span class="blank">&nbsp;</span>
        </div>
    </div>

    <div class="amt-box">
        <div class="amt-lbl">Amount (in Figures)</div>
        <div style="position:relative;">
            <span class="amt-sym">₹</span>
            <div class="amt-line">&nbsp;</div>
        </div>
    </div>

    <div class="words-row">
        <span class="lbl-xs">Amount in Words (Rupees)</span>
        <div class="words-line">&nbsp;</div>
        <div class="words-line" style="margin-top:2px;">&nbsp;</div>
    </div>

    <div class="lbl-xs" style="margin-bottom:3px;">Particulars / Narration</div>
    <div class="narr-box">
        <div class="narr-line">&nbsp;</div>
        <div class="narr-line">&nbsp;</div>
        <div class="narr-line" style="border:none;">&nbsp;</div>
    </div>

    <table class="info-tbl">
        <tr>
            <td class="k">Category</td>
            <td><span class="blank">&nbsp;</span></td>
            <td class="k">Payment Mode</td>
            <td>
                <span style="font-size:7.5px;">&#9633; Cash &nbsp; &#9633; Cheque &nbsp; &#9633; Online &nbsp; &#9633; UPI</span>
            </td>
        </tr>
        <tr>
            <td class="k">Reference / Cheque No.</td>
            <td><span class="blank">&nbsp;</span></td>
            <td class="k">Bank / Branch</td>
            <td><span class="blank">&nbsp;</span></td>
        </tr>
    </table>

    <div class="sig-wrap">
        <div class="sig-cell"><div class="sig-line">Prepared By</div></div>
        <div class="sig-cell"><div class="sig-line">Checked / Approved By</div></div>
        <div class="sig-cell"><div class="sig-line">Receiver's / Payee's Sign</div></div>
    </div>

    <div class="footer">
        AS Dairy Dashboard &nbsp;|&nbsp; Cash Voucher Template &nbsp;|&nbsp; For internal use only
    </div>
</div>

</body>
</html>
