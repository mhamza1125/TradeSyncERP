{{-- Overall Article Result — final pass/fail verdict for the inspected article(s) --}}
@php
    $data    = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId   = $runSection->id;
    $result  = old("sections.{$secId}.data.overall_result", $data['overall_result'] ?? null);
    $remarks = old("sections.{$secId}.data.remarks", $data['remarks'] ?? '');
    $opts    = ['Pass' => 'success', 'Fail' => 'danger', 'Pending' => 'warning'];
@endphp

<div class="row g-3 mb-0">
    <div class="col-12">
        <label class="form-label fw-semibold fs-12">Overall Article Result</label>
        <div class="d-flex flex-wrap gap-2 mt-1">
            @foreach($opts as $opt => $color)
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio"
                       name="sections[{{ $secId }}][data][overall_result]"
                       id="overall_result_{{ $secId }}_{{ $loop->index }}"
                       value="{{ $opt }}" @checked($result === $opt)>
                <label class="form-check-label badge bg-{{ $color }}-transparent text-{{ $color }} fs-12 fw-medium px-3 py-2"
                       for="overall_result_{{ $secId }}_{{ $loop->index }}">{{ $opt }}</label>
            </div>
            @endforeach
        </div>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold fs-12">Optional Notes</label>
        <textarea name="sections[{{ $secId }}][data][remarks]"
                  rows="3"
                  class="form-control form-control-sm"
                  placeholder="Optional notes for this section…">{{ $remarks }}</textarea>
    </div>
</div>
