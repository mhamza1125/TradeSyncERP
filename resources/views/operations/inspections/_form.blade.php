{{--
    Shared inspection form partial (create & edit).
    Variables: $employees, $samples (mapped [{id,text}]), $customerOrders (mapped [{id,text}])
    Optional: $inspection (edit only)
--}}
@php
    $savedSampleIds  = isset($inspection) ? $inspection->samples->pluck('id')->toArray() : [];
    $savedOrderIds   = isset($inspection) ? $inspection->customerOrders->pluck('id')->toArray() : [];
    $savedInspectors = isset($inspection) ? $inspection->inspectors->pluck('id')->toArray() : [];
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
<style>
    .ts-wrapper.form-control { padding: 0; border: 0; }
    .ts-wrapper .ts-control { border-radius: 0.375rem; }
</style>
@endpush

{{-- ── Inspection Details ────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            Inspection Details
            @isset($inspection)
                <span class="text-muted fw-normal fs-14">— {{ $inspection->report_number }}</span>
            @endisset
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-4">
                <label class="form-label">Inspection Date <span class="text-danger">*</span></label>
                <input type="date" name="inspection_date"
                       class="form-control @error('inspection_date') is-invalid @enderror"
                       value="{{ old('inspection_date', isset($inspection) ? $inspection->inspection_date->toDateString() : now()->toDateString()) }}"
                       required>
                @error('inspection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-4">
                <label class="form-label">Overall Status</label>
                <select name="overall_status" class="form-select @error('overall_status') is-invalid @enderror">
                    @foreach(['Pending','Pass','Fail'] as $s)
                        <option value="{{ $s }}" @selected(old('overall_status', $inspection->overall_status ?? 'Pending') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                @error('overall_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" rows="2" class="form-control"
                          placeholder="Overall inspection notes…">{{ old('remarks', $inspection->remarks ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- ── Samples Being Tested ──────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Samples Being Tested</h5>
        <small class="text-muted">Search and select samples for this inspection.</small>
    </div>
    <div class="card-body">
        <select id="sampleSelect" name="sample_ids[]" multiple placeholder="Search samples…"
                class="@error('sample_ids') is-invalid @enderror">
            @foreach($samples as $s)
                <option value="{{ $s['id'] }}" @selected(in_array($s['id'], old('sample_ids', $savedSampleIds)))>
                    {{ $s['text'] }}
                </option>
            @endforeach
        </select>
        @error('sample_ids')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
    </div>
</div>

{{-- ── Linked Customer Orders ────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Linked Customer Orders</h5>
        <small class="text-muted">Search and select customer orders related to this inspection.</small>
    </div>
    <div class="card-body">
        <select id="orderSelect" name="customer_order_ids[]" multiple placeholder="Search orders…"
                class="@error('customer_order_ids') is-invalid @enderror">
            @foreach($customerOrders as $o)
                <option value="{{ $o['id'] }}" @selected(in_array($o['id'], old('customer_order_ids', $savedOrderIds)))>
                    {{ $o['text'] }}
                </option>
            @endforeach
        </select>
        @error('customer_order_ids')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
    </div>
</div>

{{-- ── Assigned Inspectors ──────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Assigned Inspectors</h5>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @forelse($employees as $e)
            <div class="col-md-4 col-lg-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="inspector_ids[]" value="{{ $e->id }}"
                           id="insp_{{ $e->id }}"
                           @checked(in_array($e->id, old('inspector_ids', $savedInspectors)))>
                    <label class="form-check-label" for="insp_{{ $e->id }}">
                        {{ $e->employee_name }}
                        @if($e->designation)
                            <small class="text-muted d-block">{{ $e->designation }}</small>
                        @endif
                    </label>
                </div>
            </div>
            @empty
            <p class="text-muted mb-0">No active employees found.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    new TomSelect('#sampleSelect', {
        plugins: ['remove_button', 'checkbox_options'],
        maxOptions: null,
        placeholder: 'Search samples…',
    });
    new TomSelect('#orderSelect', {
        plugins: ['remove_button', 'checkbox_options'],
        maxOptions: null,
        placeholder: 'Search orders…',
    });
</script>
@endpush
