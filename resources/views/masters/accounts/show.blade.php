@extends('index')

@section('title', 'Account: ' . $account->account_name . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Accounts</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.accounts.index') }}">Accounts</a></li>
                <li class="breadcrumb-item">{{ $account->account_name }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('masters.accounts.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    @can('accounts.edit')
                    <a href="{{ route('masters.accounts.edit', $account) }}" class="btn btn-light-brand">
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
            <div class="col-xl-6">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Account Details</h5>
                        @if($account->status)
                            <span class="badge bg-soft-success text-success">Active</span>
                        @else
                            <span class="badge bg-soft-danger text-danger">Inactive</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Account Name</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ $account->account_name }}</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Type</div>
                            <div class="col-sm-7">
                                <span class="badge bg-soft-primary text-primary">{{ $account->account_type }}</span>
                            </div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Bank</div>
                            <div class="col-sm-7">{{ optional($account->bank)->bank_name ?? '—' }}</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Currency</div>
                            <div class="col-sm-7">{{ $account->currency ?? '—' }}</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Opening Balance</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ number_format($account->opening_balance, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
