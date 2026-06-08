{{-- Generic checklist section — handles all checklist-type sections --}}
{{-- Supports standard items (label/result/remarks) and measurement items (label/spec/actual/result/remarks) --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $items = $data['items'] ?? [];
    $secId = $runSection->id;
    $slug  = $runSection->section->slug;
    $isMeasurement = $slug === 'measurement_check';
    $isCartonVerification = $slug === 'carton_verification';
    $cartonKeys = ['total_qty_ordered', 'total_qty_loaded', 'total_cartons_ordered', 'total_cartons_loaded'];
    $cv = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
@endphp

{{-- Extra top-level fields (e.g. spec_reference for measurement, factory_name for factory readiness) --}}
@php
    $topFields = collect($data)->except('items');
    if ($isCartonVerification) {
        $topFields = $topFields->except($cartonKeys);
    }
@endphp

@if($isCartonVerification)
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold fs-12">Total Quantity Ordered</label>
        <input type="number" name="sections[{{ $secId }}][data][total_qty_ordered]"
               class="form-control form-control-sm" min="0" placeholder="0"
               value="{{ $cv('total_qty_ordered') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold fs-12">Total Loaded Quantity</label>
        <input type="number" name="sections[{{ $secId }}][data][total_qty_loaded]"
               class="form-control form-control-sm" min="0" placeholder="0"
               value="{{ $cv('total_qty_loaded') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold fs-12">Total Cartons Ordered</label>
        <input type="number" name="sections[{{ $secId }}][data][total_cartons_ordered]"
               class="form-control form-control-sm" min="0" placeholder="0"
               value="{{ $cv('total_cartons_ordered') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold fs-12">Total Cartons Loaded</label>
        <input type="number" name="sections[{{ $secId }}][data][total_cartons_loaded]"
               class="form-control form-control-sm" min="0" placeholder="0"
               value="{{ $cv('total_cartons_loaded') }}">
    </div>
</div>
@endif

@if($topFields->isNotEmpty())
<div class="row g-3 mb-4">
    @foreach($topFields as $fieldKey => $fieldVal)
    @php
        $label = ucwords(str_replace('_', ' ', $fieldKey));
    @endphp
    <div class="col-lg-4 col-md-6">
        <label class="form-label fw-semibold fs-12">{{ $label }}</label>
        <input type="text"
               name="sections[{{ $secId }}][data][{{ $fieldKey }}]"
               class="form-control form-control-sm"
               value="{{ old("sections.{$secId}.data.{$fieldKey}", $fieldVal) }}"
               placeholder="{{ $label }}…">
    </div>
    @endforeach
</div>
@endif

{{-- Checklist table --}}
@if(!empty($items))
<div class="table-responsive mb-3">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3">Checkpoint</th>
                @if($isMeasurement)
                    <th style="width:110px">Spec</th>
                    <th style="width:110px">Actual</th>
                @endif
                <th style="width:240px">Result</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $idx => $item)
        @php
            $result  = old("sections.{$secId}.data.items.{$idx}.result",  $item['result'] ?? null);
            $rowClass = match($result) {
                'Pass' => 'table-success',
                'Fail' => 'table-danger',
                'N/A'  => 'table-light text-muted',
                default => '',
            };
        @endphp
        <tr class="{{ $rowClass }}" style="--bs-table-bg-type: transparent;">
            <td class="ps-3 fw-semibold fs-13">
                <input type="hidden" name="sections[{{ $secId }}][data][items][{{ $idx }}][label]"
                       value="{{ $item['label'] ?? '' }}">
                {{ $item['label'] ?? '' }}
            </td>
            @if($isMeasurement)
            <td>
                <input type="text"
                       name="sections[{{ $secId }}][data][items][{{ $idx }}][spec]"
                       class="form-control form-control-sm text-center"
                       value="{{ old("sections.{$secId}.data.items.{$idx}.spec", $item['spec'] ?? '') }}"
                       placeholder="Spec…">
            </td>
            <td>
                <input type="text"
                       name="sections[{{ $secId }}][data][items][{{ $idx }}][actual]"
                       class="form-control form-control-sm text-center"
                       value="{{ old("sections.{$secId}.data.items.{$idx}.actual", $item['actual'] ?? '') }}"
                       placeholder="Actual…">
            </td>
            @endif
            <td>
                @include('operations.inspections.runs.sections._result_toggle', [
                    'name'  => "sections[{$secId}][data][items][{$idx}][result]",
                    'value' => $result,
                ])
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@else
<p class="text-muted fst-italic">No checklist items defined for this section.</p>
@endif
