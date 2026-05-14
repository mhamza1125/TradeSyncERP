@extends('index')

@section('title', 'Edit {{ $inspection->report_number }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Edit Inspection</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.index') }}">Samples</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.inspections.index', $sample) }}">Inspections</a></li>
                {{-- Shallow route: inspections.show --}}
                <li class="breadcrumb-item"><a href="{{ route('inspections.show', $inspection) }}">{{ $inspection->report_number }}</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    {{-- Shallow route: inspections.show --}}
                    <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="editInspectionForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Shallow route: inspections.update --}}
        <form id="editInspectionForm" action="{{ route('inspections.update', $inspection) }}" method="POST">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-xl-8">
                    @include('operations.inspections._form')
                </div>

                {{-- Results summary sidebar — edit-only --}}
                <div class="col-xl-4">
                    <div class="card stretch stretch-full">
                        <div class="card-header"><h5 class="card-title">Results Summary</h5></div>
                        <div class="card-body">
                            @php
                                $passed = $inspection->results->where('pass_fail', 'Pass')->count();
                                $failed = $inspection->results->where('pass_fail', 'Fail')->count();
                            @endphp
                            <div class="d-flex gap-3 mb-4">
                                <div class="text-center flex-fill">
                                    <div class="fs-24 fw-bold text-success">{{ $passed }}</div>
                                    <div class="text-muted fs-12">Pass</div>
                                </div>
                                <div class="text-center flex-fill">
                                    <div class="fs-24 fw-bold text-danger">{{ $failed }}</div>
                                    <div class="text-muted fs-12">Fail</div>
                                </div>
                                <div class="text-center flex-fill">
                                    <div class="fs-24 fw-bold text-secondary">{{ $inspection->results->count() }}</div>
                                    <div class="text-muted fs-12">Total</div>
                                </div>
                            </div>
                            {{-- Shallow route: inspections.show --}}
                            <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-light-brand w-100">
                                <i class="feather-eye me-2"></i> View Full Results
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
