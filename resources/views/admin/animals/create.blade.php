@extends('layouts.app')
@section('title', 'Register New Animal')

@section('content')
<div class="container py-3" style="max-width: 800px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.animals.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h1 class="h3 mb-0 fw-bold text-primary"><i class="bi bi-plus-circle me-2"></i>Register New Animal</h1>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.animals.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ear Tag Number <span class="text-danger">*</span></label>
                        <input type="text" name="tag_number" class="form-control" placeholder="e.g. ASD-BUF-011" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Animal Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Laxmi">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Breed <span class="text-danger">*</span></label>
                        <select name="breed" class="form-select">
                            <option value="Murrah">Murrah Buffalo</option>
                            <option value="Bhadawari">Bhadawari Buffalo</option>
                            <option value="Jafarabadi">Jafarabadi</option>
                            <option value="Nili-Ravi">Nili-Ravi</option>
                            <option value="Crossbred">Crossbred</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Lactation Number <span class="text-danger">*</span></label>
                        <input type="number" name="lactation_number" class="form-control" value="1" min="0" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Current Weight (kg)</label>
                        <input type="number" step="0.1" name="current_weight" class="form-control" placeholder="e.g. 520">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Pregnancy Status <span class="text-danger">*</span></label>
                        <select name="pregnancy_status" class="form-select">
                            <option value="Open">Open</option>
                            <option value="Inseminated">Inseminated</option>
                            <option value="Pregnant">Pregnant</option>
                            <option value="Dry">Dry</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Health Status <span class="text-danger">*</span></label>
                        <select name="health_status" class="form-select">
                            <option value="Healthy">Healthy</option>
                            <option value="Sick">Sick</option>
                            <option value="Under Treatment">Under Treatment</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Shed Number <span class="text-danger">*</span></label>
                        <input type="text" name="shed_number" class="form-control" value="Shed No. 1" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.animals.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Register Animal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
