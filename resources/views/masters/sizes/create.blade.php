@extends('index')

@section('title', 'Add Size - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Sizes</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.sizes.index') }}">Sizes</a></li>
                <li class="breadcrumb-item">Add</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('masters.sizes.index') }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
                <button type="submit" form="sizeForm" class="btn btn-primary">
                    <i class="feather-save me-2"></i>Save
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')
        <form id="sizeForm" action="{{ route('masters.sizes.store') }}" method="POST">
            @csrf
            @include('masters.sizes._form')
        </form>
    </div>
</div>
@endsection
