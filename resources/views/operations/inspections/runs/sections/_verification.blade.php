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
        @include('operations.inspections.runs.sections._result_toggle', [
            'name'    => "sections[{$secId}][data][seal_intact]",
            'value'   => old("sections.{$secId}.data.seal_intact", $data['seal_intact'] ?? ''),
            'options' => ['Yes' => 'success', 'No' => 'danger'],
        ])
    </div>
    <div class="col-lg-2 col-md-3">
        <label class="form-label fw-semibold fs-12">Photo Taken?</label>
        @include('operations.inspections.runs.sections._result_toggle', [
            'name'    => "sections[{$secId}][data][seal_photo_taken]",
            'value'   => old("sections.{$secId}.data.seal_photo_taken", $data['seal_photo_taken'] ?? ''),
            'options' => ['Yes' => 'success', 'No' => 'danger'],
        ])
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
                <th style="width:240px">Result</th>
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
                @include('operations.inspections.runs.sections._result_toggle', [
                    'name'  => "sections[{$secId}][data][items][{$idx}][result]",
                    'value' => $result,
                ])
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Photos --}}
@include('operations.inspections.runs.sections._photo_upload', [
    'runSection' => $runSection,
    'uploadUrl'  => $uploadUrl,
    'inspection' => $inspection,
    'run'        => $run,
    'taskKey'    => 'verification_photos',
])
