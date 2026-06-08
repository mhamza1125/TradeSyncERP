{{-- Reusable photo/file upload strip for any section — standardized AJAX append/remove --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run, optional $taskKey --}}
@php
    $taskKey  = $taskKey ?? '';
    $attsHere = $taskKey
        ? $runSection->attachments->where('task_key', $taskKey)
        : $runSection->attachments->whereNull('task_key');
@endphp

<div class="attachment-area" data-upload-url="{{ $uploadUrl }}" data-task-key="{{ $taskKey }}">
    <div class="att-previews d-flex flex-wrap gap-2 mb-2">
        @foreach($attsHere as $att)
        <div class="att-thumb position-relative d-inline-block" id="att-{{ $att->id }}">
            @if($att->isImage())
                <img src="{{ $att->url }}" class="rounded border"
                     style="width:64px;height:64px;object-fit:cover" alt="">
            @else
                <div class="d-flex flex-column align-items-center justify-content-center border rounded bg-light"
                     style="width:64px;height:64px">
                    <i class="feather-file text-muted" style="font-size:20px"></i>
                    <small class="text-muted mt-1"
                           style="font-size:9px;max-width:60px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $att->file_name }}
                    </small>
                </div>
            @endif
            <button type="button"
                    class="att-delete-btn btn btn-danger btn-sm p-0 position-absolute top-0 end-0 d-flex align-items-center justify-content-center"
                    style="width:18px;height:18px;font-size:10px;border-radius:50%;margin:-4px;z-index:1;"
                    data-delete-url="{{ route('inspections.runs.attachments.delete', [$inspection, $run, $att]) }}"
                    data-thumb-id="att-{{ $att->id }}">×</button>
        </div>
        @endforeach
    </div>
    <button type="button" class="add-files-btn btn btn-sm btn-light-brand w-100">
        <i class="feather-paperclip me-1"></i>Attach Photos and Files
    </button>
    <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf,.doc,.docx">
</div>
