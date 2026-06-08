{{-- Sample Conformity — compare reference sample vs inspected product via photos --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $notes = old("sections.{$secId}.data.notes", $data['notes'] ?? '');
@endphp

<div class="row g-4 mb-3">
    <div class="col-lg-6">
        <h6 class="fw-semibold mb-1">Sample / Reference Images</h6>
        <p class="text-muted fs-13 mb-2">Photos of the approved reference sample.</p>
        @include('operations.inspections.runs.sections._photo_upload', [
            'runSection' => $runSection,
            'uploadUrl'  => $uploadUrl,
            'inspection' => $inspection,
            'run'        => $run,
            'taskKey'    => 'reference_images',
        ])
    </div>
    <div class="col-lg-6">
        <h6 class="fw-semibold mb-1">Inspected Product Images</h6>
        <p class="text-muted fs-13 mb-2">Photos of the actual product being inspected.</p>
        @include('operations.inspections.runs.sections._photo_upload', [
            'runSection' => $runSection,
            'uploadUrl'  => $uploadUrl,
            'inspection' => $inspection,
            'run'        => $run,
            'taskKey'    => 'inspected_images',
        ])
    </div>
</div>

<div>
    <label class="form-label fw-semibold fs-12">Notes <span class="text-muted fw-normal">(optional)</span></label>
    <textarea name="sections[{{ $secId }}][data][notes]"
              rows="3"
              class="form-control form-control-sm"
              placeholder="Observations about how the sample compares to the approved reference…">{{ $notes }}</textarea>
</div>
