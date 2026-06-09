{{-- Number of Cartons Loaded Section --}}
{{-- Expects: $runSection --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $v     = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
@endphp

<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Expected Cartons</label>
        <input type="number"
               name="sections[{{ $secId }}][data][expected_cartons]"
               class="form-control form-control-sm"
               value="{{ $v('expected_cartons') }}"
               placeholder="0" min="0">
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Cartons Loaded</label>
        <input type="number"
               name="sections[{{ $secId }}][data][cartons_loaded]"
               class="form-control form-control-sm"
               value="{{ $v('cartons_loaded') }}"
               placeholder="0" min="0">
    </div>
</div>

<div>
    <label class="form-label fw-semibold fs-12">Remarks</label>
    <textarea name="sections[{{ $secId }}][data][notes]"
              rows="2"
              class="form-control form-control-sm"
              placeholder="Any remarks about the carton count…">{{ $v('notes') }}</textarea>
</div>
