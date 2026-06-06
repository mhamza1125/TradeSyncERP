@extends('index')

@section('title', 'New Inspection Section - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">New Inspection Section</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspection-sections.index') }}">Inspection Sections</a></li>
                <li class="breadcrumb-item">New</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspection-sections.index') }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
                <button type="submit" form="sectionForm" class="btn btn-primary">
                    <i class="feather-save me-2"></i>Save Section
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')
        <form id="sectionForm" action="{{ route('inspection-sections.store') }}" method="POST">
            @csrf
            @include('operations.inspection-sections._form')
        </form>
    </div>
</div>
@endsection
