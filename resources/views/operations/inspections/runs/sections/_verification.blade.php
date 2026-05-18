{{-- Seal Verification / Shipment Verification sections --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $slug  = $runSection->section->slug;
    $items = $data['items'] ?? [];
@endphp

{{-- Seal Verification --}}
@if($slug === 'seal_verification')
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Seal Number</label>
        <input type="text"
               name="sections[{{ $secId }}][data][seal_number]"
               class="form-control form-control-sm"
               value="{{ old("sections.{$secId}.data.seal_number", $data['seal_number'] ?? '') }}"
               placeholder="Seal / bolt seal number">
    </div>
    <div class="col-lg-2 col-md-3">
        <label class="form-label fw-semibold fs-12">Seal Intact?</label>
        <select name="sections[{{ $secId }}][data][seal_intact]" class="form-select form-select-sm">
            <option value="">— Select —</option>
            <option value="Yes" @selected(old("sections.{$secId}.data.seal_intact", $data['seal_intact'] ?? '') === 'Yes')>Yes</option>
            <option value="No"  @selected(old("sections.{$secId}.data.seal_intact", $data['seal_intact'] ?? '') === 'No')>No</option>
        </select>
    </div>
    <div class="col-lg-2 col-md-3">
        <label class="form-label fw-semibold fs-12">Photo Taken?</label>
        <select name="sections[{{ $secId }}][data][seal_photo_taken]" class="form-select form-select-sm">
            <option value="">— Select —</option>
            <option value="Yes" @selected(old("sections.{$secId}.data.seal_photo_taken", $data['seal_photo_taken'] ?? '') === 'Yes')>Yes</option>
            <option value="No"  @selected(old("sections.{$secId}.data.seal_photo_taken", $data['seal_photo_taken'] ?? '') === 'No')>No</option>
        </select>
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Verified By</label>
        <input type="text"
               name="sections[{{ $secId }}][data][verified_by]"
               class="form-control form-control-sm"
               value="{{ old("sections.{$secId}.data.verified_by", $data['verified_by'] ?? '') }}"
               placeholder="Inspector name…">
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold fs-12">Notes</label>
        <input type="text"
               name="sections[{{ $secId }}][data][notes]"
               class="form-control form-control-sm"
               value="{{ old("sections.{$secId}.data.notes", $data['notes'] ?? '') }}"
               placeholder="Seal verification notes…">
    </div>
</div>
@endif

{{-- Checklist items (shipment_verification, or any verification with items) --}}
@if(!empty($items))
<div class="table-responsive mb-3">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3">Checkpoint</th>
                <th style="width:120px">Result</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $idx => $item)
        @php
            $result   = old("sections.{$secId}.data.items.{$idx}.result", $item['result'] ?? null);
            $rowClass = match($result) {
                'Pass' => 'table-success',
                'Fail' => 'table-danger',
                'N/A'  => 'table-light text-muted',
                default => '',
            };
        @endphp
        <tr class="{{ $rowClass }}" style="--bs-table-bg-type: transparent;">
            <td class="ps-3 fw-semibold fs-13">
                <input type="hidden" name="sections[{{ $secId }}][data][items][{{ $idx }}][label]"
                       value="{{ $item['label'] ?? '' }}">
                {{ $item['label'] ?? '' }}
            </td>
            <td>
                <select name="sections[{{ $secId }}][data][items][{{ $idx }}][result]"
                        class="form-select form-select-sm checklist-result"
                        onchange="updateChecklistRow(this)">
                    <option value="">— Select —</option>
                    <option value="Pass" @selected($result === 'Pass')>Pass</option>
                    <option value="Fail" @selected($result === 'Fail')>Fail</option>
                    <option value="N/A"  @selected($result === 'N/A')>N/A</option>
                </select>
            </td>
            <td>
                <input type="text"
                       name="sections[{{ $secId }}][data][items][{{ $idx }}][remarks]"
                       class="form-control form-control-sm"
                       value="{{ old("sections.{$secId}.data.items.{$idx}.remarks", $item['remarks'] ?? '') }}"
                       placeholder="Remarks…">
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Photos --}}
@include('operations.inspections.runs.sections._photo_upload', ['runSection' => $runSection])
