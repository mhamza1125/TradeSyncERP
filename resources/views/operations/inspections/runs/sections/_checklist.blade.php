{{-- Generic checklist section — handles all checklist-type sections --}}
{{-- Supports standard items (label/result/remarks) and measurement items (label/spec/actual/result/remarks) --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $items = $data['items'] ?? [];
    $secId = $runSection->id;
    $isMeasurement = $runSection->section->slug === 'measurement_check';
@endphp

{{-- Extra top-level fields (e.g. spec_reference for measurement, factory_name for factory readiness) --}}
@php
    $topFields = collect($data)->except('items');
@endphp

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
                <th class="ps-3" style="width:{{ $isMeasurement ? '160px' : '260px' }}">Checkpoint</th>
                @if($isMeasurement)
                    <th style="width:110px">Spec</th>
                    <th style="width:110px">Actual</th>
                @endif
                <th style="width:120px">Result</th>
                <th>Remarks</th>
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
                <select name="sections[{{ $secId }}][data][items][{{ $idx }}][result]"
                        class="form-select form-select-sm checklist-result"
                        onchange="updateChecklistRow(this)">
                    <option value="">— Select —</option>
                    <option value="Pass" @selected($result === 'Pass')>Pass</option>
                    <option value="Fail" @selected($result === 'Fail')>Fail</option>
                    <option value="N/A"  @selected($result === 'N/A')>N/A</option>
                </select>
            </td>
            <td>
                <input type="text"
                       name="sections[{{ $secId }}][data][items][{{ $idx }}][remarks]"
                       class="form-control form-control-sm"
                       value="{{ old("sections.{$secId}.data.items.{$idx}.remarks", $item['remarks'] ?? '') }}"
                       placeholder="Remarks…">
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@else
<p class="text-muted fst-italic">No checklist items defined for this section.</p>
@endif

<script>
function updateChecklistRow(sel) {
    const row = sel.closest('tr');
    row.classList.remove('table-success','table-danger','table-light','text-muted');
    if      (sel.value === 'Pass') row.classList.add('table-success');
    else if (sel.value === 'Fail') row.classList.add('table-danger');
    else if (sel.value === 'N/A')  { row.classList.add('table-light'); row.classList.add('text-muted'); }
}
</script>
