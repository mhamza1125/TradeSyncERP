@extends('index')

@section('title', 'Edit Allowance Type - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Allowance Types</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('allowance-types.index') }}">Allowance Types</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('allowance-types.index') }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
                <button type="submit" form="allowanceTypeForm" class="btn btn-primary">
                    <i class="feather-save me-2"></i>Update
                </button>
            </div>
        </div>
    </div>
    <div class="main-content">
        @include('partials.flash-messages')
        <form id="allowanceTypeForm" action="{{ route('allowance-types.update', $allowanceType) }}" method="POST">
            @csrf @method('PUT')
            @include('finance.allowance-types._form')
        </form>
    </div>
</div>
@endsection
