{{-- Reusable photo upload strip for any section --}}
@php $secId = $runSection->id; @endphp

@if($runSection->attachments->count())
<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach($runSection->attachments as $att)
    <div class="position-relative" style="width:64px;" id="att-{{ $att->id }}">
        @if($att->isImage())
            <a href="{{ $att->url }}" target="_blank">
                <img src="{{ $att->url }}" class="rounded border"
                     style="width:64px;height:64px;object-fit:cover;"
                     alt="{{ $att->title }}">
            </a>
        @else
            <a href="{{ $att->url }}" target="_blank"
               class="d-flex align-items-center justify-content-center border rounded bg-light text-muted"
               style="width:64px;height:64px;" title="{{ $att->file_name }}">
                <i class="feather-file" style="font-size:20px"></i>
            </a>
        @endif
        <button type="button"
                class="btn btn-danger p-0 position-absolute top-0 end-0 rounded-circle delete-attachment"
                style="width:16px;height:16px;font-size:9px;line-height:1;"
                data-att-id="{{ $att->id }}"
                data-target="att-{{ $att->id }}"
                title="Remove">×</button>
    </div>
    @endforeach
</div>
@endif

<label class="btn btn-xs btn-light-brand mb-0">
    <i class="feather-paperclip me-1"></i>Attach Photos / Files
    <input type="file"
           name="section_files[{{ $secId }}][]"
           multiple
           accept="image/*,.pdf"
           class="d-none file-input"
           data-preview="section-preview-{{ $secId }}">
</label>
<div id="section-preview-{{ $secId }}" class="d-flex flex-wrap gap-2 mt-2"></div>
