@extends('index')

@section('title', 'Update Return – TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Update Return</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.index') }}">Samples</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.movements.index', $sample) }}">Movements</a></li>
                <li class="breadcrumb-item">Update Return</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('movements.show', $movement) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="editMovementForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="editMovementForm" action="{{ route('movements.update', $movement) }}" method="POST">
            @csrf @method('PUT')
            @include('operations.movements._form')
        </form>
    </div>
</div>
@endsection
