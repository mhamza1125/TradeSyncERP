{{-- Barcode Testing Section --}}
@php
    $data   = old("sections.{$runSection->id}.data", $runSection->data ?? []);
    $status = $data['barcode_status'] ?? null;
@endphp

<div class="mb-3">
    <label class="form-label fw-semibold">Barcode Status <span class="text-danger">*</span></label>
    <div>
        @include('operations.inspections.runs.sections._result_toggle', [
            'name'    => "sections[{$runSection->id}][data][barcode_status]",
            'value'   => $status,
            'options' => ['functional' => 'success', 'partial' => 'warning', 'non-functional' => 'danger'],
            'labels'  => ['functional' => 'Functional', 'partial' => 'Partial', 'non-functional' => 'Non-Functional'],
        ])
    </div>
    <small class="text-muted d-block mt-1">Scan test result for all barcodes in the lot.</small>
</div>

<div class="mt-4">
    <label class="form-label fw-semibold">Attachments <small class="text-muted">(photos / videos)</small></label>
    @include('operations.inspections.runs.sections._photo_upload', [
        'runSection' => $runSection,
        'uploadUrl'  => $uploadUrl,
        'inspection' => $inspection,
        'run'        => $run,
        'taskKey'    => 'barcode_photos',
    ])
</div>
