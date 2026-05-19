@extends('index')

@section('title', $inspection->report_number . ' — TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Inspection</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                <li class="breadcrumb-item">{{ $inspection->report_number }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspections.index') }}" class="btn btn-icon btn-light-brand">
                    <i class="feather-arrow-left"></i>
                </a>
                @can('inspections.edit')
                <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-light-brand">
                    <i class="feather-edit-3 me-2"></i>Edit
                </a>
                @endcan
                @can('inspections.delete')
                <form action="{{ route('inspections.destroy', $inspection) }}" method="POST"
                      onsubmit="return confirm('Delete this inspection?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="feather-trash-2 me-2"></i>Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">

            {{-- ── Left column ──────────────────────────────────────────── --}}
            <div class="col-xl-8">

                {{-- Header card --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div>
                                <h4 class="mb-1">{{ $inspection->report_number }}</h4>
                                <span class="text-muted fs-13">{{ $inspection->inspection_date->format('d M Y') }}</span>
                            </div>
                            @php $ic = ['Pass'=>'success','Fail'=>'danger','Pending'=>'warning']; @endphp
                            <span class="badge bg-soft-{{ $ic[$inspection->overall_status] ?? 'secondary' }} text-{{ $ic[$inspection->overall_status] ?? 'secondary' }} fs-13 ms-auto">
                                {{ $inspection->overall_status }}
                            </span>
                        </div>
                        @if($inspection->remarks)
                        <p class="text-muted mb-0">{{ $inspection->remarks }}</p>
                        @endif
                    </div>
                </div>

                {{-- Samples --}}
                @if($inspection->samples->count())
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Samples Being Tested</h5></div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($inspection->samples as $s)
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <a href="{{ route('samples.show', $s) }}" class="fw-bold text-primary">{{ $s->sample_code }}</a>
                                    @if($s->product_name)
                                    <div class="text-muted fs-12">{{ $s->product_name }}</div>
                                    @endif
                                    @if($s->customer)
                                    <div class="text-muted fs-12">{{ $s->customer->customer_name }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Customer Orders --}}
                @if($inspection->customerOrders->count())
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Linked Customer Orders</h5></div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($inspection->customerOrders as $order)
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <a href="{{ route('customer-orders.show', $order) }}" class="fw-bold text-primary">{{ $order->order_code }}</a>
                                    @if($order->customer)
                                    <div class="text-muted fs-12">{{ $order->customer->customer_name }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── Inspection Runs ─────────────────────────────────── --}}
                @forelse($inspection->runs as $runIdx => $run)
                @php
                    $rc = ['Pass'=>'success','Fail'=>'danger','Rejected'=>'warning','Pending'=>'secondary'];
                    // Group results: sample_id → [results]
                    $bySample = $run->results->groupBy('sample_id');
                @endphp
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center gap-2">
                        <h5 class="card-title mb-0">Run {{ $runIdx + 1 }}</h5>
                        @if($run->inspectionType)
                        <span class="badge bg-soft-primary text-primary">{{ $run->inspectionType->name }}</span>
                        @endif
                        @if($run->remarks)
                        <span class="text-muted fs-12">{{ $run->remarks }}</span>
                        @endif
                        @can('inspections.edit')
                        <div class="ms-auto d-flex gap-2">
                            <a href="{{ route('inspections.runs.edit', [$inspection, $run]) }}"
                               class="btn btn-sm btn-light-brand">
                                <i class="feather-eye me-1"></i>View
                            </a>
                            <a href="{{ route('inspections.runs.edit', [$inspection, $run]) }}"
                               class="btn btn-sm btn-primary">
                                <i class="feather-edit-2 me-1"></i>Edit
                            </a>
                        </div>
                        @endcan
                    </div>

                    @if($run->results->isEmpty())
                    <div class="card-body">
                        <p class="text-muted mb-0">No results recorded for this run.</p>
                    </div>
                    @else
                    <div class="card-body p-0">
                        @foreach($bySample as $sampleId => $results)
                        @php $sample = $results->first()->sample; @endphp
                        <div class="px-4 pt-3 pb-1">
                            <div class="fw-semibold fs-13 mb-2">
                                <i class="feather-package me-1 text-muted"></i>
                                @if($sample)
                                    <a href="{{ route('samples.show', $sample) }}" class="text-dark">{{ $sample->sample_code }}</a>
                                    <span class="text-muted fw-normal ms-1">— {{ $sample->product_name }}</span>
                                @else
                                    <span class="text-muted fst-italic">Sample removed</span>
                                @endif
                            </div>
                        </div>
                        <div class="table-responsive px-4 pb-3">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fs-12">Testing Parameter</th>
                                        <th class="fs-12" style="width:110px">Status</th>
                                        <th class="fs-12">Defect / Remarks</th>
                                        <th class="fs-12" style="width:80px">Images</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($results as $result)
                                <tr>
                                    <td class="fs-12 align-middle">
                                        {{ optional($result->testingParameter)->parameter_name ?? '—' }}
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge bg-soft-{{ $rc[$result->status] ?? 'secondary' }} text-{{ $rc[$result->status] ?? 'secondary' }} fs-11">
                                            {{ $result->status }}
                                        </span>
                                    </td>
                                    <td class="fs-12 align-middle">
                                        @if($result->status === 'Rejected' && $result->defect)
                                        <div class="text-danger fw-semibold">{{ $result->defect->defect_name }}</div>
                                        @if($result->defect->corrective_action)
                                        <div class="text-muted">{{ $result->defect->corrective_action }}</div>
                                        @endif
                                        @endif
                                        @if($result->remarks)
                                        <div class="text-muted {{ $result->status === 'Rejected' ? 'mt-1' : '' }}">{{ $result->remarks }}</div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        @php $imgs = $result->attachments->filter(fn($a) => $a->isImage()); @endphp
                                        @if($imgs->count())
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            @foreach($imgs->take(3) as $img)
                                            <a href="{{ $img->url }}" target="_blank">
                                                <img src="{{ $img->url }}" alt="" class="rounded" style="width:36px;height:36px;object-fit:cover;">
                                            </a>
                                            @endforeach
                                            @if($imgs->count() > 3)
                                            <span class="text-muted fs-11">+{{ $imgs->count() - 3 }}</span>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-muted fs-11">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(!$loop->last)<hr class="my-0">@endif
                        @endforeach
                    </div>
                    @endif

                    @if($run->sampleMovement)
                    <div class="card-footer text-muted fs-12">
                        <i class="feather-package me-1"></i>
                        Sample movement logged — Status: {{ $run->sampleMovement->status }}
                    </div>
                    @endif
                </div>
                @empty
                <div class="card mb-4">
                    <div class="card-body text-center text-muted py-4">No inspection runs recorded.</div>
                </div>
                @endforelse

            </div>

            {{-- ── Right column ──────────────────────────────────────────── --}}
            <div class="col-xl-4">

                {{-- Inspectors --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Inspectors</h5></div>
                    <div class="card-body">
                        @forelse($inspection->inspectors as $e)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-text avatar-md bg-soft-primary text-primary rounded-circle fw-bold">
                                {{ strtoupper(substr($e->employee_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $e->employee_name }}</div>
                                @if($e->designation)<div class="text-muted fs-12">{{ $e->designation }}</div>@endif
                            </div>
                        </div>
                        @empty
                        <p class="text-muted mb-0">No inspectors assigned.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Summary --}}
                @if($inspection->runs->count())
                @php
                    $allResults   = $inspection->runs->flatMap(fn($r) => $r->results);
                    $totalCount   = $allResults->count();
                    $passCount    = $allResults->where('status', 'Pass')->count();
                    $failCount    = $allResults->where('status', 'Fail')->count();
                    $rejCount     = $allResults->where('status', 'Rejected')->count();
                    $pendingCount = $allResults->where('status', 'Pending')->count();
                @endphp
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Results Summary</h5></div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Total checks</span>
                                <strong>{{ $totalCount }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-success"><i class="feather-check me-1"></i>Pass</span>
                                <strong class="text-success">{{ $passCount }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-danger"><i class="feather-x me-1"></i>Fail</span>
                                <strong class="text-danger">{{ $failCount }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-warning"><i class="feather-alert-triangle me-1"></i>Rejected</span>
                                <strong class="text-warning">{{ $rejCount }}</strong>
                            </li>
                            @if($pendingCount)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Pending</span>
                                <strong>{{ $pendingCount }}</strong>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
