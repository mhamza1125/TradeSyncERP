@extends('index')

@section('title', 'Customer Invoices - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Customer Invoices</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Customer Invoices</li>
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
                    @can('customer-invoices.create')
                    <a href="{{ route('customer-invoices.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>New Invoice</span>
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

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Filters --}}
        <div class="collapse mb-4 @if(request()->hasAny(['search','customer_id','status','from_date','to_date'])) show @endif" id="collapseFilters">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <form method="GET" action="{{ route('customer-invoices.index') }}">
                        <div class="row g-3">
                            <div class="col-lg-3">
                                <input type="text" name="search" class="form-control" placeholder="Search invoice no..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-lg-3">
                                <select name="customer_id" class="form-select">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->customer_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    @foreach(['Draft','Sent','Partial','Paid','Overdue','Cancelled'] as $s)
                                    <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-lg-2">
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="feather-search me-1"></i> Filter
                                </button>
                                <a href="{{ route('customer-invoices.index') }}" class="btn btn-light btn-sm">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card stretch stretch-full">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Due</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('customer-invoices.show', $invoice) }}" class="fw-semibold text-primary">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->customer->customer_name }}</td>
                                <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                <td>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                                <td class="text-end">{{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="text-end">{{ number_format($invoice->amount_paid, 2) }}</td>
                                <td class="text-end fw-semibold {{ $invoice->amount_due > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($invoice->amount_due, 2) }}
                                </td>
                                <td>
                                    @php
                                        $badge = match($invoice->status) {
                                            'Paid'      => 'success',
                                            'Partial'   => 'warning',
                                            'Overdue'   => 'danger',
                                            'Sent'      => 'info',
                                            'Cancelled' => 'secondary',
                                            default     => 'light',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $invoice->status }}</span>
                                </td>
                                <td>
                                    <div class="hstack gap-2 justify-content-end">
                                        @can('customer-invoices.index')
                                        <a href="{{ route('customer-invoices.show', $invoice) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                            <i class="feather feather-eye"></i>
                                        </a>
                                        @endcan
                                        <div class="dropdown">
                                            <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                <i class="feather feather-more-horizontal"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                @can('customer-invoices.edit')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('customer-invoices.edit', $invoice) }}">
                                                        <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('customer-invoices.delete')
                                                <li class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('customer-invoices.destroy', $invoice) }}" method="POST"
                                                          onsubmit="return confirm('Delete this invoice?')">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="feather feather-trash-2 me-3"></i><span>Delete</span>
                                                        </button>
                                                    </form>
                                                </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No invoices found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($invoices->hasPages())
                <div class="card-footer">
                    {{ $invoices->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
