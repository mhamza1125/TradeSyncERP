@extends('index')

@section('title', '{{ $customerOrder->order_code }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">{{ $customerOrder->order_code }}</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer-orders.index') }}">Customer Orders</a></li>
                <li class="breadcrumb-item">{{ $customerOrder->order_code }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('customer-orders.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    @can('customer-orders.edit')
                    <a href="{{ route('customer-orders.edit', $customerOrder) }}" class="btn btn-primary">
                        <i class="feather-edit-3 me-2"></i><span>Edit</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            {{-- Order Summary --}}
            <div class="col-xl-8">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Order Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Order Code</div>
                                <div class="fw-semibold">{{ $customerOrder->order_code }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Customer</div>
                                <div class="fw-semibold">{{ $customerOrder->customer->customer_name }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Brand</div>
                                <div class="fw-semibold">{{ $customerOrder->brand?->brand_name ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Order Date</div>
                                <div class="fw-semibold">{{ $customerOrder->order_date->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Required By</div>
                                <div class="fw-semibold">{{ $customerOrder->required_by?->format('d M Y') ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Status</div>
                                @php
                                $statusColors = ['Draft'=>'secondary','Confirmed'=>'primary','Processing'=>'warning','Dispatched'=>'success','Cancelled'=>'danger'];
                                @endphp
                                <span class="badge bg-soft-{{ $statusColors[$customerOrder->status] ?? 'secondary' }} text-{{ $statusColors[$customerOrder->status] ?? 'secondary' }}">
                                    {{ $customerOrder->status }}
                                </span>
                            </div>
                            @if($customerOrder->remarks)
                            <div class="col-12">
                                <div class="text-muted fs-12">Remarks</div>
                                <div>{{ $customerOrder->remarks }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Requested Items --}}
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Requested Items ({{ $customerOrder->items->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Qty</th>
                                        <th>Unit</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customerOrder->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="fw-semibold">{{ $item->product_name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->unit ?? '—' }}</td>
                                        <td class="text-muted">{{ $item->description ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Side Panel --}}
            <div class="col-xl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Actions</h5>
                    </div>
                    <div class="card-body">
                        @can('customer-orders.edit')
                        <a href="{{ route('customer-orders.edit', $customerOrder) }}" class="btn btn-light-brand w-100 mb-3">
                            <i class="feather-edit-3 me-2"></i> Edit Order
                        </a>
                        @endcan
                        @can('customer-orders.delete')
                        <form action="{{ route('customer-orders.destroy', $customerOrder) }}" method="POST"
                              onsubmit="return confirm('Delete this order?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-light-danger w-100" type="submit">
                                <i class="feather-trash-2 me-2"></i> Delete Order
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
