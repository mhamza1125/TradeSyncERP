@extends('index')

@section('title', 'Edit ' . $inspection->report_number . ' — TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Edit Inspection</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.show', $inspection) }}">{{ $inspection->report_number }}</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-light-brand">
                    <i class="feather-eye me-2"></i>View
                </a>
                <button type="submit" form="editInspectionForm" class="btn btn-primary">
                    <i class="feather-save me-2"></i>Save Details
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Inspection details form ──────────────────────────────────────── --}}
        <form id="editInspectionForm" action="{{ route('inspections.update', $inspection) }}" method="POST">
            @csrf @method('PUT')
            @include('operations.inspections._form')
        </form>

        {{-- Runs table ───────────────────────────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Inspection Runs</h5>
                    <small class="text-muted">
                        Each run tests one sample. Sections are automatically applied based on the inspection type
                        <strong>{{ $inspection->inspectionType?->name ?? '(no type)' }}</strong>
                        and the sample's category.
                    </small>
                </div>
                <a href="{{ route('inspections.runs.create', $inspection) }}" class="btn btn-sm btn-primary">
                    <i class="feather-plus me-1"></i>Add Run
                </a>
            </div>

            @if($inspection->runs->isEmpty())
            <div class="card-body text-center text-muted py-5">
                <i class="feather-clipboard" style="font-size:2rem; opacity:.3"></i>
                <p class="mt-2 mb-0">No runs yet. Click <strong>Add Run</strong> to create the first one.</p>
            </div>
            @else
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Sample</th>
                                <th>Category</th>
                                <th class="text-center">Sections</th>
                                <th class="text-center">Verdict</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inspection->runs as $i => $run)
                            @php
                                $verdictColors = [
                                    'Pass'        => 'success',
                                    'Fail'        => 'danger',
                                    'Conditional' => 'warning',
                                    'Pending'     => 'secondary',
                                ];
                                $vColor = $verdictColors[$run->verdict] ?? 'secondary';
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold text-muted">{{ $i + 1 }}</td>
                                <td>
                                    @if($run->sample)
                                        <div class="fw-semibold fs-13">{{ $run->sample->sample_code }}</div>
                                        <div class="text-muted fs-12">{{ $run->sample->product_name }}</div>
                                    @else
                                        <span class="text-muted fst-italic fs-12">No sample</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-13">
                                    {{ $run->sample?->customer?->customer_name ?? '—' }}
                                </td>
                                <td class="text-center">
                                    @php $sectionCount = $run->runSections->count() @endphp
                                    @if($sectionCount)
                                        <span class="badge bg-soft-info text-info">{{ $sectionCount }} sections</span>
                                    @else
                                        <span class="text-muted fs-12">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-soft-{{ $vColor }} text-{{ $vColor }}">
                                        {{ $run->verdict }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('inspections.runs.edit', [$inspection, $run]) }}"
                                           class="btn btn-sm btn-light-brand">
                                            <i class="feather-edit-3 me-1"></i>Edit
                                        </a>
                                        <form action="{{ route('inspections.runs.destroy', [$inspection, $run]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Delete this run and all its results?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light-danger">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
