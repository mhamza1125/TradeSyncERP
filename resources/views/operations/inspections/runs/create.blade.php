@extends('index')

@section('title', 'Add Run — ' . $inspection->report_number . ' — TradeSyncERP')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/tom-select/tom-select.bootstrap5.min.css') }}">
@endpush

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Add Inspection Run</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.edit', $inspection) }}">{{ $inspection->report_number }}</a></li>
                <li class="breadcrumb-item">Add Run</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
                <button type="submit" form="createRunForm" class="btn btn-primary">
                    <i class="feather-play me-1"></i>Start Run
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <form id="createRunForm" action="{{ route('inspections.runs.store', $inspection) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Select Sample for This Run</h5>
                            <small class="text-muted d-block mt-1">
                                Each run tests exactly one sample. Inspection sections will be automatically
                                applied based on the
                                @if($inspection->inspectionType)
                                    <strong>{{ $inspection->inspectionType->name }}</strong> type
                                @else
                                    inspection type
                                @endif
                                and the sample's product category.
                            </small>
                        </div>
                        <div class="card-body pb-5">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Sample <span class="text-danger">*</span></label>
                                <select id="sampleSelect"
                                        name="sample_id"
                                        class="form-select @error('sample_id') is-invalid @enderror"
                                        required>
                                    <option value="">— Search and select a sample —</option>
                                    @foreach($samples as $s)
                                        <option value="{{ $s['id'] }}"
                                                @selected(old('sample_id') == $s['id'])>
                                            {{ $s['text'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sample_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold">
                                    Files To Review <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <small class="text-muted d-block mb-2">
                                    Attach reference documents, specs, or PDFs for the inspector to review before
                                    starting this run. Visible to the inspector as read-only.
                                </small>
                                <input type="file"
                                       id="reviewFilesInput"
                                       name="review_files[]"
                                       class="form-control @error('review_files') is-invalid @enderror"
                                       multiple
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,image/*">
                                @error('review_files')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <ul id="reviewFilesList" class="list-unstyled mt-2 mb-0 fs-13"></ul>
                            </div>

                            @if(!$inspection->inspection_type_id)
                            <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-0 mt-3">
                                <i class="feather-alert-triangle flex-shrink-0"></i>
                                <span class="fs-13">
                                    This inspection has no type set. Sections cannot be auto-resolved.
                                    <a href="{{ route('inspections.edit', $inspection) }}">Edit the inspection</a> to set a type first.
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/tom-select/tom-select.complete.min.js') }}"></script>
<script>
new TomSelect('#sampleSelect', {
    maxOptions: null,
    placeholder: '— Search and select a sample —',
    create: false,
    dropdownParent: 'body',
});

document.getElementById('reviewFilesInput')?.addEventListener('change', function () {
    const list = document.getElementById('reviewFilesList');
    list.innerHTML = '';
    [...this.files].forEach(f => {
        const li = document.createElement('li');
        li.innerHTML = '<i class="feather-file me-1 text-muted"></i>' + f.name;
        list.appendChild(li);
    });
});
</script>
@endpush
