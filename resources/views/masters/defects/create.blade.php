@extends('index')

@section('title', 'Add Defect — TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Add Defect</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.defects.index') }}">Defects</a></li>
                <li class="breadcrumb-item">Add</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('masters.defects.index') }}" class="btn btn-light-brand">
                <i class="feather-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('masters.defects.store') }}" method="POST">
                            @csrf
                            @include('masters.defects._form')
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-save me-2"></i>Save Defect
                                </button>
                                <a href="{{ route('masters.defects.index') }}" class="btn btn-light-brand">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
