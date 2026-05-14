@extends('index')

@section('title', 'Movement Detail - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Movement Detail</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.index') }}">Samples</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.movements.index', $sample) }}">Movements</a></li>
                <li class="breadcrumb-item">Detail</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('samples.movements.index', $sample) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    @can('sample-movements.edit')
                    <a href="{{ route('samples.movements.edit', $movement) }}" class="btn btn-primary">
                        <i class="feather-edit me-2"></i><span>Update Return</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        @php $mc = ['Issued'=>'primary','Returned'=>'success','Overdue'=>'danger']; @endphp

        <div class="row">
            <div class="col-xl-8">
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Movement Record</h5>
                        <span class="badge bg-soft-{{ $mc[$movement->status] ?? 'secondary' }} text-{{ $mc[$movement->status] ?? 'secondary' }} fs-12">
                            {{ $movement->status }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Sample</div>
                                <div class="fw-semibold">
                                    <a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a>
                                    — {{ $sample->product_name }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Customer</div>
                                <div class="fw-semibold">{{ $sample->customer->customer_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Issue Date</div>
                                <div class="fw-semibold">{{ $movement->issue_date->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Moved By</div>
                                <div class="fw-semibold">
                                    <span class="badge bg-soft-secondary text-secondary me-1">{{ $movement->moved_by_type }}</span>
                                    ID: {{ $movement->moved_by_id }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Assigned To</div>
                                <div class="fw-semibold">
                                    <span class="badge bg-soft-info text-info me-1">{{ $movement->assigned_to_type }}</span>
                                    @if($movement->assigned_to_type === 'Employee')
                                        {{ $movement->movedByEmployee?->employee_name ?? 'ID: '.$movement->assigned_to_id }}
                                    @elseif($movement->assigned_to_type === 'Supplier')
                                        ID: {{ $movement->assigned_to_id }}
                                    @elseif($movement->assigned_to_type === 'Storage')
                                        Bay / Shelf #{{ $movement->assigned_to_id }}
                                    @else
                                        ID: {{ $movement->assigned_to_id }}
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Expected Return</div>
                                <div class="fw-semibold">{{ $movement->expected_return_date?->format('d M Y') ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Actual Return</div>
                                <div class="fw-semibold">
                                    @if($movement->actual_return_date)
                                        <span class="text-success">{{ $movement->actual_return_date->format('d M Y') }}</span>
                                    @else
                                        <span class="text-muted">Not yet returned</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Alert Days</div>
                                <div class="fw-semibold">{{ $movement->alert_days ? $movement->alert_days.' days' : '—' }}</div>
                            </div>
                            @if($movement->remarks)
                            <div class="col-12">
                                <div class="text-muted fs-12">Remarks</div>
                                <div>{{ $movement->remarks }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Actions</h5>
                    </div>
                    <div class="card-body">
                        @can('sample-movements.edit')
                        <a href="{{ route('samples.movements.edit', $movement) }}" class="btn btn-light-brand w-100 mb-3">
                            <i class="feather-edit-3 me-2"></i> Update Return
                        </a>
                        @endcan
                        @can('sample-movements.create')
                        <a href="{{ route('samples.movements.create', $sample) }}" class="btn btn-light-brand w-100 mb-3">
                            <i class="feather-send me-2"></i> Issue New Movement
                        </a>
                        @endcan
                        @can('sample-movements.delete')
                        <form action="{{ route('samples.movements.destroy', $movement) }}" method="POST"
                              onsubmit="return confirm('Delete this movement record?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-light-danger w-100" type="submit">
                                <i class="feather-trash-2 me-2"></i> Delete
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
