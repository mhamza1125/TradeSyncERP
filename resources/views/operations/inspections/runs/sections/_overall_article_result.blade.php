{{-- Overall Article Result — final pass/fail verdict for the inspected article(s) --}}
@php
    $data     = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId    = $runSection->id;
    $result   = old("sections.{$secId}.data.overall_result", $data['overall_result'] ?? null);
    $remarks  = old("sections.{$secId}.data.remarks",        $data['remarks']        ?? '');
    $followUp = old("sections.{$secId}.data.follow_up_date", $data['follow_up_date'] ?? '');
    $opts     = [
        'Pass'                   => 'success',
        'Fail'                   => 'danger',
        'Pending'                => 'warning',
        'Re-Inspection Required' => 'info',
    ];
@endphp

<div class="row g-3 mb-0">
    <div class="col-12">
        <label class="form-label fw-semibold fs-12">Overall Article Result</label>
        <div class="d-flex flex-wrap gap-2 mt-1">
            @foreach($opts as $opt => $color)
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input overall-result-radio" type="radio"
                       name="sections[{{ $secId }}][data][overall_result]"
                       id="overall_result_{{ $secId }}_{{ $loop->index }}"
                       value="{{ $opt }}"
                       @checked($result === $opt)
                       data-section-id="{{ $secId }}">
                <label class="form-check-label badge bg-{{ $color }}-transparent text-{{ $color }} fs-12 fw-medium px-3 py-2"
                       for="overall_result_{{ $secId }}_{{ $loop->index }}">{{ $opt }}</label>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Follow-up Date: shown only when Re-Inspection Required is selected --}}
    <div class="col-lg-4 col-md-6"
         id="follow-up-date-row-{{ $secId }}"
         style="{{ $result === 'Re-Inspection Required' ? '' : 'display:none' }}">
        <label class="form-label fw-semibold fs-12">Follow-up Date</label>
        <input type="date"
               name="sections[{{ $secId }}][data][follow_up_date]"
               class="form-control form-control-sm"
               value="{{ $followUp }}">
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold fs-12">Remarks</label>
        <textarea name="sections[{{ $secId }}][data][remarks]"
                  rows="3"
                  class="form-control form-control-sm"
                  placeholder="Remarks about the overall article result…">{{ $remarks }}</textarea>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const secId   = {{ $secId }};
    const dateRow = document.getElementById('follow-up-date-row-' + secId);
    if (!dateRow) return;

    document.querySelectorAll(`.overall-result-radio[data-section-id="${secId}"]`).forEach(radio => {
        radio.addEventListener('change', function () {
            dateRow.style.display = this.value === 'Re-Inspection Required' ? '' : 'none';
        });
    });
})();
</script>
@endpush
