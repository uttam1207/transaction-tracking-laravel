@extends('layouts.app')
@section('title', 'Customer — ' . $crmCustomer->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.crm.index') }}">CRM</a></li>
    <li class="breadcrumb-item active">{{ $crmCustomer->name }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $crmCustomer->name }}</h4>
            <p>{{ $crmCustomer->category }} &mdash; {{ $crmCustomer->status }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.crm.edit', $crmCustomer) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.crm.destroy', $crmCustomer) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Delete this contact?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.crm.index') }}" class="btn btn-sm btn-outline-secondary px-4">
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

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-glass overflow-hidden">
            <div style="background:linear-gradient(135deg,#2563eb,#4f46e5);padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:58px;height:58px;background:rgba(255,255,255,.18);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-person-lines-fill" style="font-size:1.55rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $crmCustomer->name }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:3px;">{{ $crmCustomer->category }}</div>
                    </div>
                    <div class="ms-auto d-flex gap-2 flex-wrap">
                        @php $sColor = $crmCustomer->status==='Active' ? 'spill-success' : ($crmCustomer->status==='Lead' ? 'spill-warning' : 'spill-secondary'); @endphp
                        <span class="spill {{ $sColor }}" style="font-size:.8rem;">{{ $crmCustomer->status }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-person me-1"></i>Name</div>
                            <div class="fw-bold" style="color:#1f2937;">{{ $crmCustomer->name }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;"><i class="bi bi-grid me-1"></i>Category</div>
                            <span class="spill spill-info">{{ $crmCustomer->category }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-telephone me-1"></i>Phone</div>
                            <div style="color:#374151;">{{ $crmCustomer->phone ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-envelope me-1"></i>Email</div>
                            <div style="color:#374151;">{{ $crmCustomer->email ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-geo-alt me-1"></i>Address</div>
                            <div style="color:#374151;">{{ $crmCustomer->address ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#ecfdf5;border-radius:10px;padding:14px 16px;border:1px solid #d1fae5;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-graph-up-arrow me-1"></i>Total Business</div>
                            <div class="fw-bold" style="color:#059669;font-size:1.05rem;">&#8377;{{ number_format($crmCustomer->total_business_value,0) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clock me-1"></i>Registered At</div>
                            <div style="color:#6b7280;font-size:.85rem;">{{ $crmCustomer->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-glass">
            <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#059669,#0d9488);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-receipt-cutoff" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <h6 class="mb-0 fw-bold">Recent Sales Orders</h6>
            </div>
            <div class="px-4 pb-3">
                @forelse($crmCustomer->salesOrders->sortByDesc('sale_date')->take(6) as $order)
                    <div class="d-flex justify-content-between align-items-start py-3 border-bottom">
                        <div>
                            <div class="fw-bold" style="font-size:.84rem;color:var(--primary);">{{ $order->invoice_number }}</div>
                            <div style="font-size:.76rem;color:#9ca3af;">{{ $order->item_type }} &mdash; {{ $order->sale_date->format('d M Y') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success" style="font-size:.84rem;">&#8377;{{ number_format($order->total_amount,0) }}</div>
                            <span class="spill {{ $order->payment_status==='Paid' ? 'spill-success' : ($order->payment_status==='Pending' ? 'spill-warning' : 'spill-info') }}" style="font-size:.7rem;">{{ $order->payment_status }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-4 text-center" style="color:#9ca3af;font-size:.85rem;">
                        <i class="bi bi-bag-x d-block mb-2" style="font-size:1.8rem;"></i>No sales orders yet
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
