@extends('index')

@section('title', $inspection->report_number . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">{{ $inspection->report_number }}</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.index') }}">Samples</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.inspections.index', $sample) }}">Inspections</a></li>
                <li class="breadcrumb-item">{{ $inspection->report_number }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('samples.inspections.index', $sample) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    @can('inspections.edit')
                    {{-- Shallow route: inspections.edit --}}
                    <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-primary">
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
            $ic      = ['Pass' => 'success', 'Fail' => 'danger', 'Pending' => 'warning'];
            $pfColor = ['Pass' => 'success', 'Fail' => 'danger'];
            $stColor = ['Approve' => 'success', 'Reject' => 'danger', 'Review' => 'warning'];
        @endphp

        <div class="row">
            <div class="col-xl-8">
                {{-- Header card --}}
                <div class="card stretch stretch-full mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Inspection Report</h5>
                        <span class="badge bg-soft-{{ $ic[$inspection->overall_status] ?? 'secondary' }} text-{{ $ic[$inspection->overall_status] ?? 'secondary' }} fs-13">
                            {{ $inspection->overall_status }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Report Number</div>
                                <div class="fw-semibold">{{ $inspection->report_number }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Inspection Date</div>
                                <div class="fw-semibold">{{ $inspection->inspection_date->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Inspection Type</div>
                                <div class="fw-semibold">{{ $inspection->inspectionType?->name ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Sample</div>
                                <div class="fw-semibold">
                                    <a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Customer</div>
                                <div class="fw-semibold">{{ $sample->customer->customer_name }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Supplier / Factory</div>
                                <div class="fw-semibold">{{ $sample->supplier?->name ?? '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted fs-12">Inspectors</div>
                                <div class="fw-semibold">
                                    @forelse($inspection->inspectors as $inspector)
                                        <span class="badge bg-soft-primary text-primary me-1">{{ $inspector->employee_name }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </div>
                            </div>
                            @if($inspection->remarks)
                            <div class="col-12">
                                <div class="text-muted fs-12">Remarks</div>
                                <div>{{ $inspection->remarks }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Results card --}}
                <div class="card stretch stretch-full">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Test Results ({{ $inspection->results->count() }})</h5>
                        @php
                            $passed = $inspection->results->where('pass_fail','Pass')->count();
                            $failed = $inspection->results->where('pass_fail','Fail')->count();
                        @endphp
                        <div class="d-flex gap-2">
                            @if($passed) <span class="badge bg-soft-success text-success">{{ $passed }} Pass</span> @endif
                            @if($failed) <span class="badge bg-soft-danger text-danger">{{ $failed }} Fail</span> @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($inspection->results->count())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Parameter</th>
                                        <th>Actual Result</th>
                                        <th>Pass / Fail</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Attachment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inspection->results as $i => $result)
                                    <tr>
                                        <td class="ps-3">{{ $i + 1 }}</td>
                                        <td class="fw-semibold">
                                            {{ $result->sampleTestingParameter?->parameter?->parameter_name ?? '—' }}
                                        </td>
                                        <td>{{ $result->actual_result ?? '—' }}</td>
                                        <td>
                                            @if($result->pass_fail)
                                            <span class="badge bg-soft-{{ $pfColor[$result->pass_fail] ?? 'secondary' }} text-{{ $pfColor[$result->pass_fail] ?? 'secondary' }}">
                                                {{ $result->pass_fail }}
                                            </span>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($result->status)
                                            <span class="badge bg-soft-{{ $stColor[$result->status] ?? 'secondary' }} text-{{ $stColor[$result->status] ?? 'secondary' }}">
                                                {{ $result->status }}
                                            </span>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $result->remarks ?? '—' }}</td>
                                        <td>
                                            @if($result->attachment)
                                            <a href="{{ asset('storage/'.$result->attachment) }}" target="_blank" class="btn btn-xs btn-light-brand">
                                                <i class="feather-paperclip me-1"></i> View
                                            </a>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4 text-muted">
                            <i class="feather-sliders fs-2 d-block mb-1"></i>
                            No test results recorded.
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header"><h5 class="card-title">Actions</h5></div>
                    <div class="card-body">
                        @can('inspections.edit')
                        <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-light-brand w-100 mb-3">
                            <i class="feather-edit-3 me-2"></i> Edit Inspection
                        </a>
                        @endcan
                        @can('inspections.create')
                        <a href="{{ route('samples.inspections.create', $sample) }}" class="btn btn-light-brand w-100 mb-3">
                            <i class="feather-clipboard me-2"></i> New Inspection
                        </a>
                        @endcan
                        @can('inspections.delete')
                        <form action="{{ route('inspections.destroy', $inspection) }}" method="POST"
                              onsubmit="return confirm('Delete inspection {{ $inspection->report_number }}?')">
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
