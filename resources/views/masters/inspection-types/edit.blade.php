@extends('index')

@section('title', 'Edit Inspection Type - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Inspection Types</h5></div>
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
                    <button type="submit" form="inspectionTypeForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Update</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')
        <form id="inspectionTypeForm" action="{{ route('masters.inspection-types.update', $inspectionType) }}" method="POST">
            @csrf
            @method('PUT')
            @include('masters.inspection-types._form')
        </form>
    </div>
</div>
@endsection
