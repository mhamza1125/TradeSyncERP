@extends('index')

@section('title', 'Customer Payments - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Customer Payments</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Payments</li>
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
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                        <i class="feather-filter"></i>
                    </a>
                    @can('customer-payments.index')
                    <a href="{{ route('customer-payments.export-list-pdf', request()->query()) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i><span>Export PDF</span>
                    </a>
                    @endcan
                    @can('customer-payments.create')
                    <a href="{{ route('customer-payments.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Record Payment</span>
                    </a>
                    @endcan
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <div id="collapseFilters" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('customer-payments.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="customer_id" class="form-select">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search me-1"></i>Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('customer-payments.index') }}" class="btn btn-light-brand w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="paymentList">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Invoice Ref</th>
                                        <th>Currency</th>
                                        <th>FC Received</th>
                                        <th>Exchange Rate</th>
                                        <th>PKR Received</th>
                                        <th>Gain/Loss (PKR)</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                    <tr class="single-item">
                                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('masters.customers.show', $payment->customer) }}" class="hstack gap-3">
                                                <div class="avatar-text avatar-md bg-soft-primary text-primary">
                                                    {{ strtoupper(substr($payment->customer->customer_name, 0, 1)) }}
                                                </div>
                                                <span class="text-truncate-1-line">{{ $payment->customer->customer_name }}</span>
                                            </a>
                                        </td>
                                        <td>{{ $payment->invoice_reference ?? '—' }}</td>
                                        <td>{{ $payment->foreign_currency }}</td>
                                        <td class="fw-semibold">{{ number_format($payment->received_fc, 2) }}</td>
                                        <td>{{ number_format($payment->exchange_rate, 4) }}</td>
                                        <td class="fw-bold text-dark">{{ number_format($payment->actual_pkr_received, 2) }}</td>
                                        <td>
                                            @if($payment->pkr_gain_loss > 0)
                                                <span class="text-success fw-semibold">+{{ number_format($payment->pkr_gain_loss, 2) }}</span>
                                            @elseif($payment->pkr_gain_loss < 0)
                                                <span class="text-danger fw-semibold">{{ number_format($payment->pkr_gain_loss, 2) }}</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('customer-payments.index')
                                                <a href="{{ route('customer-payments.show', $payment) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                @canany(['customer-payments.edit', 'customer-payments.delete'])
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21" data-bs-strategy="fixed">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('customer-payments.edit')
                                                        <li>
                                                            <a href="{{ route('customer-payments.edit', $payment) }}" class="dropdown-item">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('customer-payments.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('customer-payments.destroy', $payment) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this payment?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Delete</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                                @endcanany
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="feather-credit-card fs-1 d-block mb-2"></i>
                                            No payments found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($payments->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}</small>
                        {{ $payments->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
