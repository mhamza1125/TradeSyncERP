{{-- Variation vs Tech Pack — simplified overall result + attachment + optional notes --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $data      = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId     = $runSection->id;
    $reference = old("sections.{$secId}.data.tech_pack_reference", $data['tech_pack_reference'] ?? '');
    $result    = old("sections.{$secId}.data.result", $data['result'] ?? null);
    $notes     = old("sections.{$secId}.data.notes", $data['notes'] ?? '');
@endphp

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <label class="form-label fw-semibold fs-12">Tech Pack Reference</label>
        <input type="text"
               name="sections[{{ $secId }}][data][tech_pack_reference]"
               class="form-control form-control-sm"
               value="{{ $reference }}"
               placeholder="Tech pack version / reference number…">
    </div>
    <div class="col-lg-6">
        <label class="form-label fw-semibold fs-12 d-block">Matches Tech Pack?</label>
        @include('operations.inspections.runs.sections._result_toggle', [
            'name'  => "sections[{$secId}][data][result]",
            'value' => $result,
        ])
    </div>
</div>

<div class="mb-4">
    <h6 class="fw-semibold mb-2 fs-13"><i class="feather-camera me-1 text-muted"></i>Tech Pack Comparison Photos</h6>
    @include('operations.inspections.runs.sections._photo_upload', [
        'runSection' => $runSection,
        'uploadUrl'  => $uploadUrl,
        'inspection' => $inspection,
        'run'        => $run,
        'taskKey'    => 'techpack_photos',
    ])
</div>

<div>
    <label class="form-label fw-semibold fs-12">Remarks</label>
    <textarea name="sections[{{ $secId }}][data][notes]"
              rows="3"
              class="form-control form-control-sm"
              placeholder="Notes about variations from the tech pack…">{{ $notes }}</textarea>
</div>
