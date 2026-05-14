{{--
    Inspection Header card — shared by create.blade.php and edit.blade.php.
    Callers keep the <form> tag, column wrapper, and any create/edit-specific
    adjacent cards (test-results table on create; results summary on edit).

    Available variables:
      $sample      – always present
      $inspection  – present on edit only (use isset($inspection) to branch)
      $employees   – Employee collection
      $inspectionTypes – InspectionType collection
--}}
@php
    $savedInspectorIds = isset($inspection)
        ? $inspection->inspectors->pluck('id')->toArray()
        : [];
    $defaultDate = isset($inspection)
        ? $inspection->inspection_date->toDateString()
        : now()->toDateString();
@endphp

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">
            Inspection Header
            @isset($inspection)
                <span class="text-muted fw-normal">— {{ $inspection->report_number }}</span>
            @endisset
        </h5>
    </div>
    <div class="card-body">

        {{-- Sample info strip --}}
        <div class="alert alert-light border mb-4">
            <div class="d-flex gap-4 flex-wrap">
                <div><span class="text-muted fs-12">Sample</span><br><strong>{{ $sample->sample_code }}</strong></div>
                <div><span class="text-muted fs-12">Product</span><br><strong>{{ $sample->product_name }}</strong></div>
                <div><span class="text-muted fs-12">Customer</span><br><strong>{{ $sample->customer->customer_name }}</strong></div>
                @if($sample->supplier)
                <div><span class="text-muted fs-12">Supplier / Factory</span><br><strong>{{ $sample->supplier->name }}</strong></div>
                @endif
            </div>
        </div>

        {{-- Edit-only notice --}}
        @isset($inspection)
        <div class="alert alert-warning border mb-4">
            <small><i class="feather-info me-2"></i>
                Test results cannot be modified after creation. Only the header fields are editable here.
            </small>
        </div>
        @endisset

        {{-- Core fields --}}
        <div class="row">
            <div class="col-lg-6 mb-4">
                <label class="form-label">Inspection Date <span class="text-danger">*</span></label>
                <input type="date" name="inspection_date"
                       class="form-control @error('inspection_date') is-invalid @enderror"
                       value="{{ old('inspection_date', $defaultDate) }}" required>
                @error('inspection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6 mb-4">
                <label class="form-label">Inspection Type</label>
                <select name="inspection_type_id" class="form-select @error('inspection_type_id') is-invalid @enderror">
                    <option value="">— Select Type —</option>
                    @foreach($inspectionTypes as $type)
                    <option value="{{ $type->id }}"
                        @selected(old('inspection_type_id', $inspection->inspection_type_id ?? '') == $type->id)>
                        {{ $type->name }}
                    </option>
                    @endforeach
                </select>
                @error('inspection_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 mb-4">
                <label class="form-label">
                    {{ isset($inspection) ? 'Inspectors' : 'Inspectors (our employees visiting factory)' }}
                </label>
                <div class="row g-2">
                    @foreach($employees as $e)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="inspector_ids[]" value="{{ $e->id }}"
                                   id="inspector_{{ $e->id }}"
                                   @checked(in_array($e->id, old('inspector_ids', $savedInspectorIds)))>
                            <label class="form-check-label" for="inspector_{{ $e->id }}">
                                {{ $e->employee_name }}
                                @if($e->job_title)<small class="text-muted">({{ $e->job_title }})</small>@endif
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                @error('inspector_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6 mb-4">
                <label class="form-label">Overall Status</label>
                <select name="overall_status" class="form-select @error('overall_status') is-invalid @enderror">
                    @php
                        $currentStatus = old('overall_status', $inspection->overall_status ?? 'Pending');
                    @endphp
                    <option value="Pending" @selected($currentStatus === 'Pending')>
                        Pending{{ isset($inspection) ? '' : ' (auto-calculated)' }}
                    </option>
                    <option value="Pass" @selected($currentStatus === 'Pass')>Pass</option>
                    <option value="Fail" @selected($currentStatus === 'Fail')>Fail</option>
                </select>
                @error('overall_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 mb-2">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" rows="{{ isset($inspection) ? 3 : 2 }}"
                          class="form-control @error('remarks') is-invalid @enderror"
                          placeholder="Optional overall notes…">{{ old('remarks', $inspection->remarks ?? '') }}</textarea>
                @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

    </div>
</div>
