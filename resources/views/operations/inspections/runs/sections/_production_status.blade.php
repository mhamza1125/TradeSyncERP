{{-- Production Status — single status dropdown + notes --}}
{{-- Expects: $runSection --}}
@php
    $data   = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId  = $runSection->id;
    $status = old("sections.{$secId}.data.status", $data['status'] ?? null);
    $notes  = old("sections.{$secId}.data.notes", $data['notes'] ?? '');
@endphp

<div class="mb-3">
    <label class="form-label fw-semibold fs-12">Production Status</label>
    <div>
        @include('operations.inspections.runs.sections._result_toggle', [
            'name'    => "sections[{$secId}][data][status]",
            'value'   => $status,
            'options' => ['Not Started' => 'secondary', 'In Progress' => 'warning', 'Completed' => 'success'],
        ])
    </div>
</div>

<div>
    <label class="form-label fw-semibold fs-12">Remarks</label>
    <textarea name="sections[{{ $secId }}][data][notes]"
              rows="3"
              class="form-control form-control-sm"
              placeholder="Notes about production progress…">{{ $notes }}</textarea>
</div>
