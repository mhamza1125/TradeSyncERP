@extends('index')

@section('title', 'Customer: {{ $customer->customer_name }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Customers</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.customers.index') }}">Customers</a></li>
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
                    <a href="{{ route('masters.customers.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    @can('customers.edit')
                    <a href="{{ route('masters.customers.edit', $customer) }}" class="btn btn-primary">
                        <i class="feather-edit me-2"></i><span>Edit</span>
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
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            {{-- Profile Card --}}
            <div class="col-xxl-4 col-xl-5">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="mb-4 text-center">
                            <div class="wd-100 ht-100 mx-auto mb-3 avatar-text avatar-lg bg-soft-primary text-primary fs-2 rounded-circle d-flex align-items-center justify-content-center">
                                {{ strtoupper(substr($customer->customer_name, 0, 2)) }}
                            </div>
                            <a href="javascript:void(0);" class="fs-14 fw-bold d-block">{{ $customer->customer_name }}</a>
                            <a href="javascript:void(0);" class="fs-12 fw-normal text-muted d-block">{{ $customer->email ?? 'No email' }}</a>
                            @if($customer->status)
                                <span class="badge bg-soft-success text-success mt-2">Active</span>
                            @else
                                <span class="badge bg-soft-danger text-danger mt-2">Inactive</span>
                            @endif
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-user"></i>Contact Person</span>
                                <span class="fw-semibold">{{ $customer->contact_person }}</span>
                            </li>
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-phone"></i>Phone</span>
                                <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                            </li>
                            @if($customer->email)
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-mail"></i>Email</span>
                                <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                            </li>
                            @endif
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-dollar-sign"></i>Currency</span>
                                <span>{{ $customer->currency?->currency_code ?? '—' }}</span>
                            </li>
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-credit-card"></i>Opening Bal.</span>
                                <span class="fw-semibold">{{ number_format($customer->opening_balance, 2) }} {{ $customer->currency?->currency_code ?? '' }}</span>
                            </li>
                            @if($customer->address)
                            <li class="hstack justify-content-between mb-0">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-map-pin"></i>Address</span>
                                <span class="text-end" style="max-width:180px">{{ $customer->address }}</span>
                            </li>
                            @endif
                        </ul>
                        <div class="d-flex gap-2 text-center pt-4">
                            @can('customers.delete')
                            <form action="{{ route('masters.customers.destroy', $customer) }}" method="POST"
                                  class="w-50" onsubmit="return confirm('Delete this customer?')">
                                @csrf
                                @method('DELETE')
                                <button class="w-100 btn btn-light-brand" type="submit">
                                    <i class="feather-trash-2 me-2"></i>Delete
                                </button>
                            </form>
                            @endcan
                            @can('customers.edit')
                            <a href="{{ route('masters.customers.edit', $customer) }}" class="w-50 btn btn-primary">
                                <i class="feather-edit me-2"></i>Edit
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs Column --}}
            <div class="col-xxl-8 col-xl-7">
                <div class="card border-top-0">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs" id="customerTab" role="tablist">
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="tab" data-bs-target="#overviewTab" role="tab">Overview</a>
                            </li>
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#paymentsTab" role="tab">Payments</a>
                            </li>
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#activityTab" role="tab">Activity Log</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        {{-- Overview Tab --}}
                        <div class="tab-pane fade show active p-4" id="overviewTab" role="tabpanel">
                            <div class="mb-5">
                                <div class="mb-4 d-flex align-items-center justify-content-between">
                                    <h5 class="fw-bold mb-0">Customer Details:</h5>
                                    @can('customers.edit')
                                    <a href="{{ route('masters.customers.edit', $customer) }}" class="btn btn-sm btn-light-brand">Edit</a>
                                    @endcan
                                </div>
                                <div class="row g-0 mb-3">
                                    <div class="col-sm-5 text-muted">Customer Name:</div>
                                    <div class="col-sm-7 fw-semibold">{{ $customer->customer_name }}</div>
                                </div>
                                <div class="row g-0 mb-3">
                                    <div class="col-sm-5 text-muted">Contact Person:</div>
                                    <div class="col-sm-7 fw-semibold">{{ $customer->contact_person }}</div>
                                </div>
                                <div class="row g-0 mb-3">
                                    <div class="col-sm-5 text-muted">Phone:</div>
                                    <div class="col-sm-7 fw-semibold">{{ $customer->phone }}</div>
                                </div>
                                <div class="row g-0 mb-3">
                                    <div class="col-sm-5 text-muted">Email:</div>
                                    <div class="col-sm-7 fw-semibold">{{ $customer->email ?? '—' }}</div>
                                </div>
                                <div class="row g-0 mb-3">
                                    <div class="col-sm-5 text-muted">Address:</div>
                                    <div class="col-sm-7 fw-semibold">{{ $customer->address ?? '—' }}</div>
                                </div>
                                <div class="row g-0 mb-3">
                                    <div class="col-sm-5 text-muted">Currency:</div>
                                    <div class="col-sm-7 fw-semibold">{{ $customer->currency?->currency_code ?? '—' }}</div>
                                </div>
                                <div class="row g-0 mb-3">
                                    <div class="col-sm-5 text-muted">Opening Balance:</div>
                                    <div class="col-sm-7 fw-semibold">
                                        {{ number_format($customer->opening_balance, 2) }} {{ $customer->currency?->currency_code ?? '' }}
                                    </div>
                                </div>
                                <div class="row g-0 mb-3">
                                    <div class="col-sm-5 text-muted">Status:</div>
                                    <div class="col-sm-7">
                                        @if($customer->status)
                                            <span class="badge bg-soft-success text-success">Active</span>
                                        @else
                                            <span class="badge bg-soft-danger text-danger">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row g-0">
                                    <div class="col-sm-5 text-muted">Created At:</div>
                                    <div class="col-sm-7 fw-semibold">{{ $customer->created_at->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Payments Tab --}}
                        <div class="tab-pane fade p-4" id="paymentsTab" role="tabpanel">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0">Recent Payments:</h5>
                                @can('customer-payments.index')
                                <a href="{{ route('customer-payments.index', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-light-brand">View All</a>
                                @endcan
                            </div>
                            @if($customer->payments && $customer->payments->count())
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Invoice Ref</th>
                                            <th>Currency</th>
                                            <th>Received (FC)</th>
                                            <th>PKR Received</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->payments as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_date }}</td>
                                            <td>{{ $payment->invoice_reference ?? '—' }}</td>
                                            <td>{{ $payment->foreign_currency }}</td>
                                            <td>{{ number_format($payment->received_fc, 2) }}</td>
                                            <td class="fw-semibold">{{ number_format($payment->actual_pkr_received, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted text-center py-4">No payment records found.</p>
                            @endif
                        </div>

                        {{-- Activity Tab --}}
                        <div class="tab-pane fade p-4" id="activityTab" role="tabpanel">
                            <div class="mb-4">
                                <h5 class="fw-bold mb-0">Activity Log:</h5>
                            </div>
                            @php $activities = $customer->activities ?? collect(); @endphp
                            @if($activities->count())
                            <div class="timeline">
                                @foreach($activities as $activity)
                                <div class="d-flex gap-3 mb-4">
                                    <div class="avatar-text avatar-sm bg-soft-primary text-primary flex-shrink-0">
                                        <i class="feather-activity"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $activity->description }}</div>
                                        <small class="text-muted">
                                            by {{ optional($activity->causer)->name ?? 'System' }}
                                            — {{ $activity->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-muted text-center py-4">No activity recorded yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attachment Panel --}}
        @include('partials.attachment-panel', [
            'attachEntity'     => $customer,
            'attachEntityType' => 'customers',
            'attachLabel'      => 'Customer Attachments',
        ])
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection
