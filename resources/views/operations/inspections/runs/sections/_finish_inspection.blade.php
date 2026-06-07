{{-- Finish Inspection Section --}}
{{-- Expects: $runSection, $run --}}
@php
    $d        = $runSection->data ?? [];
    $comments = $d['comments'] ?? '';
    $rsId     = $runSection->id;
    $isFinished = (bool) $run->completed_at;
@endphp

<div class="text-center py-2" data-section-wrapper="{{ $rsId }}">

    @if($isFinished)
    {{-- Already finished --}}
    <div class="d-flex flex-column align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center justify-content-center bg-soft-success text-success rounded-circle"
             style="width:72px;height:72px">
            <i class="feather-check-circle" style="font-size:36px"></i>
        </div>
        <div>
            <h5 class="fw-bold text-success mb-1">Inspection Finished</h5>
            <p class="text-muted mb-0 fs-13">
                Completed on {{ $run->completed_at->format('d M Y \a\t H:i') }}
            </p>
        </div>
    </div>
    @if($comments)
    <div class="p-3 bg-light rounded text-start mb-4 mx-auto" style="max-width:480px">
        <div class="fw-semibold fs-12 text-muted mb-1">Final Comments</div>
        <p class="mb-0 fs-14">{{ $comments }}</p>
    </div>
    @endif
    @else
    {{-- Not yet finished --}}
    <div class="d-flex flex-column align-items-center gap-2 mb-4">
        <div class="d-flex align-items-center justify-content-center bg-soft-secondary text-muted rounded-circle"
             style="width:72px;height:72px">
            <i class="feather-flag" style="font-size:36px"></i>
        </div>
        <div>
            <h5 class="fw-semibold mb-1">Ready to Finish?</h5>
            <p class="text-muted fs-13 mb-0">
                Review all sections above, then click <strong>Finish Inspection</strong> to close this run.
            </p>
        </div>
    </div>
    @endif

    {{-- Final comments textarea --}}
    <div class="mb-4 mx-auto text-start" style="max-width:480px">
        <label class="form-label fw-semibold">
            Final Comments <span class="text-muted fw-normal">(optional)</span>
        </label>
        <textarea name="sections[{{ $rsId }}][data][comments]"
                  class="form-control"
                  rows="4"
                  placeholder="Overall summary, observations, or follow-up actions…"
                  {{ $isFinished ? 'readonly' : '' }}>{{ old("sections.{$rsId}.data.comments", $comments) }}</textarea>
    </div>

    @if(!$isFinished)
    {{-- Finish button --}}
    <div class="d-flex justify-content-center gap-3">
        <button type="button" id="finish-inspection-btn" class="btn btn-success btn-lg px-5">
            <i class="feather-check-circle me-2"></i>Finish Inspection
        </button>
    </div>
    <p class="text-muted fs-12 mt-2">Finishing will lock this run and mark it as complete.</p>
    @else
    <a href="{{ route('inspections.edit', $inspection) }}"
       class="btn btn-outline-primary">
        <i class="feather-arrow-left me-2"></i>Back to Inspection
    </a>
    @endif

</div>
