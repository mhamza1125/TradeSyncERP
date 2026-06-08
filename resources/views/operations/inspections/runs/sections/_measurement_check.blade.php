{{-- Measurement Check — simplified Yes/No/N/A per checkpoint + photos + notes --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $items = $data['items'] ?? [];
    $notes = old("sections.{$secId}.data.notes", $data['notes'] ?? '');
@endphp

@if(!empty($items))
<div class="table-responsive mb-3">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3">Checkpoint</th>
                <th style="width:220px">Result</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $idx => $item)
        @php
            $result   = old("sections.{$secId}.data.items.{$idx}.result", $item['result'] ?? null);
            $rowClass = match($result) {
                'Yes' => 'table-success',
                'No'  => 'table-danger',
                'N/A' => 'table-light text-muted',
                default => '',
            };
        @endphp
        <tr class="{{ $rowClass }}" data-result-row style="--bs-table-bg-type: transparent;">
            <td class="ps-3 fw-semibold fs-13">
                <input type="hidden" name="sections[{{ $secId }}][data][items][{{ $idx }}][key]" value="{{ $item['key'] ?? '' }}">
                <input type="hidden" name="sections[{{ $secId }}][data][items][{{ $idx }}][label]" value="{{ $item['label'] ?? '' }}">
                {{ $item['label'] ?? '' }}
            </td>
            <td>
                @include('operations.inspections.runs.sections._result_toggle', [
                    'name'    => "sections[{$secId}][data][items][{$idx}][result]",
                    'value'   => $result,
                    'options' => ['Yes' => 'success', 'No' => 'danger', 'N/A' => 'secondary'],
                    'rowClasses' => ['Yes' => 'table-success', 'No' => 'table-danger', 'N/A' => 'table-light text-muted'],
                ])
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@else
<p class="text-muted fst-italic">No measurement checkpoints defined for this section.</p>
@endif

<div class="mb-4">
    <h6 class="fw-semibold mb-2 fs-13"><i class="feather-camera me-1 text-muted"></i>Measurement Photos</h6>
    @include('operations.inspections.runs.sections._photo_upload', [
        'runSection' => $runSection,
        'uploadUrl'  => $uploadUrl,
        'inspection' => $inspection,
        'run'        => $run,
        'taskKey'    => 'measurement_photos',
    ])
</div>

<div>
    <label class="form-label fw-semibold fs-12">Notes <span class="text-muted fw-normal">(optional)</span></label>
    <textarea name="sections[{{ $secId }}][data][notes]"
              rows="3"
              class="form-control form-control-sm"
              placeholder="Notes about measurement findings…">{{ $notes }}</textarea>
</div>
