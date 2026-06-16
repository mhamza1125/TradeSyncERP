{{-- Final Review & Approval — verdict and remarks are now captured in Overall Article Result --}}
{{-- This section is retained for compatibility but collects no data. --}}
@php
    $isFinished = (bool) $run->completed_at;
@endphp

<div class="text-center py-3">
    <div class="d-flex align-items-center justify-content-center bg-soft-info text-info rounded-circle mx-auto mb-3"
         style="width:56px;height:56px">
        <i class="feather-info" style="font-size:26px"></i>
    </div>
    <h6 class="fw-semibold mb-2">Verdict Recorded in Overall Article Result</h6>
    <p class="text-muted fs-13 mb-4" style="max-width:420px;margin:0 auto">
        The inspection verdict (Pass / Fail / Pending / Re-Inspection Required) and remarks
        are captured in the <strong>Overall Article Result</strong> section above.
        Use the <strong>Finish Inspection</strong> button at the bottom of the page to close this run.
    </p>
    <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-outline-secondary btn-sm">
        <i class="feather-arrow-left me-1"></i>Back to Inspection
    </a>
</div>
