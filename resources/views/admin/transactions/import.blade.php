@extends('layouts.app')
@section('title', 'Import Transactions')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transactions</a></li>
    <li class="breadcrumb-item active">Import Transactions</li>
@endsection

@push('styles')
<style>
.import-hero {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
    border-radius: 16px;
    padding: 22px 28px;
    margin-bottom: 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.import-hero::before {
    content:'';position:absolute;top:-40px;right:-30px;
    width:160px;height:160px;background:rgba(255,255,255,.06);border-radius:50%;
}
.upload-zone {
    border: 2.5px dashed #6ee7b7;
    border-radius: 14px;
    padding: 36px 24px;
    text-align: center;
    background: #f0fdf4;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    position: relative;
}
.upload-zone:hover, .upload-zone.drag-over {
    border-color: #059669;
    background: #d1fae5;
}
.upload-zone input[type=file] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
    z-index: 2;
}
.file-chosen {
    display: none;
    align-items: center;
    gap: 12px;
    background: #d1fae5;
    border: 1.5px solid #6ee7b7;
    border-radius: 10px;
    padding: 12px 16px;
    margin-top: 12px;
}
.col-badge {
    display: inline-block;
    background: #1e1b4b;
    color: #a5b4fc;
    font-family: monospace;
    font-size: .7rem;
    padding: 2px 7px;
    border-radius: 5px;
    margin: 2px 2px;
    white-space: nowrap;
}
.col-badge.required {
    background: #450a0a;
    color: #fca5a5;
}
.col-badge.optional {
    background: #1e293b;
    color: #94a3b8;
}
.option-pill {
    display: inline-block;
    background: #f3f4f6;
    color: #374151;
    font-size: .72rem;
    padding: 1px 7px;
    border-radius: 20px;
    margin: 1px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
}
.section-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    margin-bottom: 20px;
    overflow: hidden;
}
.section-hdr {
    padding: 13px 20px;
    border-bottom: 1px solid #f3f4f6;
    background: #f9fafb;
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-hdr i { font-size: .9rem; color: #4f46e5; }
.section-body { padding: 20px; }
</style>
@endpush

@section('content')

<div class="import-hero">
    <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h5 class="mb-1 fw-bold" style="font-weight:800;letter-spacing:-.3px;">Import Transactions</h5>
            <p class="mb-0" style="opacity:.7;font-size:.82rem;">Upload a CSV or Excel file to bulk-create transactions. Download the template to get started.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.transactions.sample-csv') }}"
               style="display:inline-flex;align-items:center;gap:6px;font-size:.82rem;font-weight:700;
                      padding:8px 18px;border-radius:9px;border:1.5px solid rgba(255,255,255,.5);
                      background:rgba(255,255,255,.15);color:#fff;text-decoration:none;backdrop-filter:blur(4px);"
               onmouseover="this.style.background='rgba(255,255,255,.25)'"
               onmouseout="this.style.background='rgba(255,255,255,.15)'">
                <i class="bi bi-download"></i>Download Template (.xlsx)
            </a>
            <a href="{{ route('admin.transactions.index') }}"
               style="display:inline-flex;align-items:center;gap:6px;font-size:.82rem;font-weight:600;
                      padding:8px 16px;border-radius:9px;border:1.5px solid rgba(255,255,255,.3);
                      background:transparent;color:rgba(255,255,255,.8);text-decoration:none;"
               onmouseover="this.style.background='rgba(255,255,255,.1)'"
               onmouseout="this.style.background='transparent'">
                <i class="bi bi-arrow-left"></i>Back
            </a>
        </div>
    </div>
</div>

{{-- Import Result --}}
@if(session('import_result'))
@php $result = session('import_result'); @endphp
<div class="section-card mb-3">
    <div class="section-hdr" style="background:{{ $result['imported'] > 0 ? '#f0fdf4' : '#fff' }};border-bottom-color:{{ $result['imported'] > 0 ? '#bbf7d0' : '#f3f4f6' }};">
        <i class="bi bi-check2-circle" style="color:#16a34a;"></i>
        <span style="color:#15803d;">Import Complete</span>
    </div>
    <div class="section-body">
        <div class="d-flex gap-3 flex-wrap mb-3">
            <div style="background:#dcfce7;border:1.5px solid #86efac;border-radius:10px;padding:12px 20px;text-align:center;min-width:110px;">
                <div style="font-size:1.8rem;font-weight:900;color:#15803d;line-height:1;">{{ $result['imported'] }}</div>
                <div style="font-size:.75rem;font-weight:600;color:#166534;margin-top:2px;">Imported</div>
            </div>
            @if(count($result['errors']))
            <div style="background:#fee2e2;border:1.5px solid #fca5a5;border-radius:10px;padding:12px 20px;text-align:center;min-width:110px;">
                <div style="font-size:1.8rem;font-weight:900;color:#dc2626;line-height:1;">{{ count($result['errors']) }}</div>
                <div style="font-size:.75rem;font-weight:600;color:#991b1b;margin-top:2px;">Failed Rows</div>
            </div>
            @endif
        </div>

        @if(count($result['errors']))
        <div style="background:#fff7f7;border:1.5px solid #fecaca;border-radius:10px;padding:14px 16px;">
            <div style="font-size:.8rem;font-weight:700;color:#dc2626;margin-bottom:8px;">
                <i class="bi bi-exclamation-triangle me-1"></i>Rows with errors (skipped):
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size:.8rem;">
                    <thead>
                        <tr style="background:#fee2e2;">
                            <th style="width:70px;border:none;padding:5px 8px;color:#991b1b;">Row #</th>
                            <th style="border:none;padding:5px 8px;color:#991b1b;">Issues</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['errors'] as $err)
                        <tr>
                            <td style="padding:5px 8px;font-weight:700;color:#dc2626;">{{ $err['row'] }}</td>
                            <td style="padding:5px 8px;color:#374151;">{{ implode('; ', $err['errors']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($result['imported'] > 0)
        <div class="mt-3">
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-primary-grad px-4">
                <i class="bi bi-eye me-1"></i>View Transactions
            </a>
        </div>
        @endif
    </div>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">

        {{-- Upload Form --}}
        <div class="section-card">
            <div class="section-hdr"><i class="bi bi-upload"></i>Upload File</div>
            <div class="section-body">
                <form method="POST" action="{{ route('admin.transactions.import.process') }}"
                      enctype="multipart/form-data" id="importForm">
                    @csrf

                    <div class="upload-zone" id="uploadZone">
                        <input type="file" name="csv_file" id="csvFile" accept=".csv,.txt,.xlsx,.xls" required>
                        <i class="bi bi-file-earmark-spreadsheet" style="font-size:2.4rem;color:#059669;display:block;margin-bottom:8px;"></i>
                        <div style="font-size:.9rem;font-weight:700;color:#065f46;margin-bottom:4px;">Click or drag your file here</div>
                        <div style="font-size:.78rem;color:#6b7280;">Supports .xlsx, .xls, .csv &nbsp;·&nbsp; Max 5 MB</div>
                    </div>

                    <div class="file-chosen" id="fileChosen">
                        <i class="bi bi-file-earmark-spreadsheet" style="font-size:1.4rem;color:#059669;flex-shrink:0;"></i>
                        <div style="flex:1;min-width:0;">
                            <div id="chosenFileName" style="font-size:.85rem;font-weight:700;color:#065f46;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></div>
                            <div id="chosenFileSize" style="font-size:.72rem;color:#6b7280;"></div>
                        </div>
                        <button type="button" onclick="clearFile()"
                            style="border:none;background:none;color:#dc2626;font-size:.8rem;cursor:pointer;flex-shrink:0;font-weight:600;">
                            <i class="bi bi-x-circle-fill"></i> Remove
                        </button>
                    </div>

                    @error('csv_file')
                    <div class="alert alert-danger mt-2 py-2 px-3" style="font-size:.82rem;border-radius:9px;">
                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                    </div>
                    @enderror

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" id="submitBtn" disabled
                            class="btn btn-sm btn-primary-grad px-5"
                            style="opacity:.5;cursor:not-allowed;height:40px;font-size:.88rem;">
                            <i class="bi bi-cloud-upload me-1"></i>Import Now
                        </button>
                        <a href="{{ route('admin.transactions.sample-csv') }}"
                           class="btn btn-sm px-4"
                           style="border:1.5px solid #6ee7b7;background:#f0fdf4;color:#065f46;border-radius:9px;height:40px;font-size:.88rem;display:inline-flex;align-items:center;gap:5px;font-weight:600;text-decoration:none;">
                            <i class="bi bi-download"></i>Template (.xlsx)
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Rules --}}
        <div class="section-card">
            <div class="section-hdr"><i class="bi bi-info-circle"></i>Import Rules</div>
            <div class="section-body">
                <ul style="font-size:.83rem;color:#374151;line-height:2;padding-left:18px;margin:0;">
                    <li>Row 1 must be the <strong>header row</strong> with exact column names</li>
                    <li>Use the <strong>.xlsx template</strong> for built-in dropdown validation on required fields</li>
                    <li><span style="color:#dc2626;font-weight:700;">type</span>, <span style="color:#dc2626;font-weight:700;">amount</span>, <span style="color:#dc2626;font-weight:700;">sender_name</span>, <span style="color:#dc2626;font-weight:700;">receiver_name</span> are required</li>
                    <li>Columns with invalid dropdown values fall back to the default</li>
                    <li>Missing optional columns are saved as empty</li>
                    <li>Rows with validation errors are <strong>skipped</strong> and reported after import</li>
                    <li><code>processed_at</code> format: <code>YYYY-MM-DD HH:MM:SS</code></li>
                    <li>Max file size: <strong>5 MB</strong></li>
                </ul>
            </div>
        </div>

    </div>

    <div class="col-lg-5">

        {{-- Column Reference --}}
        <div class="section-card">
            <div class="section-hdr"><i class="bi bi-table"></i>Column Reference</div>
            <div class="section-body" style="font-size:.8rem;">

                <div class="mb-3">
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:6px;">Transaction Fields</div>
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f9fafb;">
                                <th style="padding:5px 8px;font-size:.7rem;color:#6b7280;border:1px solid #f3f4f6;width:38%;">Column</th>
                                <th style="padding:5px 8px;font-size:.7rem;color:#6b7280;border:1px solid #f3f4f6;">Allowed Values</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge required">type *</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;">
                                    <span class="option-pill">debit</span>
                                    <span class="option-pill">credit</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge required">category *</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;">
                                    @foreach(['transfer','payment','withdrawal','deposit','refund','purchase','salary','investment','loan','other'] as $o)
                                    <span class="option-pill">{{ $o }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge required">amount *</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;color:#6b7280;">Positive number e.g. <code>5000.00</code></td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge optional">currency</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="option-pill">INR</span></td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge optional">fee</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;color:#6b7280;">Number, default <code>0.00</code></td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge required">payment_method *</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;">
                                    @foreach(['bank_transfer','credit_card','debit_card','cash','mobile_money','wire_transfer','crypto'] as $o)
                                    <span class="option-pill">{{ $o }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge optional">status</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;">
                                    @foreach(['pending','processing','success','failed'] as $o)
                                    <span class="option-pill">{{ $o }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge optional">processed_at</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;color:#6b7280;"><code>2026-06-04 10:00:00</code></td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge optional">reference</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;color:#6b7280;">Free text</td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge optional">description</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;color:#6b7280;">Free text</td>
                            </tr>
                            <tr>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;"><span class="col-badge optional">country</span></td>
                                <td style="padding:5px 8px;border:1px solid #f3f4f6;color:#6b7280;">e.g. <code>IN</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mb-2">
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:6px;">Sender &amp; Receiver</div>
                    <div style="display:flex;flex-wrap:wrap;gap:3px;">
                        <span class="col-badge required">sender_name *</span>
                        <span class="col-badge optional">sender_mobile</span>
                        <span class="col-badge optional">sender_company</span>
                        <span class="col-badge optional">sender_account</span>
                        <span class="col-badge optional">sender_bank</span>
                        <span class="col-badge required">receiver_name *</span>
                        <span class="col-badge optional">receiver_mobile</span>
                        <span class="col-badge optional">receiver_company</span>
                        <span class="col-badge optional">receiver_address</span>
                        <span class="col-badge optional">receiver_account</span>
                        <span class="col-badge optional">receiver_bank</span>
                        <span class="col-badge optional">device_id</span>
                    </div>
                    <div class="mt-2" style="font-size:.72rem;color:#9ca3af;">
                        <span style="display:inline-block;width:10px;height:10px;background:#450a0a;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>red = required &nbsp;
                        <span style="display:inline-block;width:10px;height:10px;background:#1e293b;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>grey = optional
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
const csvFile   = document.getElementById('csvFile');
const uploadZone = document.getElementById('uploadZone');
const fileChosen = document.getElementById('fileChosen');
const submitBtn  = document.getElementById('submitBtn');

csvFile.addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const f = this.files[0];
        document.getElementById('chosenFileName').textContent = f.name;
        document.getElementById('chosenFileSize').textContent = (f.size / 1024).toFixed(1) + ' KB';
        fileChosen.style.display = 'flex';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }
});

function clearFile() {
    csvFile.value = '';
    fileChosen.style.display = 'none';
    submitBtn.disabled = true;
    submitBtn.style.opacity = '.5';
    submitBtn.style.cursor = 'not-allowed';
}

// Drag-and-drop
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) {
        csvFile.files = e.dataTransfer.files;
        csvFile.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
