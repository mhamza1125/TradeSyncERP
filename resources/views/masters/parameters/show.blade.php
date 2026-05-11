@extends('index')

@section('title', 'Parameter: {{ $parameter->parameter_name }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Testing Parameters</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.parameters.index') }}">Parameters</a></li>
                <li class="breadcrumb-item">{{ $parameter->parameter_name }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('masters.parameters.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    @can('parameters.edit')
                    <a href="{{ route('masters.parameters.edit', $parameter) }}" class="btn btn-light-brand">
                        <i class="feather-edit me-2"></i>Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row justify-content-center">
            <div class="col-xl-6">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Parameter Details</h5>
                        @if($parameter->status)
                            <span class="badge bg-soft-success text-success">Active</span>
                        @else
                            <span class="badge bg-soft-danger text-danger">Inactive</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-0 mb-3">
                            <div class="col-sm-4 text-muted">Parameter Name</div>
                            <div class="col-sm-8 fw-semibold text-dark">{{ $parameter->parameter_name }}</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-4 text-muted">Category</div>
                            <div class="col-sm-8">
                                <span class="badge bg-soft-primary text-primary">
                                    {{ optional($parameter->category)->category_name ?? '—' }}
                                </span>
                            </div>
                        </div>
                        @if($parameter->description)
                        <div class="row g-0 mb-3">
                            <div class="col-sm-4 text-muted">Description</div>
                            <div class="col-sm-8 text-muted">{{ $parameter->description }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
