{{-- Final Review & Approval — merged with Conclusion + Finish Inspection --}}
{{-- Expects: $runSection, $run, $inspection (for the Finish block) --}}
@php
    $data       = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId      = $runSection->id;
    $v          = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
    $isFinished = (bool) $run->completed_at;
@endphp

<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-6">
        <label class="form-label fw-semibold">Overall QC Verdict</label>
        @php
            $currentVerdict = $v('overall_verdict');
            $verdictOpts = ['Pass', 'Fail', 'Conditional Pass', 'Re-Inspection Required'];
        @endphp
        <div class="d-flex flex-wrap gap-2 mt-1">
            @foreach($verdictOpts as $opt)
            @php
                $colorMap = [
                    'Pass'                     => 'success',
                    'Fail'                     => 'danger',
                    'Conditional Pass'         => 'warning',
                    'Re-Inspection Required'   => 'info',
                ];
                $color = $colorMap[$opt] ?? 'secondary';
            @endphp
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio"
                       name="sections[{{ $secId }}][data][overall_verdict]"
                       id="verdict_{{ $secId }}_{{ $loop->index }}"
                       value="{{ $opt }}"
                       {{ $currentVerdict === $opt ? 'checked' : '' }}>
                <label class="form-check-label" for="verdict_{{ $secId }}_{{ $loop->index }}">
                    <span class="badge bg-soft-{{ $color }} text-{{ $color }}">{{ $opt }}</span>
                </label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <label class="form-label fw-semibold">Inspector Name</label>
        <input type="text"
               name="sections[{{ $secId }}][data][inspector_name]"
               class="form-control form-control-sm"
               value="{{ $v('inspector_name') }}"
               placeholder="Signing inspector name…">
    </div>

    <div class="col-lg-4 col-md-6">
        <label class="form-label fw-semibold">Follow-up Date <small class="text-muted fw-normal">(if needed)</small></label>
        <input type="date"
               name="sections[{{ $secId }}][data][follow_up_date]"
               class="form-control form-control-sm"
               value="{{ $v('follow_up_date') }}">
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold fs-12">Remarks</label>
        <textarea name="sections[{{ $secId }}][data][notes]"
                  rows="3"
                  class="form-control form-control-sm"
                  placeholder="Closing remarks…">{{ $v('notes') }}</textarea>
    </div>
</div>

<hr class="my-4">

{{-- Finish Inspection --}}
<div class="text-center py-2">
    @if($isFinished)
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
    @else
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

    @if(!$isFinished)
    <div class="d-flex justify-content-center gap-3">
        <button type="button" id="finish-inspection-btn" class="btn btn-success btn-lg px-5">
            <i class="feather-check-circle me-2"></i>Finish Inspection
        </button>
    </div>
    <p class="text-muted fs-12 mt-2">Finishing will lock this run and mark it as complete.</p>
    @else
    <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-outline-primary">
        <i class="feather-arrow-left me-2"></i>Back to Inspection
    </a>
    @endif
</div>
