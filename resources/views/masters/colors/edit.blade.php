@extends('index')

@section('title', 'Edit Color - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Colors</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.colors.index') }}">Colors</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('masters.colors.index') }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
                <button type="submit" form="colorForm" class="btn btn-primary">
                    <i class="feather-save me-2"></i>Update
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')
        <form id="colorForm" action="{{ route('masters.colors.update', $color) }}" method="POST">
            @csrf @method('PUT')
            @include('masters.colors._form')
        </form>
    </div>
</div>
@endsection
