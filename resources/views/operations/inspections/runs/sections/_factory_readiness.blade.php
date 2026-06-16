{{-- Factory Readiness Section --}}
{{-- Expects: $runSection --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $v     = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
    $items = $data['items'] ?? $runSection->section->default_data['items'] ?? [];
@endphp

{{-- Header fields --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-6">
        <label class="form-label fw-semibold fs-12">Factory Name</label>
        <input type="text"
               name="sections[{{ $secId }}][data][factory_name]"
               class="form-control form-control-sm"
               value="{{ $v('factory_name') }}"
               placeholder="Factory / supplier name">
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Production Start Date</label>
        <input type="date"
               name="sections[{{ $secId }}][data][production_start_date]"
               class="form-control form-control-sm"
               value="{{ $v('production_start_date') }}">
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Expected Completion</label>
        <input type="date"
               name="sections[{{ $secId }}][data][expected_completion]"
               class="form-control form-control-sm"
               value="{{ $v('expected_completion') }}">
    </div>
</div>

{{-- Checklist items --}}
@if(!empty($items))
<div class="table-responsive" data-checklist-wrapper="{{ $secId }}">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3" style="width:36px">#</th>
                <th>Checkpoint</th>
                <th style="width:200px">Result</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $idx => $item)
        @php
            $result   = old("sections.{$secId}.data.items.{$idx}.result", $item['result'] ?? null);
            $rowClass = match($result) {
                'Pass' => 'table-success',
                'Fail' => 'table-danger',
                'N/A'  => 'table-light text-muted',
                default => '',
            };
        @endphp
        <tr class="{{ $rowClass }}" data-result-row style="--bs-table-bg-type: transparent;">
            <td class="text-muted ps-3">{{ $idx + 1 }}</td>
            <td class="fw-semibold fs-13">
                <input type="hidden" name="sections[{{ $secId }}][data][items][{{ $idx }}][label]" value="{{ $item['label'] ?? '' }}">
                {{ $item['label'] ?? '' }}
            </td>
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
@endif
