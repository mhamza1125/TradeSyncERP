{{-- Final Review & Approval --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $v     = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
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
        <label class="form-label fw-semibold">QC Remarks</label>
        <textarea name="sections[{{ $secId }}][data][qc_remarks]"
                  rows="3"
                  class="form-control form-control-sm"
                  placeholder="Overall inspection remarks, observations, and conclusions…">{{ $v('qc_remarks') }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Action Required</label>
        <textarea name="sections[{{ $secId }}][data][action_required]"
                  rows="2"
                  class="form-control form-control-sm"
                  placeholder="Actions the supplier / buyer must take before shipment can proceed…">{{ $v('action_required') }}</textarea>
    </div>
</div>
