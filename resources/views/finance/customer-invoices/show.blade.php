@extends('index')

@section('title', $customerInvoice->invoice_number . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Customer Invoices</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer-invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item">{{ $customerInvoice->invoice_number }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('customer-invoices.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i> Back
                    </a>
                    @can('customer-invoices.edit')
                    <a href="{{ route('customer-invoices.edit', $customerInvoice) }}" class="btn btn-primary">
                        <i class="feather-edit-2 me-2"></i> Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Invoice Main Card — full width --}}
        <div class="card stretch stretch-full mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $customerInvoice->invoice_number }}</h5>
                @php
                    $badge = match($customerInvoice->status) {
                        'Paid'      => 'success',
                        'Partial'   => 'warning',
                        'Overdue'   => 'danger',
                        'Sent'      => 'info',
                        'Cancelled' => 'secondary',
                        default     => 'light',
                    };
                @endphp
                <span class="badge bg-{{ $badge }} fs-12">{{ $customerInvoice->status }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted mb-1">Customer</h6>
                        <p class="fw-semibold mb-0">{{ $customerInvoice->customer->customer_name }}</p>
                        <p class="text-muted mb-0">{{ $customerInvoice->customer->phone }}</p>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="text-muted mb-1">Invoice Date</h6>
                        <p class="mb-1">{{ $customerInvoice->invoice_date->format('d M Y') }}</p>
                        @if($customerInvoice->due_date)
                        <h6 class="text-muted mb-1">Due Date</h6>
                        <p class="{{ $customerInvoice->isOverdue() ? 'text-danger fw-semibold' : '' }} mb-0">
                            {{ $customerInvoice->due_date->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th class="text-end">Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customerInvoice->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    @if($item->supplier)
                                    <div class="fw-semibold">{{ $item->supplier->name }}</div>
                                    @endif
                                    @if($item->inspectionType)
                                    <div class="text-muted fs-12">{{ $item->inspectionType->name }}</div>
                                    @endif
                                    @if($item->po_invoice_no)
                                    <div class="text-muted fs-12">PO/Inv: {{ $item->po_invoice_no }}</div>
                                    @endif
                                    @if($item->item_date)
                                    <div class="text-muted fs-12">{{ $item->item_date->format('d M Y') }}</div>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end">Subtotal</td>
                                <td class="text-end">{{ number_format($customerInvoice->subtotal, 2) }}</td>
                            </tr>
                            @if($customerInvoice->tax_amount > 0)
                            <tr>
                                <td colspan="2" class="text-end">Tax</td>
                                <td class="text-end">{{ number_format($customerInvoice->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            @if($customerInvoice->discount_amount > 0)
                            <tr>
                                <td colspan="2" class="text-end">Discount</td>
                                <td class="text-end text-danger">- {{ number_format($customerInvoice->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">Total</td>
                                <td class="text-end">{{ number_format($customerInvoice->total_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end text-success">Amount Paid</td>
                                <td class="text-end text-success">{{ number_format($customerInvoice->amount_paid, 2) }}</td>
                            </tr>
                            <tr class="fw-bold {{ $customerInvoice->amount_due > 0 ? 'text-danger' : 'text-success' }}">
                                <td colspan="2" class="text-end">Amount Due</td>
                                <td class="text-end">{{ number_format($customerInvoice->amount_due, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($customerInvoice->remarks)
                <div class="mt-3">
                    <h6 class="text-muted">Remarks</h6>
                    <p>{{ $customerInvoice->remarks }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Secondary cards: FC details + Attachments side by side if both exist --}}
        @if($customerInvoice->foreignCurrency || $customerInvoice->attachments->count())
        <div class="row">
            @if($customerInvoice->foreignCurrency)
            <div class="col-md-4 mb-4">
                <div class="card stretch stretch-full h-100">
                    <div class="card-header">
                        <h5 class="card-title">Foreign Currency</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-6 text-muted">Currency</dt>
                            <dd class="col-6">{{ $customerInvoice->foreignCurrency->currency_code }}</dd>
                            <dt class="col-6 text-muted">Exchange Rate</dt>
                            <dd class="col-6">{{ $customerInvoice->exchange_rate }}</dd>
                            <dt class="col-6 text-muted">Foreign Amount</dt>
                            <dd class="col-6">{{ number_format($customerInvoice->foreign_amount, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            @endif

            @if($customerInvoice->attachments->count())
            <div class="col-md-{{ $customerInvoice->foreignCurrency ? '8' : '12' }} mb-4">
                <div class="card stretch stretch-full h-100">
                    <div class="card-header">
                        <h5 class="card-title">Attachments</h5>
                    </div>
                    <div class="card-body">
                        @foreach($customerInvoice->attachments as $att)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="feather-{{ $att->isImage() ? 'image' : 'file' }} text-muted"></i>
                            <a href="{{ Storage::url($att->file_path) }}" target="_blank">{{ $att->title }}</a>
                            <small class="text-muted ms-auto">{{ $att->humanFileSize() }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
