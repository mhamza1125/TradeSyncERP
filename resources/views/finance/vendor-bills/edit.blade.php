@extends('index')

@section('title', 'Edit Bill - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Vendor Bills</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vendor-bills.index') }}">Vendor Bills</a></li>
                <li class="breadcrumb-item">{{ $vendorBill->bill_number }}</li>
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
                    <a href="{{ route('vendor-bills.show', $vendorBill) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="billForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Update Bill</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')
        <form id="billForm" action="{{ route('vendor-bills.update', $vendorBill) }}" method="POST">
            @csrf @method('PUT')
            @include('finance.vendor-bills._form')
        </form>
    </div>
</div>
@endsection
