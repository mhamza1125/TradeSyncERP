@extends('index')

@section('title', 'Edit Purchase Order - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Purchase Orders</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Purchase Orders</a></li>
                <li class="breadcrumb-item"><a href="{{ route('purchase-orders.show', $purchaseOrder) }}">{{ $purchaseOrder->po_number }}</a></li>
                <li class="breadcrumb-item">Edit</li>
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
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="purchaseOrderForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Update Purchase Order</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        @if($purchaseOrder->amount_paid > 0)
        <div class="alert alert-warning border-0 mb-4 d-flex align-items-center gap-3">
            <i class="feather-alert-triangle fs-5"></i>
            <div>
                {{ number_format($purchaseOrder->amount_paid, 2) }} has already been paid against this order.
                The total cannot be reduced below that amount.
            </div>
        </div>
        @endif

        <form id="purchaseOrderForm" action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST">
            @csrf @method('PUT')
            @include('finance.purchase-orders._form')
        </form>
    </div>
</div>
@endsection
