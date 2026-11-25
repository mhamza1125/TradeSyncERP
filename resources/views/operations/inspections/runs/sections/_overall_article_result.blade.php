{{-- Overall Article Result = the inspector's assessment (Q15 = a, docs/QC_RULES.md §3).
     The run's decision is calculated from the AQL result and the defect rows; this choice is
     recorded and printed next to it, and needs a reason when it differs. --}}
@php
    $data     = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId    = $runSection->id;
    $result   = old("sections.{$secId}.data.overall_result", $data['overall_result'] ?? null);
    $remarks  = old("sections.{$secId}.data.remarks",        $data['remarks']        ?? '');
    $followUp = old("sections.{$secId}.data.follow_up_date", $data['follow_up_date'] ?? '');
    $reason   = old("sections.{$secId}.data.assessment_reason", $data['assessment_reason'] ?? '');
    $decision = $computedVerdict ?? 'Pending';
    $differs  = \App\Services\Inspection\InspectionVerdictService::contradicts(
        \App\Services\Inspection\InspectionVerdictService::fromAssessment($result), $decision
    );
    $decisionDisplay = \App\Services\Inspection\InspectionVerdictService::display($decision);
    $opts     = [
        'Pass'                   => 'success',
        'Fail'                   => 'danger',
        'Pending'                => 'warning',
        'Re-Inspection Required' => 'info',
    ];
@endphp

<div class="row g-3 mb-0">
    <div class="col-12">
        <div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 py-2 mb-2 fs-12" role="note">
            <span>{{ $run->completed_at ? 'Decision' : 'Calculated decision so far' }}:</span>
            <span class="badge bg-soft-{{ $decisionDisplay['badge'] }} text-{{ $decisionDisplay['badge'] }}"
                  id="computed-verdict-{{ $secId }}" data-computed-verdict="{{ $decision }}">{{ $decisionDisplay['label'] }}</span>
            <span class="text-muted">The report's decision is calculated from the AQL result and the recorded defects. Your assessment is printed next to it and never changes it.</span>
        </div>
        <label class="form-label fw-semibold fs-12">Inspector's assessment</label>
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

    {{-- Required to finish when the assessment differs from the calculated decision --}}
    <div class="col-12" id="assessment-reason-row-{{ $secId }}" style="{{ ($differs || $reason !== '') ? '' : 'display:none' }}">
        <label class="form-label fw-semibold fs-12" for="assessment-reason-{{ $secId }}">Reason the assessment differs <span class="text-danger">*</span></label>
        <textarea name="sections[{{ $secId }}][data][assessment_reason]"
                  id="assessment-reason-{{ $secId }}"
                  rows="2" maxlength="2000"
                  class="form-control form-control-sm"
                  placeholder="Why your assessment differs from the calculated decision (printed on the report)…">{{ $reason }}</textarea>
        <div class="form-text fs-11">Needed to finish the run while your assessment differs from the calculated decision.</div>
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
    const secId     = {{ $secId }};
    const dateRow   = document.getElementById('follow-up-date-row-' + secId);
    const reasonRow = document.getElementById('assessment-reason-row-' + secId);
    const decision  = document.getElementById('computed-verdict-' + secId)?.dataset.computedVerdict || 'Pending';
    const asVerdict = { 'Pass': 'Pass', 'Fail': 'Fail', 'Re-Inspection Required': 'Re-Inspection' };

    document.querySelectorAll(`.overall-result-radio[data-section-id="${secId}"]`).forEach(radio => {
        radio.addEventListener('change', function () {
            if (dateRow) dateRow.style.display = this.value === 'Re-Inspection Required' ? '' : 'none';
            // Same rule as the server (InspectionVerdictService::contradicts); the server decides at finish.
            const mine = asVerdict[this.value] || null;
            const differs = mine !== null && decision !== 'Pending' && mine !== decision;
            const reason = document.getElementById('assessment-reason-' + secId);
            if (reasonRow) reasonRow.style.display = (differs || (reason && reason.value.trim() !== '')) ? '' : 'none';
        });
    });
})();
</script>
@endpush
