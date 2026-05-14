@extends('index')

@section('title', 'Currency: ' . $currency->currency_code . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Currencies</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.currencies.index') }}">Currencies</a></li>
                <li class="breadcrumb-item">{{ $currency->currency_code }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('masters.currencies.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    @can('currencies.edit')
                    <a href="{{ route('masters.currencies.edit', $currency) }}" class="btn btn-light-brand">
                        <i class="feather-edit me-2"></i>Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row justify-content-center">
            <div class="col-xl-5">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <div>
                            <h5 class="card-title mb-0">{{ $currency->currency_name }}</h5>
                            <span class="badge bg-soft-primary text-primary">{{ $currency->currency_code }}</span>
                        </div>
                        @if($currency->is_default)
                            <span class="badge bg-soft-success text-success">Default</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Symbol</div>
                            <div class="col-sm-7 fw-semibold text-dark fs-20">{{ $currency->symbol ?? '—' }}</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Exchange Rate</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ number_format($currency->exchange_rate, 4) }} PKR</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Status</div>
                            <div class="col-sm-7">
                                @if($currency->status)
                                    <span class="badge bg-soft-success text-success">Active</span>
                                @else
                                    <span class="badge bg-soft-danger text-danger">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
