@extends('index')

@section('title', 'Add Run — ' . $inspection->report_number . ' — TradeSyncERP')

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
                    <i class="feather-save me-2"></i>Create Run
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="createRunForm" action="{{ route('inspections.runs.store', $inspection) }}" method="POST">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Run Details</h5>
                    <small class="text-muted">After creating this run you will be taken to the results page to record testing parameter outcomes.</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label">Inspection Type</label>
                            <select name="inspection_type_id" class="form-select @error('inspection_type_id') is-invalid @enderror">
                                <option value="">— Select type (optional) —</option>
                                @foreach($inspectionTypes as $t)
                                    <option value="{{ $t->id }}" @selected(old('inspection_type_id') == $t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('inspection_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-7">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks"
                                   class="form-control @error('remarks') is-invalid @enderror"
                                   value="{{ old('remarks') }}"
                                   placeholder="e.g. Initial check, final random inspection…">
                            @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection
