@extends('index')

@section('title', '{{ $inspectionType->name }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">{{ $inspectionType->name }}</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.inspection-types.index') }}">Inspection Types</a></li>
                <li class="breadcrumb-item">{{ $inspectionType->name }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('masters.inspection-types.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    @can('inspection-types.edit')
                    <a href="{{ route('masters.inspection-types.edit', $inspectionType) }}" class="btn btn-primary">
                        <i class="feather-edit me-2"></i><span>Edit</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')
        <div class="row">
            <div class="col-xl-6">
                <div class="card stretch stretch-full">
                    <div class="card-header"><h5 class="card-title">Details</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="text-muted fs-12">Name</div>
                                <div class="fw-semibold">{{ $inspectionType->name }}</div>
                            </div>
                            @if($inspectionType->description)
                            <div class="col-12">
                                <div class="text-muted fs-12">Description</div>
                                <div>{{ $inspectionType->description }}</div>
                            </div>
                            @endif
                            <div class="col-12">
                                <div class="text-muted fs-12">Status</div>
                                <div>
                                    @if($inspectionType->status)
                                        <span class="badge bg-soft-success text-success">Active</span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted fs-12">Total Inspections</div>
                                <div class="fw-semibold">{{ $inspectionType->inspections->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
