@extends('index')

@section('title', 'New Inspection - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">New Inspection</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                <li class="breadcrumb-item">New</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspections.index') }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i><span>Back</span>
                </a>
                <button type="submit" form="inspectionForm" class="btn btn-primary">
                    <i class="feather-save me-2"></i><span>Save Inspection</span>
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="inspectionForm" action="{{ route('inspections.store') }}" method="POST">
            @csrf
            @include('operations.inspections._form')
        </form>
    </div>
</div>
@endsection
