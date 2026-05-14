@extends('index')

@section('title', 'New Inspection – ' . $sample->sample_code . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">New Inspection</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.index') }}">Samples</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.inspections.index', $sample) }}">Inspections</a></li>
                <li class="breadcrumb-item">New</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('samples.inspections.index', $sample) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="inspectionForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Save Inspection</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- enctype required for per-result file attachments in the test results table --}}
        <form id="inspectionForm" action="{{ route('samples.inspections.store', $sample) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-xl-12">

                    @include('operations.inspections._form')

                    {{-- Test Results — create-only card with per-parameter file uploads --}}
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Test Results
                                @if($testingParameters->count())
                                <small class="text-muted fs-12 ms-2">{{ $testingParameters->count() }} parameter(s)</small>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if($testingParameters->count())
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Parameter</th>
                                            <th>Category</th>
                                            <th>Actual Result</th>
                                            <th class="wd-120">Pass / Fail</th>
                                            <th class="wd-120">Status</th>
                                            <th>Remarks</th>
                                            <th class="wd-150">Attachment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($testingParameters as $i => $tp)
                                        <input type="hidden" name="results[{{ $i }}][sample_testing_parameter_id]" value="{{ $tp->id }}">
                                        <tr>
                                            <td class="ps-3 fw-semibold">{{ $tp->parameter->parameter_name ?? '—' }}</td>
                                            <td class="text-muted fs-12">{{ $tp->parameter->category->category_name ?? '—' }}</td>
                                            <td>
                                                <input type="text" name="results[{{ $i }}][actual_result]"
                                                       class="form-control form-control-sm @error("results.{$i}.actual_result") is-invalid @enderror"
                                                       placeholder="Enter result…"
                                                       value="{{ old("results.{$i}.actual_result") }}">
                                                @error("results.{$i}.actual_result")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </td>
                                            <td>
                                                <select name="results[{{ $i }}][pass_fail]"
                                                        class="form-select form-select-sm @error("results.{$i}.pass_fail") is-invalid @enderror">
                                                    <option value="">—</option>
                                                    <option value="Pass" @selected(old("results.{$i}.pass_fail") === 'Pass')>Pass</option>
                                                    <option value="Fail" @selected(old("results.{$i}.pass_fail") === 'Fail')>Fail</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="results[{{ $i }}][status]"
                                                        class="form-select form-select-sm @error("results.{$i}.status") is-invalid @enderror">
                                                    <option value="">—</option>
                                                    <option value="Approve" @selected(old("results.{$i}.status") === 'Approve')>Approve</option>
                                                    <option value="Reject" @selected(old("results.{$i}.status") === 'Reject')>Reject</option>
                                                    <option value="Review" @selected(old("results.{$i}.status") === 'Review')>Review</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="results[{{ $i }}][remarks]"
                                                       class="form-control form-control-sm"
                                                       placeholder="Optional…"
                                                       value="{{ old("results.{$i}.remarks") }}">
                                            </td>
                                            <td>
                                                <input type="file" name="results[{{ $i }}][attachment]"
                                                       class="form-control form-control-sm"
                                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-4 text-muted p-4">
                                <i class="feather-sliders fs-2 d-block mb-2"></i>
                                <p class="mb-2">No testing parameters defined for this sample.</p>
                                <a href="{{ route('samples.edit', $sample) }}" class="btn btn-sm btn-light-brand">
                                    <i class="feather-plus me-1"></i> Add Testing Parameters
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>
@endsection
