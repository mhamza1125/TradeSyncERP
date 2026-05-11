@extends('index')

@section('title', 'Vendor: {{ $vendor->vendor_name }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Vendors</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.vendors.index') }}">Vendors</a></li>
                <li class="breadcrumb-item">{{ $vendor->vendor_name }}</li>
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
                    <a href="{{ route('masters.vendors.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    @can('vendors.edit')
                    <a href="{{ route('masters.vendors.edit', $vendor) }}" class="btn btn-primary">
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
            {{-- Profile Card --}}
            <div class="col-xxl-4 col-xl-5">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="mb-4 text-center">
                            <div class="wd-100 ht-100 mx-auto mb-3 avatar-text avatar-lg bg-soft-warning text-warning fs-2 rounded-circle d-flex align-items-center justify-content-center">
                                {{ strtoupper(substr($vendor->vendor_name, 0, 2)) }}
                            </div>
                            <a href="javascript:void(0);" class="fs-14 fw-bold d-block">{{ $vendor->vendor_name }}</a>
                            <span class="fs-12 fw-normal text-muted d-block">{{ $vendor->company_name }}</span>
                            @if($vendor->status)
                                <span class="badge bg-soft-success text-success mt-2">Active</span>
                            @else
                                <span class="badge bg-soft-danger text-danger mt-2">Inactive</span>
                            @endif
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-phone"></i>Phone</span>
                                <a href="tel:{{ $vendor->phone }}">{{ $vendor->phone }}</a>
                            </li>
                            @if($vendor->email)
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-mail"></i>Email</span>
                                <a href="mailto:{{ $vendor->email }}">{{ $vendor->email }}</a>
                            </li>
                            @endif
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-clock"></i>Payment Terms</span>
                                <span>{{ $vendor->payment_terms ?? '—' }}</span>
                            </li>
                            <li class="hstack justify-content-between mb-0">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-credit-card"></i>Opening Bal.</span>
                                <span class="fw-semibold">{{ number_format($vendor->opening_balance, 2) }}</span>
                            </li>
                        </ul>
                        <div class="d-flex gap-2 pt-4">
                            @can('vendors.delete')
                            <form action="{{ route('masters.vendors.destroy', $vendor) }}" method="POST"
                                  class="w-50" onsubmit="return confirm('Delete this vendor?')">
                                @csrf @method('DELETE')
                                <button class="w-100 btn btn-light-brand" type="submit">
                                    <i class="feather-trash-2 me-2"></i>Delete
                                </button>
                            </form>
                            @endcan
                            @can('vendors.edit')
                            <a href="{{ route('masters.vendors.edit', $vendor) }}" class="w-50 btn btn-primary">
                                <i class="feather-edit me-2"></i>Edit
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="col-xxl-8 col-xl-7">
                <div class="card border-top-0">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs" role="tablist">
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="tab" data-bs-target="#vendorOverview" role="tab">Overview</a>
                            </li>
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#vendorBills" role="tab">Bills</a>
                            </li>
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#vendorActivity" role="tab">Activity Log</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade show active p-4" id="vendorOverview" role="tabpanel">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0">Vendor Details:</h5>
                                @can('vendors.edit')
                                <a href="{{ route('masters.vendors.edit', $vendor) }}" class="btn btn-sm btn-light-brand">Edit</a>
                                @endcan
                            </div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Vendor Name:</div><div class="col-sm-7 fw-semibold">{{ $vendor->vendor_name }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Company Name:</div><div class="col-sm-7 fw-semibold">{{ $vendor->company_name }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Phone:</div><div class="col-sm-7 fw-semibold">{{ $vendor->phone }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Email:</div><div class="col-sm-7 fw-semibold">{{ $vendor->email ?? '—' }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Address:</div><div class="col-sm-7 fw-semibold">{{ $vendor->address ?? '—' }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Payment Terms:</div><div class="col-sm-7 fw-semibold">{{ $vendor->payment_terms ?? '—' }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Opening Balance:</div><div class="col-sm-7 fw-semibold">{{ number_format($vendor->opening_balance, 2) }}</div></div>
                            <div class="row g-0"><div class="col-sm-5 text-muted">Created At:</div><div class="col-sm-7 fw-semibold">{{ $vendor->created_at->format('d M Y') }}</div></div>
                        </div>

                        <div class="tab-pane fade p-4" id="vendorBills" role="tabpanel">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0">Recent Bills:</h5>
                                @can('vendor-bills.index')
                                <a href="{{ route('vendor-bills.index', ['vendor_id' => $vendor->id]) }}" class="btn btn-sm btn-light-brand">View All</a>
                                @endcan
                            </div>
                            @if($vendor->bills && $vendor->bills->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr><th>Bill #</th><th>Date</th><th>Total</th><th>Status</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($vendor->bills as $bill)
                                        <tr>
                                            <td><a href="{{ route('vendor-bills.show', $bill) }}" class="fw-semibold">{{ $bill->bill_number }}</a></td>
                                            <td>{{ $bill->bill_date }}</td>
                                            <td class="fw-semibold">{{ number_format($bill->total_amount, 2) }}</td>
                                            <td>
                                                @php $statusColors = ['Paid'=>'success','Unpaid'=>'warning','Partial'=>'info','Overdue'=>'danger']; @endphp
                                                <span class="badge bg-soft-{{ $statusColors[$bill->status] ?? 'secondary' }} text-{{ $statusColors[$bill->status] ?? 'secondary' }}">{{ $bill->status }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted text-center py-4">No bills found for this vendor.</p>
                            @endif
                        </div>

                        <div class="tab-pane fade p-4" id="vendorActivity" role="tabpanel">
                            <h5 class="fw-bold mb-4">Activity Log:</h5>
                            @php $activities = $vendor->activities ?? collect(); @endphp
                            @forelse($activities as $activity)
                            <div class="d-flex gap-3 mb-4">
                                <div class="avatar-text avatar-sm bg-soft-warning text-warning flex-shrink-0">
                                    <i class="feather-activity"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $activity->description }}</div>
                                    <small class="text-muted">by {{ optional($activity->causer)->name ?? 'System' }} — {{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @empty
                            <p class="text-muted text-center py-4">No activity recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
