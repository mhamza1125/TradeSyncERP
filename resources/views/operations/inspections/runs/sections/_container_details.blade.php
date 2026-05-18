{{-- Container Details Section --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $v     = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
@endphp

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Container Number</label>
        <input type="text" name="sections[{{ $secId }}][data][container_number]"
               class="form-control form-control-sm"
               value="{{ $v('container_number') }}"
               placeholder="e.g. ABCD1234567">
    </div>
    <div class="col-lg-2 col-md-6">
        <label class="form-label fw-semibold fs-12">Container Type</label>
        <select name="sections[{{ $secId }}][data][container_type]" class="form-select form-select-sm">
            <option value="">— Select —</option>
            @foreach(["20'GP","40'GP","40'HC","20'Reefer","40'Reefer","LCL"] as $ct)
                <option value="{{ $ct }}" @selected($v('container_type') === $ct)>{{ $ct }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Seal Number</label>
        <input type="text" name="sections[{{ $secId }}][data][seal_number]"
               class="form-control form-control-sm"
               value="{{ $v('seal_number') }}"
               placeholder="Seal / bolt seal number">
    </div>
    <div class="col-lg-2 col-md-6">
        <label class="form-label fw-semibold fs-12">Loading Date</label>
        <input type="date" name="sections[{{ $secId }}][data][loading_date]"
               class="form-control form-control-sm"
               value="{{ $v('loading_date') }}">
    </div>

    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Loading Port</label>
        <input type="text" name="sections[{{ $secId }}][data][loading_port]"
               class="form-control form-control-sm"
               value="{{ $v('loading_port') }}"
               placeholder="e.g. Karachi, Shanghai…">
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Discharge Port</label>
        <input type="text" name="sections[{{ $secId }}][data][discharge_port]"
               class="form-control form-control-sm"
               value="{{ $v('discharge_port') }}"
               placeholder="e.g. Rotterdam, Los Angeles…">
    </div>
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Total Cartons Loaded</label>
        <input type="number" name="sections[{{ $secId }}][data][total_cartons_loaded]"
               class="form-control form-control-sm"
               value="{{ $v('total_cartons_loaded') }}"
               min="0" placeholder="0">
    </div>
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Total Qty Loaded</label>
        <input type="number" name="sections[{{ $secId }}][data][total_quantity_loaded]"
               class="form-control form-control-sm"
               value="{{ $v('total_quantity_loaded') }}"
               min="0" placeholder="0">
    </div>
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Container Condition</label>
        <select name="sections[{{ $secId }}][data][container_condition]" class="form-select form-select-sm">
            <option value="">— Select —</option>
            <option value="Good"     @selected($v('container_condition') === 'Good')>Good</option>
            <option value="Fair"     @selected($v('container_condition') === 'Fair')>Fair</option>
            <option value="Damaged"  @selected($v('container_condition') === 'Damaged')>Damaged</option>
        </select>
    </div>
</div>

<div class="mb-0">
    <label class="form-label fw-semibold fs-12">Container Notes</label>
    <textarea name="sections[{{ $secId }}][data][notes]"
              rows="2"
              class="form-control form-control-sm"
              placeholder="Additional notes about the container loading…">{{ $v('notes') }}</textarea>
</div>

{{-- Photo uploads --}}
<hr class="my-4">
<h6 class="fw-semibold mb-2 fs-13">
    <i class="feather-camera me-1 text-muted"></i>Container / Loading Photos
</h6>
@include('operations.inspections.runs.sections._photo_upload', ['runSection' => $runSection])
