@extends('index')

@section('title', 'Add Currency - TradeSyncERP')

@section('content')
<div class="nxl-content apps-container">
    <div class="nxl-content without-header nxl-full-content">
        <div class="main-content d-flex">

            @include('partials.masters-sidebar')

            <div class="content-area" data-scrollbar-target="#psScrollbarInit">
                <div class="content-area-header bg-white sticky-top">
                    <div class="page-header-left d-flex align-items-center">
                        <a href="javascript:void(0);" class="app-sidebar-open-trigger me-2">
                            <i class="feather-align-left fs-24"></i>
                        </a>
                        <div class="page-header-title"><h5 class="m-b-10 mb-0">Currencies</h5></div>
                        <ul class="breadcrumb ms-3 mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('masters.currencies.index') }}">Currencies</a></li>
                            <li class="breadcrumb-item">Add Currency</li>
                        </ul>
                    </div>
                    <div class="page-header-right ms-auto">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('masters.currencies.index') }}" class="btn btn-light-brand">
                                <i class="feather-arrow-left me-2"></i><span>Back</span>
                            </a>
                            <button type="submit" form="currencyForm" class="btn btn-primary">
                                <i class="feather-save me-2"></i><span>Save Currency</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="content-area-body">
                    @include('partials.flash-messages')

                    <form id="currencyForm" action="{{ route('masters.currencies.store') }}" method="POST">
                        @csrf
                        @include('masters.currencies._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
