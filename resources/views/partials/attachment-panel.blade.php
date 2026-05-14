{{--
    Reusable attachment upload panel.
    Variables required:
    - $attachEntity     : the Eloquent model instance (with attachments relation loaded)
    - $attachEntityType : route type string e.g. 'customers', 'vendors', 'employees'
    Optional:
    - $attachLabel      : panel heading (defaults to 'Attachments')
--}}
@php
    $panelLabel  = $attachLabel ?? 'Attachments';
    $existingAtt = $attachEntity?->attachments ?? collect();
@endphp

<div class="card stretch stretch-full mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ $panelLabel }}</h5>
    </div>
    <div class="card-body">
        {{-- Upload Form --}}
        <form action="{{ route('attachments.store', [$attachEntityType, $attachEntity->id]) }}"
              method="POST" enctype="multipart/form-data" id="attachUploadForm_{{ $attachEntity->id }}">
            @csrf
            <div id="newAttachRows_{{ $attachEntity->id }}">
                <div class="row attach-row mb-3">
                    <div class="col-lg-5">
                        <input type="text" name="attachment_titles[0]" class="form-control"
                               placeholder="Title / document name" required>
                    </div>
                    <div class="col-lg-6">
                        <input type="file" name="attachments[0]" class="form-control" required>
                    </div>
                    <div class="col-lg-1 d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-light remove-attach-row" style="visibility:hidden;">
                            <i class="feather-trash-2 text-danger"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-1">
                <button type="button" class="btn btn-sm btn-light-brand add-attach-row"
                        data-target="newAttachRows_{{ $attachEntity->id }}">
                    <i class="feather-plus me-1"></i> Add Another File
                </button>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="feather-upload me-1"></i> Upload
                </button>
            </div>
        </form>

        {{-- Existing Attachments --}}
        @if($existingAtt->count())
        <div class="mt-4">
            <h6 class="text-muted mb-2">Uploaded Files</h6>
            @foreach($existingAtt as $att)
            <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded">
                <i class="feather-{{ $att->isImage() ? 'image' : 'file-text' }} text-primary fs-18"></i>
                <div class="flex-grow-1">
                    <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="fw-medium">{{ $att->title }}</a>
                    <div class="text-muted fs-11">{{ $att->humanFileSize() }} &middot; {{ $att->created_at->format('d M Y') }}</div>
                </div>
                <form action="{{ route('attachments.destroy', $att->id) }}" method="POST"
                      onsubmit="return confirm('Remove this attachment?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-icon btn-light" title="Remove">
                        <i class="feather-trash-2 text-danger"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    const counters = {};

    document.querySelectorAll('.add-attach-row').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId  = this.dataset.target;
            counters[targetId] = (counters[targetId] || 1);
            const idx       = counters[targetId]++;
            const container = document.getElementById(targetId);
            const row = document.createElement('div');
            row.className = 'row attach-row mb-3';
            row.innerHTML = `
                <div class="col-lg-5">
                    <input type="text" name="attachment_titles[${idx}]" class="form-control" placeholder="Title / document name" required>
                </div>
                <div class="col-lg-6">
                    <input type="file" name="attachments[${idx}]" class="form-control" required>
                </div>
                <div class="col-lg-1 d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-light remove-attach-row">
                        <i class="feather-trash-2 text-danger"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-attach-row')) {
            const row = e.target.closest('.attach-row');
            const container = row.parentElement;
            if (container.querySelectorAll('.attach-row').length > 1) {
                row.remove();
            }
        }
    });
})();
</script>
@endpush
