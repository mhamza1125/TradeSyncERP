@extends('index')

@section('title', 'Payment Receipt - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Customer Payments</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer-payments.index') }}">Payments</a></li>
                <li class="breadcrumb-item">Receipt #{{ $customerPayment->id }}</li>
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
                    <a href="{{ route('customer-payments.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-icon btn-light-brand printBTN">
                        <i class="feather-printer"></i>
                    </a>
                    @can('customer-payments.edit')
                    <a href="{{ route('customer-payments.edit', $customerPayment) }}" class="btn btn-primary">
                        <i class="feather-edit me-2"></i>Edit
                    </a>
                    @endcan
                    @can('customer-payments.delete')
                    <form action="{{ route('customer-payments.destroy', $customerPayment) }}" method="POST"
                          onsubmit="return confirm('Delete this payment record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-light-brand" type="submit">
                            <i class="feather-trash-2 me-2"></i>Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content container-lg">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card invoice-container">
                    <div class="card-header">
                        <div>
                            <h2 class="fs-16 fw-700 mb-0">Payment Receipt</h2>
                            <span class="fs-12 text-muted">#{{ $customerPayment->id }}</span>
                        </div>
                        <span class="badge bg-soft-success text-success fs-12">Completed</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="px-4 pt-4">
                            <div class="d-sm-flex align-items-start justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-2">Received From:</h6>
                                    <address class="text-muted lh-lg">
                                        <strong class="text-dark">{{ $customerPayment->customer->customer_name }}</strong><br>
                                        {{ $customerPayment->customer->contact_person }}<br>
                                        {{ $customerPayment->customer->phone }}
                                    </address>
                                </div>
                                <div class="lh-lg pt-3 pt-sm-0">
                                    <div><span class="fw-bold text-dark">Payment Date:</span> <span class="text-muted">{{ \Carbon\Carbon::parse($customerPayment->payment_date)->format('d M Y') }}</span></div>
                                    @if($customerPayment->invoice_reference)
                                    <div><span class="fw-bold text-dark">Invoice Ref:</span> <span class="text-primary">{{ $customerPayment->invoice_reference }}</span></div>
                                    @endif
                                    <div><span class="fw-bold text-dark">Account:</span> <span class="text-muted">{{ optional($customerPayment->account)->account_name }}</span></div>
                                </div>
                            </div>
                        </div>
                        <hr class="border-dashed">
                        <div class="px-4 py-4">
                            <div class="d-sm-flex gap-4 justify-content-center">
                                <div>
                                    <h2 class="fs-16 fw-bold text-dark mb-3">Currency Details:</h2>
                                    <div class="text-muted lh-lg">
                                        <div><span class="text-muted">Foreign Currency:</span> <span class="fw-bold text-dark">{{ $customerPayment->foreign_currency }}</span></div>
                                        <div><span class="text-muted">Invoiced Amount:</span> <span class="fw-bold text-dark">{{ number_format($customerPayment->invoiced_amount_fc, 2) }} {{ $customerPayment->foreign_currency }}</span></div>
                                        <div><span class="text-muted">Received (FC):</span> <span class="fw-bold text-dark">{{ number_format($customerPayment->received_fc, 2) }} {{ $customerPayment->foreign_currency }}</span></div>
                                        <div><span class="text-muted">Deduction (FC):</span> <span class="fw-bold text-{{ $customerPayment->deduction_fc > 0 ? 'danger' : 'muted' }}">{{ number_format($customerPayment->deduction_fc, 2) }}</span></div>
                                    </div>
                                </div>
                                <div class="border-end border-end-dashed border-gray-500 d-none d-sm-block"></div>
                                <div>
                                    <h2 class="fs-16 fw-bold text-dark mb-3">PKR Details:</h2>
                                    <div class="text-muted lh-lg">
                                        <div><span class="text-muted">Exchange Rate:</span> <span class="fw-bold text-dark">{{ number_format($customerPayment->exchange_rate, 4) }}</span></div>
                                        <div><span class="text-muted">Expected PKR:</span> <span class="fw-bold text-dark">{{ number_format($customerPayment->expected_pkr, 2) }}</span></div>
                                        <div><span class="text-muted">Actual PKR Received:</span> <span class="fw-bold text-success fs-16">{{ number_format($customerPayment->actual_pkr_received, 2) }}</span></div>
                                        <div><span class="text-muted">PKR Gain/Loss:</span>
                                            @if($customerPayment->pkr_gain_loss > 0)
                                                <span class="fw-bold text-success">+{{ number_format($customerPayment->pkr_gain_loss, 2) }}</span>
                                            @elseif($customerPayment->pkr_gain_loss < 0)
                                                <span class="fw-bold text-danger">{{ number_format($customerPayment->pkr_gain_loss, 2) }}</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
