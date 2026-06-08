{{-- Inspection Conclusion — Presented / Unpresented Styles + closing note --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $v     = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
@endphp

<div class="row g-3 mb-0">
    <div class="col-lg-6">
        <label class="form-label fw-semibold fs-12">Presented Styles</label>
        <textarea name="sections[{{ $secId }}][data][presented_styles]"
                  rows="3"
                  class="form-control form-control-sm"
                  placeholder="List the styles / articles that were presented for inspection…">{{ $v('presented_styles') }}</textarea>
    </div>
    <div class="col-lg-6">
        <label class="form-label fw-semibold fs-12">Unpresented Styles</label>
        <textarea name="sections[{{ $secId }}][data][unpresented_styles]"
                  rows="3"
                  class="form-control form-control-sm"
                  placeholder="List the styles / articles that were not available / not presented, with reason…">{{ $v('unpresented_styles') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold fs-12">Note</label>
        <textarea name="sections[{{ $secId }}][data][note]"
                  rows="3"
                  class="form-control form-control-sm"
                  placeholder="Closing remarks, overall observations, or follow-up notes for this inspection…">{{ $v('note') }}</textarea>
    </div>
</div>
