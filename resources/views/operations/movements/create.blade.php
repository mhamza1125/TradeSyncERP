@extends('index')

@section('title', 'Issue Movement – {{ $sample->sample_code }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Issue Movement</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.index') }}">Samples</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.movements.index', $sample) }}">Movements</a></li>
                <li class="breadcrumb-item">Issue</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('samples.movements.index', $sample) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="movementForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Save Movement</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="movementForm" action="{{ route('samples.movements.store', $sample) }}" method="POST">
            @csrf
            <input type="hidden" name="sample_id" value="{{ $sample->id }}">
            @include('operations.movements._form')
        </form>
    </div>
</div>
@endsection
