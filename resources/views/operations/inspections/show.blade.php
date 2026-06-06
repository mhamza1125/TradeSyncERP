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
                <a href="{{ route('inspections.runs.create', $inspection) }}" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>Add Run
                </a>
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
                        <div class="d-flex align-items-start gap-3 flex-wrap">
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ $inspection->report_number }}</h4>
                                <div class="text-muted fs-13 mb-2">{{ $inspection->inspection_date->format('d M Y') }}</div>
                                @if($inspection->inspectionType)
                                    <span class="badge bg-soft-primary text-primary fs-12">
                                        <i class="feather-tag me-1"></i>{{ $inspection->inspectionType->name }}
                                    </span>
                                @endif
                            </div>
                            @php $ic = ['Pass'=>'success','Fail'=>'danger','Pending'=>'warning']; @endphp
                            <span class="badge bg-soft-{{ $ic[$inspection->overall_status] ?? 'secondary' }} text-{{ $ic[$inspection->overall_status] ?? 'secondary' }} fs-14 px-3 py-2">
                                {{ $inspection->overall_status }}
                            </span>
                        </div>
                        @if($inspection->remarks)
                        <p class="text-muted mb-0 mt-3 pt-3 border-top">{{ $inspection->remarks }}</p>
                        @endif
                    </div>
                </div>

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
                    $rc = ['Pass'=>'success','Fail'=>'danger','Conditional'=>'warning','Pending'=>'secondary'];
                    $sectionStats = [
                        'complete' => $run->runSections->where('status', 'complete')->count(),
                        'pending'  => $run->runSections->where('status', 'pending')->count(),
                        'na'       => $run->runSections->where('status', 'na')->count(),
                        'total'    => $run->runSections->count(),
                    ];
                @endphp
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center gap-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold fs-14">Run #{{ $run->run_number }}</span>
                            <span class="badge bg-soft-{{ $rc[$run->verdict] ?? 'secondary' }} text-{{ $rc[$run->verdict] ?? 'secondary' }}">
                                {{ $run->verdict }}
                            </span>
                        </div>
                        <div class="ms-auto d-flex gap-2">
                            @can('inspections.edit')
                            <a href="{{ route('inspections.runs.edit', [$inspection, $run]) }}"
                               class="btn btn-sm btn-primary">
                                <i class="feather-edit-2 me-1"></i>Edit Run
                            </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Sample info --}}
                        @if($run->sample)
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded">
                            @if($run->sample->main_image)
                                <img src="{{ Storage::url($run->sample->main_image) }}"
                                     class="rounded border"
                                     style="width:48px;height:48px;object-fit:cover;">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-soft-primary text-primary rounded"
                                     style="width:48px;height:48px;flex-shrink:0">
                                    <i class="feather-package"></i>
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('samples.show', $run->sample) }}" class="fw-semibold text-primary">
                                    {{ $run->sample->sample_code }}
                                </a>
                                <div class="text-muted fs-12">{{ $run->sample->product_name }}</div>
                                <div class="d-flex gap-2 mt-1">
                                    @if($run->sample->customer)
                                        <span class="badge bg-soft-secondary text-secondary fs-11">
                                            {{ $run->sample->customer->customer_name }}
                                        </span>
                                    @endif
                                    @if($run->sample->category)
                                        <span class="badge bg-soft-primary text-primary fs-11">
                                            {{ $run->sample->category->category_name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Section progress --}}
                        @if($sectionStats['total'])
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fs-13 fw-semibold text-muted">Section Progress</span>
                                <span class="fs-12 text-muted">
                                    {{ $sectionStats['complete'] }}/{{ $sectionStats['total'] }} complete
                                </span>
                            </div>
                            <div class="progress" style="height:6px">
                                @php
                                    $pct = $sectionStats['total'] > 0
                                        ? round(($sectionStats['complete'] / $sectionStats['total']) * 100)
                                        : 0;
                                @endphp
                                <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                            </div>
                            <div class="d-flex gap-3 mt-2 fs-12">
                                <span class="text-success">{{ $sectionStats['complete'] }} done</span>
                                <span class="text-warning">{{ $sectionStats['pending'] }} pending</span>
                                @if($sectionStats['na'])<span class="text-muted">{{ $sectionStats['na'] }} N/A</span>@endif
                            </div>
                        </div>
                        @endif

                        {{-- Section list --}}
                        @if($run->runSections->count())
                        <div class="row g-2">
                            @foreach($run->runSections as $rs)
                            @php
                                $sc = ['pending'=>'secondary','complete'=>'success','na'=>'light'];
                                $sl = ['pending'=>'Pending','complete'=>'Done','na'=>'N/A'];
                            @endphp
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 p-2 border rounded">
                                    <i class="{{ $rs->section->icon ?? 'feather-layers' }} text-muted" style="font-size:14px;flex-shrink:0"></i>
                                    <span class="fs-12 flex-grow-1">{{ $rs->section->name }}</span>
                                    <span class="badge bg-soft-{{ $sc[$rs->status] ?? 'secondary' }} text-{{ $sc[$rs->status] ?? 'secondary' }} fs-10">
                                        {{ $sl[$rs->status] ?? $rs->status }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Linked movements --}}
                    @if($run->movements->count())
                    <div class="card-footer">
                        <span class="fw-semibold fs-12 text-muted">
                            <i class="feather-send me-1"></i>
                            {{ $run->movements->count() }} linked movement event(s)
                        </span>
                    </div>
                    @endif
                </div>
                @empty
                <div class="card mb-4">
                    <div class="card-body text-center text-muted py-4">
                        No inspection runs recorded.
                        @can('inspections.edit')
                        <a href="{{ route('inspections.runs.create', $inspection) }}" class="d-block mt-2">Add first run</a>
                        @endcan
                    </div>
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

                {{-- Run summary --}}
                @if($inspection->runs->count())
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Run Summary</h5></div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Total Runs</span>
                                <strong>{{ $inspection->runs->count() }}</strong>
                            </li>
                            @foreach(['Pass'=>'success','Fail'=>'danger','Conditional'=>'warning','Pending'=>'secondary'] as $v => $c)
                            @php $cnt = $inspection->runs->where('verdict', $v)->count(); @endphp
                            @if($cnt)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-{{ $c }}">{{ $v }}</span>
                                <strong class="text-{{ $c }}">{{ $cnt }}</strong>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
