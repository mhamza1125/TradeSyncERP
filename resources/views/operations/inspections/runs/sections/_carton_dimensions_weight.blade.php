{{-- Carton Dimensions & Weight Section --}}
{{-- Expects: $runSection --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $v     = fn(string $key, $default = '') => old("sections.{$secId}.data.{$key}", $data[$key] ?? $default);
@endphp

{{-- Row 1: Dimension Unit + Dimensions --}}
<div class="row g-3 mb-3">
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Dimension Unit</label>
        <select name="sections[{{ $secId }}][data][dimension_unit]" class="form-select form-select-sm">
            @foreach(['cm', 'mm', 'inch', 'ft', 'm'] as $unit)
                <option value="{{ $unit }}" @selected($v('dimension_unit', 'cm') === $unit)>{{ $unit }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Carton Length</label>
        <input type="number" step="0.01"
               name="sections[{{ $secId }}][data][carton_length]"
               class="form-control form-control-sm"
               value="{{ $v('carton_length') }}"
               placeholder="0.00" min="0">
    </div>
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Carton Width</label>
        <input type="number" step="0.01"
               name="sections[{{ $secId }}][data][carton_width]"
               class="form-control form-control-sm"
               value="{{ $v('carton_width') }}"
               placeholder="0.00" min="0">
    </div>
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Carton Height</label>
        <input type="number" step="0.01"
               name="sections[{{ $secId }}][data][carton_height]"
               class="form-control form-control-sm"
               value="{{ $v('carton_height') }}"
               placeholder="0.00" min="0">
    </div>
</div>

{{-- Row 2: Weight Unit + Weights --}}
<div class="row g-3">
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Weight Unit</label>
        <select name="sections[{{ $secId }}][data][weight_unit]" class="form-select form-select-sm">
            @foreach(['kg', 'g', 'lb', 'oz'] as $unit)
                <option value="{{ $unit }}" @selected($v('weight_unit', 'kg') === $unit)>{{ $unit }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Gross Weight</label>
        <input type="number" step="0.001"
               name="sections[{{ $secId }}][data][gross_weight]"
               class="form-control form-control-sm"
               value="{{ $v('gross_weight') }}"
               placeholder="0.000" min="0">
    </div>
    <div class="col-lg-2 col-md-4">
        <label class="form-label fw-semibold fs-12">Net Weight</label>
        <input type="number" step="0.001"
               name="sections[{{ $secId }}][data][net_weight]"
               class="form-control form-control-sm"
               value="{{ $v('net_weight') }}"
               placeholder="0.000" min="0">
    </div>
</div>
