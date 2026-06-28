@extends('index')

@section('title', 'Customer Orders - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Customer Orders</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Customer Orders</li>
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
                    @can('customer-orders.index')
                    <a href="{{ route('customer-orders.export-list-pdf', request()->query()) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i><span>Export PDF</span>
                    </a>
                    @endcan
                    @can('customer-orders.create')
                    <a href="{{ route('customer-orders.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>New Order</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div id="collapseFilters" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('customer-orders.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Order code..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="customer_id" class="form-select">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach(['Draft','Confirmed','Processing','Dispatched','Cancelled'] as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" placeholder="From" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('customer-orders.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order Code</th>
                                        <th>Customer</th>
                                        <th>Brand</th>
                                        <th>Order Date</th>
                                        <th>Required By</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                    @php
                                        $statusColors = [
                                            'Draft'      => 'secondary',
                                            'Confirmed'  => 'primary',
                                            'Processing' => 'warning',
                                            'Dispatched' => 'success',
                                            'Cancelled'  => 'danger',
                                        ];
                                    @endphp
                                    <tr class="single-item">
                                        <td>
                                            <a href="{{ route('customer-orders.show', $order) }}" class="fw-bold text-primary">
                                                {{ $order->order_code }}
                                            </a>
                                        </td>
                                        <td>{{ $order->customer->customer_name }}</td>
                                        <td>{{ $order->brand?->brand_name ?? '—' }}</td>
                                        <td>{{ $order->order_date->format('d M Y') }}</td>
                                        <td>{{ $order->required_by?->format('d M Y') ?? '—' }}</td>
                                        <td>{{ $order->items_count ?? $order->items->count() }}</td>
                                        <td>
                                            <span class="badge bg-soft-{{ $statusColors[$order->status] ?? 'secondary' }} text-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('customer-orders.show', $order) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('customer-orders.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('customer-orders.edit', $order) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('customer-orders.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('customer-orders.destroy', $order) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this order?')">
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
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="feather-clipboard fs-1 d-block mb-2"></i>
                                            No customer orders found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($orders->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }}</small>
                        {{ $orders->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
