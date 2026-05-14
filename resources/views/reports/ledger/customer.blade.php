@extends('index')

@section('title', 'Customer Ledger — ' . $customer->customer_name . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Customer Ledger</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Reports</li>
                <li class="breadcrumb-item">{{ $customer->customer_name }}</li>
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
                    <button onclick="window.print()" class="btn btn-light-brand">
                        <i class="feather-printer me-2"></i>Print
                    </button>
                    <a href="{{ route('masters.customers.show', $customer) }}" class="btn btn-light-brand">
                        <i class="feather-user me-2"></i>Customer Profile
                    </a>
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <div id="collapseFilters" class="accordion-collapse collapse {{ request()->hasAny(['from_date','to_date']) ? 'show' : '' }} page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('ledger.customer', $customer) }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search me-1"></i>Apply</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('ledger.customer', $customer) }}" class="btn btn-light-brand w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Customer Summary --}}
        <div class="row mb-3">
            <div class="col-md-5">
                <div class="card border-0 bg-soft-success">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-lg bg-success text-white rounded">
                                {{ strtoupper(substr($customer->customer_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold fs-6">{{ $customer->customer_name }}</div>
                                @if($customer->email)
                                <div class="text-muted small"><i class="feather-mail me-1"></i>{{ $customer->email }}</div>
                                @endif
                                @if($customer->phone)
                                <div class="text-muted small"><i class="feather-phone me-1"></i>{{ $customer->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @php
                $totalReceived = $payments->sum('actual_pkr_received');
                $totalFc       = $payments->sum('received_fc');
                $totalGainLoss = $payments->sum('pkr_gain_loss');
            @endphp
            <div class="col-md-7">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="card border-0 text-center py-3">
                            <div class="text-muted small mb-1">Total Payments (Page)</div>
                            <div class="fw-bold text-success fs-6">{{ number_format($totalReceived, 2) }}</div>
                            <div class="text-muted small">PKR</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 text-center py-3">
                            <div class="text-muted small mb-1">FC Received (Page)</div>
                            <div class="fw-bold text-primary fs-6">{{ number_format($totalFc, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 text-center py-3">
                            <div class="text-muted small mb-1">Gain / Loss (Page)</div>
                            <div class="fw-bold fs-6 {{ $totalGainLoss >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ ($totalGainLoss >= 0 ? '+' : '') . number_format($totalGainLoss, 2) }}
                            </div>
                            <div class="text-muted small">PKR</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="customerLedger">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:110px">Date</th>
                                        <th>Invoice Ref</th>
                                        <th>Currency</th>
                                        <th class="text-end">Invoiced (FC)</th>
                                        <th class="text-end">Received (FC)</th>
                                        <th class="text-end">Rate</th>
                                        <th class="text-end">PKR Received</th>
                                        <th class="text-end">Gain/Loss</th>
                                        <th>Account</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                    <tr>
                                        <td class="text-nowrap">{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td>{{ $payment->invoice_reference ?? '—' }}</td>
                                        <td>{{ $payment->foreign_currency }}</td>
                                        <td class="text-end">{{ number_format($payment->invoiced_amount_fc, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($payment->received_fc, 2) }}</td>
                                        <td class="text-end text-muted">{{ number_format($payment->exchange_rate, 4) }}</td>
                                        <td class="text-end fw-bold text-dark">{{ number_format($payment->actual_pkr_received, 2) }}</td>
                                        <td class="text-end fw-semibold">
                                            @if($payment->pkr_gain_loss > 0)
                                                <span class="text-success">+{{ number_format($payment->pkr_gain_loss, 2) }}</span>
                                            @elseif($payment->pkr_gain_loss < 0)
                                                <span class="text-danger">{{ number_format($payment->pkr_gain_loss, 2) }}</span>
                                            @else
                                                <span class="text-muted">0.00</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $payment->account->account_name ?? '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="feather-inbox fs-1 d-block mb-2"></i>
                                            No payments found for the selected period.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($payments->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }} payments</small>
                        {{ $payments->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
