@extends('index')

@section('title', 'Sample: {{ $sample->sample_code }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Samples</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.index') }}">Samples</a></li>
                <li class="breadcrumb-item">{{ $sample->sample_code }}</li>
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
                    <a href="{{ route('samples.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    @can('samples.edit')
                    <a href="{{ route('samples.edit', $sample) }}" class="btn btn-primary">
                        <i class="feather-edit me-2"></i><span>Edit</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        @php
            $priorityColors = ['Low'=>'secondary','Medium'=>'info','High'=>'warning','Urgent'=>'danger'];
            $statusColors   = ['Received'=>'primary','In Testing'=>'warning','Completed'=>'success','Returned'=>'secondary'];
        @endphp

        <div class="row">
            {{-- Summary Card --}}
            <div class="col-xxl-4 col-xl-5">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="avatar-text avatar-lg bg-soft-primary text-primary fs-3 rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                                <i class="feather-package"></i>
                            </div>
                            <div class="fs-16 fw-bold">{{ $sample->sample_code }}</div>
                            <div class="text-muted">{{ $sample->product_name }}</div>
                            <div class="mt-2 d-flex justify-content-center gap-2">
                                <span class="badge bg-soft-{{ $priorityColors[$sample->priority_level] ?? 'secondary' }} text-{{ $priorityColors[$sample->priority_level] ?? 'secondary' }}">
                                    {{ $sample->priority_level }}
                                </span>
                                <span class="badge bg-soft-{{ $statusColors[$sample->status] ?? 'secondary' }} text-{{ $statusColors[$sample->status] ?? 'secondary' }}">
                                    {{ $sample->status }}
                                </span>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted hstack gap-3"><i class="feather-users"></i>Customer</span>
                                <span class="fw-semibold">{{ $sample->customer->customer_name }}</span>
                            </li>
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted hstack gap-3"><i class="feather-tag"></i>Brand</span>
                                <span>{{ $sample->brand->brand_name }}</span>
                            </li>
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted hstack gap-3"><i class="feather-grid"></i>Category</span>
                                <span>{{ $sample->category->category_name }}</span>
                            </li>
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted hstack gap-3"><i class="feather-hash"></i>Quantity</span>
                                <span class="fw-semibold">{{ $sample->quantity }}</span>
                            </li>
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted hstack gap-3"><i class="feather-calendar"></i>Received</span>
                                <span>{{ \Carbon\Carbon::parse($sample->receive_date)->format('d M Y') }}</span>
                            </li>
                            @if($sample->shipment_reference)
                            <li class="hstack justify-content-between mb-0">
                                <span class="text-muted hstack gap-3"><i class="feather-anchor"></i>Shipment Ref</span>
                                <span>{{ $sample->shipment_reference }}</span>
                            </li>
                            @endif
                        </ul>
                        <div class="d-flex gap-2 pt-4">
                            @can('samples.delete')
                            <form action="{{ route('samples.destroy', $sample) }}" method="POST"
                                  class="w-50" onsubmit="return confirm('Delete this sample?')">
                                @csrf @method('DELETE')
                                <button class="w-100 btn btn-light-brand" type="submit">
                                    <i class="feather-trash-2 me-2"></i>Delete
                                </button>
                            </form>
                            @endcan
                            @can('samples.edit')
                            <a href="{{ route('samples.edit', $sample) }}" class="w-50 btn btn-primary">
                                <i class="feather-edit me-2"></i>Edit
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Tabs --}}
            <div class="col-xxl-8 col-xl-7">
                <div class="card border-top-0">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs" role="tablist">
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="tab" data-bs-target="#sampleOverview" role="tab">Details</a>
                            </li>
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#sampleMovements" role="tab">Movements</a>
                            </li>
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#sampleInspections" role="tab">Inspections</a>
                            </li>
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#sampleParameters" role="tab">Parameters</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        {{-- Details Tab --}}
                        <div class="tab-pane fade show active p-4" id="sampleOverview" role="tabpanel">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0">Sample Details:</h5>
                            </div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Sample Code:</div><div class="col-sm-7 fw-semibold">{{ $sample->sample_code }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Product Name:</div><div class="col-sm-7 fw-semibold">{{ $sample->product_name }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Customer:</div><div class="col-sm-7 fw-semibold">{{ $sample->customer->customer_name }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Brand:</div><div class="col-sm-7 fw-semibold">{{ $sample->brand->brand_name }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Category:</div><div class="col-sm-7 fw-semibold">{{ $sample->category->category_name }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Quantity:</div><div class="col-sm-7 fw-semibold">{{ $sample->quantity }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Receive Date:</div><div class="col-sm-7 fw-semibold">{{ \Carbon\Carbon::parse($sample->receive_date)->format('d M Y') }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Shipment Ref:</div><div class="col-sm-7 fw-semibold">{{ $sample->shipment_reference ?? '—' }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Alert Days:</div><div class="col-sm-7 fw-semibold">{{ $sample->alert_days }}</div></div>
                            @if($sample->remarks)
                            <div class="row g-0"><div class="col-sm-5 text-muted">Remarks:</div><div class="col-sm-7">{{ $sample->remarks }}</div></div>
                            @endif
                        </div>

                        {{-- Movements Tab --}}
                        <div class="tab-pane fade p-4" id="sampleMovements" role="tabpanel">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0">Movement History:</h5>
                            </div>
                            @if($sample->movements && $sample->movements->count())
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr><th>Date</th><th>From</th><th>To</th><th>Qty</th><th>Notes</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sample->movements as $m)
                                        <tr>
                                            <td>{{ $m->movement_date }}</td>
                                            <td>{{ $m->from_location ?? '—' }}</td>
                                            <td>{{ $m->to_location ?? '—' }}</td>
                                            <td>{{ $m->quantity }}</td>
                                            <td>{{ $m->notes ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted text-center py-4">No movements recorded.</p>
                            @endif
                        </div>

                        {{-- Inspections Tab --}}
                        <div class="tab-pane fade p-4" id="sampleInspections" role="tabpanel">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0">Inspections:</h5>
                            </div>
                            @if($sample->inspections && $sample->inspections->count())
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr><th>Date</th><th>Inspector</th><th>Overall Status</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sample->inspections as $inspection)
                                        @php $ic = ['Pass'=>'success','Fail'=>'danger','Pending'=>'warning']; @endphp
                                        <tr>
                                            <td>{{ $inspection->inspection_date }}</td>
                                            <td>{{ $inspection->inspector ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-soft-{{ $ic[$inspection->overall_status] ?? 'secondary' }} text-{{ $ic[$inspection->overall_status] ?? 'secondary' }}">
                                                    {{ $inspection->overall_status }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted text-center py-4">No inspections recorded.</p>
                            @endif
                        </div>

                        {{-- Testing Parameters Tab --}}
                        <div class="tab-pane fade p-4" id="sampleParameters" role="tabpanel">
                            <div class="mb-4">
                                <h5 class="fw-bold mb-0">Testing Parameters:</h5>
                            </div>
                            @if($sample->testingParameters && $sample->testingParameters->count())
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr><th>Parameter</th><th>Required Value</th><th>Result</th><th>Status</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sample->testingParameters as $tp)
                                        @php $tc = ['Pass'=>'success','Fail'=>'danger','Pending'=>'warning']; @endphp
                                        <tr>
                                            <td>{{ optional($tp->parameter)->parameter_name ?? '—' }}</td>
                                            <td>{{ $tp->required_value ?? '—' }}</td>
                                            <td>{{ $tp->result ?? '—' }}</td>
                                            <td>
                                                <span class="badge bg-soft-{{ $tc[$tp->status] ?? 'secondary' }} text-{{ $tc[$tp->status] ?? 'secondary' }}">
                                                    {{ $tp->status ?? 'Pending' }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-muted text-center py-4">No testing parameters assigned.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
