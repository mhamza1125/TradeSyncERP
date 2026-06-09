{{-- Product Screening — cover photo + gallery image uploads --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $data          = $runSection->data ?? [];
    $secId         = $runSection->id;
    $existingCover = $runSection->attachments->where('task_key', 'cover')->first();
@endphp

{{-- ── Cover Photo ────────────────────────────────────────────────────────────── --}}
<div class="mb-4 pb-4 border-bottom">
    <h6 class="fw-semibold mb-3 fs-13">
        <i class="feather-image me-1 text-purple"></i>Cover Photo
        <small class="text-muted fw-normal ms-1">— single featured image for the report</small>
    </h6>

    <div id="cover-photo-{{ $secId }}">

        @if($existingCover && $existingCover->isImage())
        <div class="text-center mb-3">
            <div class="d-inline-block position-relative">
                <a href="{{ $existingCover->url }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ $existingCover->url }}"
                         class="rounded border shadow-sm"
                         style="max-width:320px;max-height:280px;object-fit:contain"
                         alt="Cover Photo">
                </a>
                <button type="button"
                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                        data-delete-url="{{ route('inspections.runs.attachments.delete', [$inspection, $run, $existingCover]) }}"
                        onclick="deleteCoverPhotoPS{{ $secId }}(this)">
                    <i class="feather-trash-2 me-1"></i>Remove
                </button>
            </div>
        </div>
        <div class="text-center">
            <button type="button" class="btn btn-outline-primary btn-sm" id="cover-change-{{ $secId }}">
                <i class="feather-refresh-cw me-1"></i>Change Cover Photo
            </button>
        </div>
        @else
        <div class="border border-dashed rounded p-4 text-center bg-light-subtle" id="cover-drop-{{ $secId }}">
            <i class="feather-camera fs-1 text-muted d-block mb-2"></i>
            <p class="fw-semibold mb-1 fs-13">Upload Cover Photo</p>
            <p class="text-muted fs-12 mb-3">Single featured image for the inspection report</p>
            <button type="button" class="btn btn-primary btn-sm" id="cover-upload-btn-{{ $secId }}">
                <i class="feather-upload me-2"></i>Select Photo
            </button>
        </div>
        @endif

        <input type="file" class="att-file-input d-none" id="cover-input-{{ $secId }}"
               accept="image/*"
               data-upload-url="{{ $uploadUrl }}"
               data-task-key="cover">

        <div id="cover-uploading-{{ $secId }}" class="text-center mt-2 d-none">
            <span class="spinner-border spinner-border-sm me-2"></span>Uploading photo…
        </div>

    </div>
</div>

{{-- ── Gallery Photos ──────────────────────────────────────────────────────────── --}}
<div class="mb-4">
    <h6 class="fw-semibold mb-2 fs-13">
        <i class="feather-camera me-1 text-muted"></i>Gallery Photos
        <small class="text-muted fw-normal ms-1">— product, packaging, labels, defects, counter samples</small>
    </h6>
    @include('operations.inspections.runs.sections._photo_upload', [
        'runSection' => $runSection,
        'uploadUrl'  => $uploadUrl,
        'inspection' => $inspection,
        'run'        => $run,
        'taskKey'    => 'screening_photos',
    ])
</div>

{{-- ── Screening Notes ─────────────────────────────────────────────────────────── --}}
<div>
    <label class="form-label fw-semibold fs-12">Screening Notes</label>
    <textarea name="sections[{{ $secId }}][data][notes]"
              rows="2"
              class="form-control form-control-sm"
              placeholder="General notes about product appearance, screening findings…">{{ old("sections.{$secId}.data.notes", $data['notes'] ?? '') }}</textarea>
</div>

@push('scripts')
<script>
(function () {
    const rsId      = {{ $secId }};
    const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
    const uploadUrl = @json($uploadUrl);

    function getWrap()    { return document.getElementById('cover-photo-' + rsId); }
    function getSpinner() { return document.getElementById('cover-uploading-' + rsId); }

    function bindCoverInput(input) {
        if (!input) return;
        input.addEventListener('change', async function () {
            const file = this.files[0];
            if (!file) return;

            const spinner = getSpinner();
            if (spinner) spinner.classList.remove('d-none');
            input.disabled = true;

            const fd = new FormData();
            fd.append('files[]', file);
            fd.append('task_key', 'cover');

            try {
                const res = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: fd,
                });
                if (!res.ok) throw new Error('Upload failed');
                const { attachments } = await res.json();
                const att = attachments?.[0];
                if (!att) throw new Error('No attachment');

                const wrap = getWrap();
                if (wrap) {
                    wrap.innerHTML = `
                        <div class="text-center mb-3">
                            <div class="d-inline-block position-relative">
                                <a href="${att.url}" target="_blank" rel="noopener noreferrer">
                                    <img src="${att.url}"
                                         class="rounded border shadow-sm"
                                         style="max-width:320px;max-height:280px;object-fit:contain"
                                         alt="Cover Photo">
                                </a>
                                <button type="button"
                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                        data-delete-url="${att.delete_url}"
                                        onclick="deleteCoverPhotoPS${rsId}(this)">
                                    <i class="feather-trash-2 me-1"></i>Remove
                                </button>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="cover-change-${rsId}">
                                <i class="feather-refresh-cw me-1"></i>Change Cover Photo
                            </button>
                        </div>
                        <input type="file" class="att-file-input d-none" id="cover-input-${rsId}" accept="image/*">
                        <div id="cover-uploading-${rsId}" class="text-center mt-2 d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Uploading photo…
                        </div>`;
                    const newInput = document.getElementById('cover-input-' + rsId);
                    document.getElementById('cover-change-' + rsId)
                        ?.addEventListener('click', () => newInput?.click());
                    bindCoverInput(newInput);
                }
            } catch (e) {
                alert('Photo upload failed. Please try again.');
            } finally {
                const sp = getSpinner();
                if (sp) sp.classList.add('d-none');
                input.disabled = false;
                this.value = '';
            }
        });
    }

    // Expose so the delete function (outside IIFE) can re-bind after DOM reset
    window['_bindCoverPS_' + rsId] = bindCoverInput;

    bindCoverInput(document.getElementById('cover-input-' + rsId));
    document.getElementById('cover-upload-btn-' + rsId)
        ?.addEventListener('click', () => document.getElementById('cover-input-' + rsId)?.click());
    document.getElementById('cover-change-' + rsId)
        ?.addEventListener('click', () => document.getElementById('cover-input-' + rsId)?.click());
})();

function deleteCoverPhotoPS{{ $secId }}(btn) {
    if (!confirm('Remove this cover photo?')) return;
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    fetch(btn.dataset.deleteUrl, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).then(r => {
        if (!r.ok) { alert('Could not remove photo.'); return; }
        const wrap = document.getElementById('cover-photo-{{ $secId }}');
        if (wrap) {
            wrap.innerHTML = `
                <div class="border border-dashed rounded p-4 text-center bg-light-subtle">
                    <i class="feather-camera fs-1 text-muted d-block mb-2"></i>
                    <p class="fw-semibold mb-1 fs-13">Upload Cover Photo</p>
                    <p class="text-muted fs-12 mb-3">Single featured image for the inspection report</p>
                    <button type="button" class="btn btn-primary btn-sm" id="cover-upload-btn-{{ $secId }}">
                        <i class="feather-upload me-2"></i>Select Photo
                    </button>
                </div>
                <input type="file" class="att-file-input d-none" id="cover-input-{{ $secId }}" accept="image/*">
                <div id="cover-uploading-{{ $secId }}" class="text-center mt-2 d-none">
                    <span class="spinner-border spinner-border-sm me-2"></span>Uploading photo…
                </div>`;
            const newInput = document.getElementById('cover-input-{{ $secId }}');
            document.getElementById('cover-upload-btn-{{ $secId }}')
                ?.addEventListener('click', () => newInput?.click());
            const bindFn = window['_bindCoverPS_{{ $secId }}'];
            if (bindFn) bindFn(newInput);
        }
    }).catch(() => alert('Network error.'));
}
</script>
@endpush
