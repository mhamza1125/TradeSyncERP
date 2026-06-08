{{-- Protector Evaluation Section --}}
@php
    $data        = old("sections.{$runSection->id}.data", $runSection->data ?? []);
    $result      = $data['evaluation_result']  ?? null;
    $result      = $result === 'pending' ? null : $result;
    $impact      = $data['impact_notes']       ?? '';
    $flexibility = $data['flexibility_notes']  ?? '';
@endphp

<div class="row g-3">
    <div class="col-lg-4">
        <label class="form-label fw-semibold">Evaluation Result <span class="text-danger">*</span></label>
        <div>
            @include('operations.inspections.runs.sections._result_toggle', [
                'name'    => "sections[{$runSection->id}][data][evaluation_result]",
                'value'   => $result,
                'options' => ['pass' => 'success', 'fail' => 'danger', 'partial' => 'warning'],
                'labels'  => ['pass' => 'Pass', 'fail' => 'Fail', 'partial' => 'Partial'],
            ])
        </div>
        <small class="text-muted d-block mt-1">Overall protective gear evaluation outcome.</small>
    </div>

    <div class="col-lg-4">
        <label class="form-label fw-semibold">Strength / Impact Notes</label>
        <input type="text"
               name="sections[{{ $runSection->id }}][data][impact_notes]"
               class="form-control"
               value="{{ $impact }}"
               placeholder="e.g. Passed EN 1621-2 level 1 impact test…">
    </div>

    <div class="col-lg-4">
        <label class="form-label fw-semibold">Flexibility / Comfort Notes</label>
        <input type="text"
               name="sections[{{ $runSection->id }}][data][flexibility_notes]"
               class="form-control"
               value="{{ $flexibility }}"
               placeholder="e.g. Good range of motion, no restriction…">
    </div>
</div>

<div class="mt-4">
    <label class="form-label fw-semibold">Attachments <small class="text-muted">(photos / videos)</small></label>
    @include('operations.inspections.runs.sections._photo_upload', [
        'runSection' => $runSection,
        'uploadUrl'  => $uploadUrl,
        'inspection' => $inspection,
        'run'        => $run,
        'taskKey'    => 'evaluation_photos',
    ])
</div>
