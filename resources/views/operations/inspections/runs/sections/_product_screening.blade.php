{{-- Product Screening — photo gallery upload --}}
@php
    $data  = $runSection->data ?? [];
    $secId = $runSection->id;
@endphp

<p class="text-muted fs-13 mb-3">
    Capture product screening photos (workmanship overview, packaging, labels, defects, counter samples, etc.).
    Uploaded photos will appear in the inspection report.
</p>

{{-- Existing photos --}}
@if($runSection->attachments->count())
<div class="d-flex flex-wrap gap-3 mb-4">
    @foreach($runSection->attachments as $att)
    <div class="position-relative" style="width:80px;" id="att-{{ $att->id }}">
        @if($att->isImage())
            <a href="{{ $att->url }}" target="_blank">
                <img src="{{ $att->url }}" class="rounded border"
                     style="width:80px;height:80px;object-fit:cover;"
                     alt="{{ $att->title }}">
            </a>
        @else
            <a href="{{ $att->url }}" target="_blank"
               class="d-flex align-items-center justify-content-center border rounded bg-light text-muted"
               style="width:80px;height:80px;" title="{{ $att->file_name }}">
                <i class="feather-file" style="font-size:24px"></i>
            </a>
        @endif
        <button type="button"
                class="btn btn-danger p-0 position-absolute top-0 end-0 rounded-circle delete-attachment"
                style="width:18px;height:18px;font-size:10px;line-height:1;"
                data-att-id="{{ $att->id }}"
                data-target="att-{{ $att->id }}"
                title="Remove">×</button>
        <small class="text-muted d-block mt-1 text-center" style="font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:80px;">
            {{ $att->title }}
        </small>
    </div>
    @endforeach
</div>
@endif

{{-- Upload area --}}
<div class="border rounded p-4 text-center bg-light mb-3" style="border-style: dashed !important;">
    <i class="feather-upload-cloud text-muted" style="font-size:2rem;opacity:.5"></i>
    <p class="text-muted mb-2 mt-2 fs-13">Drag photos here or click to browse</p>
    <label class="btn btn-sm btn-light-brand mb-0">
        <i class="feather-camera me-1"></i>Browse Photos
        <input type="file"
               name="section_files[{{ $secId }}][]"
               multiple
               accept="image/*,.pdf"
               class="d-none file-input"
               data-preview="section-preview-{{ $secId }}">
    </label>
</div>
<div id="section-preview-{{ $secId }}" class="d-flex flex-wrap gap-2 mb-3"></div>

<div>
    <label class="form-label fw-semibold fs-12">Screening Notes</label>
    <textarea name="sections[{{ $secId }}][data][notes]"
              rows="2"
              class="form-control form-control-sm"
              placeholder="General notes about product appearance, screening findings…">{{ old("sections.{$secId}.data.notes", $data['notes'] ?? '') }}</textarea>
</div>
