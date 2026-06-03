{{-- Barcode Testing Section --}}
@php
    $data    = old("sections.{$runSection->id}.data", $runSection->data ?? []);
    $status  = $data['barcode_status'] ?? 'functional';
    $remarks = $data['remarks'] ?? '';
@endphp

<div class="row g-3">
    <div class="col-lg-4">
        <label class="form-label fw-semibold">Barcode Status <span class="text-danger">*</span></label>
        <select name="sections[{{ $runSection->id }}][data][barcode_status]"
                class="form-select" id="barcodeStatus-{{ $runSection->id }}">
            <option value="functional"     @selected($status === 'functional')>Functional</option>
            <option value="non-functional" @selected($status === 'non-functional')>Non-Functional</option>
            <option value="partial"        @selected($status === 'partial')>Partial</option>
        </select>
        <small class="text-muted">Scan test result for all barcodes in the lot.</small>
    </div>

    <div class="col-lg-8">
        <label class="form-label fw-semibold">Remarks</label>
        <input type="text"
               name="sections[{{ $runSection->id }}][data][remarks]"
               class="form-control"
               value="{{ $remarks }}"
               placeholder="e.g. 3 out of 50 barcodes failed scan, EAN-13 format…">
    </div>
</div>

<div class="mt-4">
    <label class="form-label fw-semibold">Attachments <small class="text-muted">(photos / videos)</small></label>
    @include('operations.inspections.runs.sections._photo_upload', ['runSection' => $runSection])
</div>
