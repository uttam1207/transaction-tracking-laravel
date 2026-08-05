@extends('layouts.app')
@section('title', 'Animal — ' . $animal->tag_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.animals.index') }}">Animals</a></li>
    <li class="breadcrumb-item active">{{ $animal->tag_number }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $animal->tag_number }}{{ $animal->name ? ' — '.$animal->name : '' }}</h4>
            <p>{{ $animal->breed }} &mdash; Shed {{ $animal->shed_number }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.animals.edit', $animal) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.animals.destroy', $animal) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Remove this animal from the register?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.animals.index') }}" class="btn btn-sm btn-outline-secondary px-4">
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
    {{-- Left: Animal Profile --}}
    <div class="col-lg-5">
        <div class="card-glass overflow-hidden mb-4">
            @php
                $breedLower = strtolower($animal->breed ?? '');
                $avatarGrad = str_contains($breedLower,'buffalo') ? 'linear-gradient(135deg,#2563eb,#4f46e5)'
                    : (str_contains($breedLower,'gir')||str_contains($breedLower,'cow')||str_contains($breedLower,'holstein')
                        ? 'linear-gradient(135deg,#059669,#0d9488)' : 'linear-gradient(135deg,#d97706,#ea580c)');
            @endphp
            <div style="background:{{ $avatarGrad }};padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.4rem;font-weight:800;color:#fff;letter-spacing:-.02em;">
                        {{ strtoupper(substr($animal->tag_number,-2)) }}
                    </div>
                    <div>
                        <div style="font-size:1.15rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $animal->tag_number }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:2px;">{{ $animal->name ?? 'Unnamed' }}</div>
                    </div>
                    <div class="ms-auto d-flex flex-column gap-1 align-items-end">
                        @php
                            $hColor = $animal->health_status==='Healthy' ? 'spill-success' : ($animal->health_status==='Sick' ? 'spill-danger' : 'spill-warning');
                        @endphp
                        <span class="spill {{ $hColor }}" style="font-size:.75rem;">{{ $animal->health_status }}</span>
                        <span class="spill {{ $animal->status==='Active' ? 'spill-success' : 'spill-secondary' }}" style="font-size:.75rem;">{{ $animal->status }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-tag me-1"></i>Tag No.</div>
                            <div class="fw-bold" style="color:var(--primary);font-size:.9rem;">{{ $animal->tag_number }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-layers me-1"></i>Breed</div>
                            <div style="color:#374151;font-size:.88rem;">{{ $animal->breed }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-calendar me-1"></i>Date of Birth</div>
                            <div style="color:#374151;font-size:.88rem;">{{ $animal->dob ? $animal->dob->format('d M Y') : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-123 me-1"></i>Lactation No.</div>
                            <div class="fw-bold" style="color:#1f2937;font-size:.95rem;">{{ $animal->lactation_number }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-speedometer me-1"></i>Weight</div>
                            <div style="color:#374151;font-size:.88rem;">{{ $animal->current_weight ? $animal->current_weight.' kg' : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-house me-1"></i>Shed</div>
                            <div style="color:#374151;font-size:.88rem;">{{ $animal->shed_number }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-heart me-1"></i>Pregnancy</div>
                            <div><span class="spill spill-info" style="font-size:.75rem;">{{ $animal->pregnancy_status }}</span></div>
                        </div>
                    </div>
                    @if($animal->purchase_date || $animal->purchase_cost)
                    <div class="col-6">
                        <div style="background:#ecfdf5;border-radius:10px;padding:12px 14px;border:1px solid #d1fae5;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-currency-rupee me-1"></i>Purchase Cost</div>
                            <div class="fw-bold" style="color:#059669;font-size:.9rem;">{{ $animal->purchase_cost ? '₹'.number_format($animal->purchase_cost,0) : '—' }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Actions & Health --}}
    <div class="col-lg-7">
        {{-- Log Action --}}
        <div class="card-glass mb-4">
            <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#059669,#16a34a);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-plus-circle" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <h6 class="mb-0 fw-bold">Log Action</h6>
            </div>
            <div class="p-4">
                <form action="{{ route('admin.animals.actions.store', $animal) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-5">
                            <select name="action_type" class="form-select form-select-sm" required>
                                <option value="">Select Action Type</option>
                                @foreach(['Vaccination','Deworming','Heat Detection','AI','Pregnancy Check','Calving','Dry Off','Sale','Death'] as $act)
                                    <option value="{{ $act }}">{{ $act }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="action_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="cost" step="0.01" min="0" class="form-control form-control-sm" placeholder="Cost &#8377;">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes">
                        </div>
                    </div>
                    <button class="btn btn-primary-grad btn-sm mt-3 px-4"><i class="bi bi-save me-1"></i>Save Action</button>
                </form>
            </div>
        </div>

        {{-- Action History --}}
        <div class="card-glass mb-4">
            <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#4f46e5);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-clock-history" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <h6 class="mb-0 fw-bold">Action History</h6>
            </div>
            <div class="px-4 pb-2">
                @forelse($animal->actions->sortByDesc('action_date') as $action)
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div>
                            <span class="spill spill-primary me-2" style="font-size:.74rem;">{{ $action->action_type }}</span>
                            <span style="font-size:.83rem;color:#374151;">{{ $action->action_date->format('d M Y') }}</span>
                            @if($action->notes)<span style="font-size:.76rem;color:#9ca3af;"> &mdash; {{ $action->notes }}</span>@endif
                        </div>
                        @if($action->cost)
                            <span class="fw-bold" style="color:#059669;font-size:.84rem;">&#8377;{{ number_format($action->cost,0) }}</span>
                        @endif
                    </div>
                @empty
                    <div class="py-4 text-center" style="color:#9ca3af;font-size:.85rem;">
                        <i class="bi bi-journal-x d-block mb-2" style="font-size:1.8rem;"></i>No actions logged yet
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Health Records --}}
        <div class="card-glass">
            <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#dc2626,#9f1239);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-heart-pulse" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <h6 class="mb-0 fw-bold">Recent Health Records</h6>
            </div>
            <div class="px-4 pb-2">
                @forelse($animal->healthRecords->sortByDesc('date')->take(6) as $h)
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div>
                            @php $htColor = match($h->record_type) { 'Vaccination'=>'spill-success','Treatment'=>'spill-danger','Deworming'=>'spill-info',default=>'spill-warning' }; @endphp
                            <span class="spill {{ $htColor }} me-2" style="font-size:.74rem;">{{ $h->record_type }}</span>
                            <span style="font-size:.83rem;color:#374151;">{{ $h->date->format('d M Y') }}</span>
                            @if($h->disease_symptoms)<span style="font-size:.76rem;color:#9ca3af;"> &mdash; {{ Str::limit($h->disease_symptoms,30) }}</span>@endif
                        </div>
                        @if($h->cost)
                            <span class="fw-bold" style="color:#dc2626;font-size:.84rem;">&#8377;{{ number_format($h->cost,0) }}</span>
                        @endif
                    </div>
                @empty
                    <div class="py-4 text-center" style="color:#9ca3af;font-size:.85rem;">
                        <i class="bi bi-heart d-block mb-2" style="font-size:1.8rem;"></i>No health records
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
