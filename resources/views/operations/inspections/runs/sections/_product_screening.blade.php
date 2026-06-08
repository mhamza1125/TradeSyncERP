{{-- Product Screening — photo gallery upload --}}
@php
    $data  = $runSection->data ?? [];
    $secId = $runSection->id;
@endphp

<p class="text-muted fs-13 mb-3">
    Capture product screening photos (workmanship overview, packaging, labels, defects, counter samples, etc.).
    Uploaded photos will appear in the inspection report.
</p>

{{-- Photo uploads — standardized append/remove attachment area --}}
<div class="mb-4">
    @include('operations.inspections.runs.sections._photo_upload', [
        'runSection' => $runSection,
        'uploadUrl'  => $uploadUrl,
        'inspection' => $inspection,
        'run'        => $run,
        'taskKey'    => 'screening_photos',
    ])
</div>

<div>
    <label class="form-label fw-semibold fs-12">Screening Notes</label>
    <textarea name="sections[{{ $secId }}][data][notes]"
              rows="2"
              class="form-control form-control-sm"
              placeholder="General notes about product appearance, screening findings…">{{ old("sections.{$secId}.data.notes", $data['notes'] ?? '') }}</textarea>
</div>
