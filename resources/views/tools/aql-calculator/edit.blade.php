@extends('index')

@section('title', 'Edit AQL Calculation - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">AQL Calculator</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tools.aql-calculator.index') }}">AQL Calculator</a></li>
                <li class="breadcrumb-item">Edit: {{ $aqlCalculation->title }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('tools.aql-calculator.show', $aqlCalculation) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="aqlCalculationForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Update</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="aqlCalculationForm" action="{{ route('tools.aql-calculator.update', $aqlCalculation) }}" method="POST">
            @csrf @method('PUT')
            @include('tools.aql-calculator._form')
        </form>
    </div>
</div>
@endsection
