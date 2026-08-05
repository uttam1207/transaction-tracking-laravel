@extends('layouts.app')
@section('title', 'Purchase Order — ' . $purchaseOrder->po_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item active">{{ $purchaseOrder->po_number }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $purchaseOrder->po_number }}</h4>
            <p>{{ $purchaseOrder->vendor->name }} &mdash; {{ $purchaseOrder->order_date->format('d M Y') }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.procurement.edit', $purchaseOrder) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.procurement.destroy', $purchaseOrder) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Delete this purchase order?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.procurement.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4 justify-content-center">
    <div class="col-lg-9">
        <div class="card-glass overflow-hidden">
            <div style="background:linear-gradient(135deg,#059669,#0d9488);padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:100px;width:90px;height:90px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:58px;height:58px;background:rgba(255,255,255,.18);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-box-seam-fill" style="font-size:1.55rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $purchaseOrder->po_number }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:3px;">{{ $purchaseOrder->vendor->name }}</div>
                    </div>
                    <div class="ms-auto d-flex gap-2 flex-wrap">
                        @php
                            $oColor = match($purchaseOrder->status) {
                                'Paid'     => 'spill-success',
                                'Received' => 'spill-info',
                                'Sent'     => 'spill-warning',
                                default    => 'spill-secondary',
                            };
                        @endphp
                        <span class="spill {{ $oColor }}" style="font-size:.8rem;">{{ $purchaseOrder->status }}</span>
                        <div style="background:rgba(255,255,255,.18);border-radius:10px;padding:6px 12px;text-align:center;">
                            <div style="font-size:1rem;font-weight:800;color:#fff;">&#8377;{{ number_format($purchaseOrder->total_amount,0) }}</div>
                            <div style="font-size:.65rem;color:rgba(255,255,255,.8);font-weight:600;">PO Value</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-file-earmark me-1"></i>PO Number</div>
                            <div class="fw-bold" style="color:var(--primary);">{{ $purchaseOrder->po_number }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-shop me-1"></i>Vendor</div>
                            <div class="fw-bold" style="color:#1f2937;">{{ $purchaseOrder->vendor->name }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar3 me-1"></i>Order Date</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $purchaseOrder->order_date->format('d F Y') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#ecfdf5;border-radius:10px;padding:14px 16px;border:1px solid #d1fae5;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-currency-rupee me-1"></i>Total Amount</div>
                            <div class="fw-bold" style="color:#059669;font-size:1.1rem;">&#8377;{{ number_format($purchaseOrder->total_amount,2) }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-chat-text me-1"></i>Remarks</div>
                            <div style="color:#374151;line-height:1.6;">{{ $purchaseOrder->remarks ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clock me-1"></i>Created At</div>
                            <div style="color:#6b7280;font-size:.85rem;">{{ $purchaseOrder->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
