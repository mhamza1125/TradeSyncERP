{{-- Quantity per Carton Section --}}
{{-- Expects: $runSection --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $v     = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
@endphp

<div class="row g-3">
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Declared Qty per Carton</label>
        <input type="number"
               name="sections[{{ $secId }}][data][declared_qty_per_carton]"
               class="form-control form-control-sm"
               value="{{ $v('declared_qty_per_carton') }}"
               placeholder="0" min="0">
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Verified Qty per Carton</label>
        <input type="number"
               name="sections[{{ $secId }}][data][verified_qty_per_carton]"
               class="form-control form-control-sm"
               value="{{ $v('verified_qty_per_carton') }}"
               placeholder="0" min="0">
    </div>
</div>
