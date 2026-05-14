@extends('index')

@section('title', 'Inspections – ' . $sample->sample_code . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Inspections</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.index') }}">Samples</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a></li>
                <li class="breadcrumb-item">Inspections</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('samples.show', $sample) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back to Sample</span>
                    </a>
                    @can('inspections.create')
                    <a href="{{ route('samples.inspections.create', $sample) }}" class="btn btn-primary">
                        <i class="feather-clipboard me-2"></i><span>New Inspection</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div>
                        <div class="text-muted fs-12">Sample</div>
                        <div class="fw-semibold">{{ $sample->sample_code }}</div>
                    </div>
                    <div>
                        <div class="text-muted fs-12">Product</div>
                        <div class="fw-semibold">{{ $sample->product_name }}</div>
                    </div>
                    <div>
                        <div class="text-muted fs-12">Customer</div>
                        <div class="fw-semibold">{{ $sample->customer->customer_name }}</div>
                    </div>
                    @if($sample->supplier)
                    <div>
                        <div class="text-muted fs-12">Supplier / Factory</div>
                        <div class="fw-semibold">{{ $sample->supplier->name }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Report #</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Inspectors</th>
                                        <th>Results</th>
                                        <th>Pass</th>
                                        <th>Fail</th>
                                        <th>Overall</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inspections as $ins)
                                    @php
                                        $ic     = ['Pass' => 'success', 'Fail' => 'danger', 'Pending' => 'warning'];
                                        $passed = $ins->results->where('pass_fail', 'Pass')->count();
                                        $failed = $ins->results->where('pass_fail', 'Fail')->count();
                                    @endphp
                                    <tr>
                                        <td>
                                            {{-- Shallow route: inspections.show --}}
                                            <a href="{{ route('inspections.show', $ins) }}" class="fw-bold text-primary">
                                                {{ $ins->report_number }}
                                            </a>
                                        </td>
                                        <td>{{ $ins->inspection_date->format('d M Y') }}</td>
                                        <td>
                                            @if($ins->inspectionType)
                                            <span class="badge bg-soft-primary text-primary">{{ $ins->inspectionType->name }}</span>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($ins->inspectors->take(2) as $insp)
                                            <span class="badge bg-soft-secondary text-secondary me-1">{{ $insp->employee_name }}</span>
                                            @endforeach
                                            @if($ins->inspectors->count() > 2)
                                            <span class="text-muted fs-12">+{{ $ins->inspectors->count() - 2 }} more</span>
                                            @endif
                                        </td>
                                        <td>{{ $ins->results->count() }}</td>
                                        <td>
                                            @if($passed > 0)
                                            <span class="badge bg-soft-success text-success">{{ $passed }}</span>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($failed > 0)
                                            <span class="badge bg-soft-danger text-danger">{{ $failed }}</span>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-{{ $ic[$ins->overall_status] ?? 'secondary' }} text-{{ $ic[$ins->overall_status] ?? 'secondary' }}">
                                                {{ $ins->overall_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                {{-- Shallow route: inspections.show --}}
                                                <a href="{{ route('inspections.show', $ins) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('inspections.edit')
                                                        <li>
                                                            {{-- Shallow route: inspections.edit --}}
                                                            <a class="dropdown-item" href="{{ route('inspections.edit', $ins) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('inspections.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            {{-- Shallow route: inspections.destroy --}}
                                                            <form action="{{ route('inspections.destroy', $ins) }}" method="POST"
                                                                  onsubmit="return confirm('Delete inspection {{ $ins->report_number }}?')">
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
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="feather-clipboard fs-1 d-block mb-2"></i>
                                            No inspections recorded for this sample.
                                            @can('inspections.create')
                                            <div class="mt-2">
                                                <a href="{{ route('samples.inspections.create', $sample) }}" class="btn btn-sm btn-primary">
                                                    <i class="feather-plus me-1"></i> Start First Inspection
                                                </a>
                                            </div>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($inspections->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $inspections->firstItem() }}–{{ $inspections->lastItem() }} of {{ $inspections->total() }}</small>
                        {{ $inspections->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
