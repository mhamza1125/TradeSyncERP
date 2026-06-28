@extends('index')

@section('title', $supplier->name . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">{{ $supplier->name }}</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.suppliers.index') }}">Suppliers</a></li>
                <li class="breadcrumb-item">{{ $supplier->name }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('masters.suppliers.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <a href="{{ route('masters.suppliers.export-single-pdf', $supplier) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i><span>Export PDF</span>
                    </a>
                    @can('suppliers.edit')
                    <a href="{{ route('masters.suppliers.edit', $supplier) }}" class="btn btn-primary">
                        <i class="feather-edit me-2"></i><span>Edit</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')
        <div class="row">
            <div class="col-xl-8">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-header"><h5 class="card-title">Supplier Details</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Name</div>
                                <div class="fw-semibold">{{ $supplier->name }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Phone</div>
                                <div class="fw-semibold">{{ $supplier->phone ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Email</div>
                                <div class="fw-semibold">{{ $supplier->email ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">City</div>
                                <div class="fw-semibold">{{ $supplier->city ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Country</div>
                                <div class="fw-semibold">{{ $supplier->country ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Status</div>
                                <div>
                                    @if($supplier->status)
                                        <span class="badge bg-soft-success text-success">Active</span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            @if($supplier->address)
                            <div class="col-12">
                                <div class="text-muted fs-12">Address</div>
                                <div>{{ $supplier->address }}</div>
                            </div>
                            @endif
                            @if($supplier->remarks)
                            <div class="col-12">
                                <div class="text-muted fs-12">Remarks</div>
                                <div>{{ $supplier->remarks }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Linked Samples --}}
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Samples ({{ $supplier->samples->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($supplier->samples->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Sample Code</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->samples as $sample)
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a>
                                        </td>
                                        <td>{{ $sample->product_name }}</td>
                                        <td>{{ $sample->customer?->customer_name ?? '—' }}</td>
                                        <td><span class="badge bg-soft-secondary text-secondary">{{ $sample->status }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4 text-muted">No samples linked yet.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header"><h5 class="card-title">Linked Customers ({{ $supplier->customers->count() }})</h5></div>
                    <div class="card-body">
                        @forelse($supplier->customers as $customer)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-text avatar-md bg-soft-primary text-primary me-3">
                                {{ strtoupper(substr($customer->customer_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $customer->customer_name }}</div>
                                <small class="text-muted">{{ $customer->phone }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted mb-0">No customers linked.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
