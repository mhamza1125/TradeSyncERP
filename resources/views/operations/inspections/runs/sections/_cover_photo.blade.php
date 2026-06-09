{{-- Cover Photo Section (single image upload) --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $rsId     = $runSection->id;
    $existing = $runSection->attachments->first();
@endphp

<div id="cover-photo-{{ $rsId }}">

    @if($existing && $existing->isImage())
    {{-- Current photo --}}
    <div class="text-center mb-4">
        <div class="d-inline-block position-relative">
            <img src="{{ $existing->url }}"
                 class="rounded border shadow-sm"
                 style="max-width:320px;max-height:320px;object-fit:contain"
                 alt="Cover Photo"
                 id="cover-img-{{ $rsId }}">
            <button type="button"
                    class="att-delete-btn btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                    data-delete-url="{{ route('inspections.runs.attachments.delete', [$inspection, $run, $existing]) }}"
                    data-thumb-id="att-{{ $existing->id }}"
                    onclick="deleteCoverPhoto{{ $rsId }}(this)">
                <i class="feather-trash-2 me-1"></i>Remove Photo
            </button>
        </div>
    </div>
    <div class="text-center">
        <button type="button" class="btn btn-outline-primary btn-sm" id="cover-change-{{ $rsId }}">
            <i class="feather-refresh-cw me-1"></i>Change Photo
        </button>
    </div>
    @else
    {{-- No photo yet --}}
    <div class="border border-dashed rounded p-5 text-center bg-light-subtle" id="cover-drop-{{ $rsId }}">
        <i class="feather-camera fs-1 text-muted d-block mb-3"></i>
        <p class="fw-semibold mb-1">Upload Cover Photo</p>
        <p class="text-muted fs-13 mb-3">Select a single image to use as the cover for this inspection run</p>
        <button type="button" class="btn btn-primary" id="cover-upload-btn-{{ $rsId }}">
            <i class="feather-upload me-2"></i>Select Photo
        </button>
    </div>
    @endif

    {{-- Hidden file input --}}
    <input type="file" class="att-file-input d-none" id="cover-input-{{ $rsId }}"
           accept="image/*"
           data-upload-url="{{ $uploadUrl }}"
           data-task-key="cover">

    {{-- Progress indicator --}}
    <div id="cover-uploading-{{ $rsId }}" class="text-center mt-3 d-none">
        <span class="spinner-border spinner-border-sm me-2"></span>Uploading photo…
    </div>

</div>

@push('scripts')
<script>
(function () {
    const rsId     = {{ $rsId }};
    const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
    const uploadUrl = @json($uploadUrl);

    function getWrap()    { return document.getElementById('cover-photo-' + rsId); }
    function getSpinner() { return document.getElementById('cover-uploading-' + rsId); }

    function bindInput(input) {
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
                        <div class="text-center mb-4">
                            <div class="d-inline-block position-relative">
                                <img src="${att.url}"
                                     class="rounded border shadow-sm"
                                     style="max-width:320px;max-height:320px;object-fit:contain"
                                     alt="Cover Photo">
                                <button type="button"
                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2"
                                        data-delete-url="${att.delete_url}"
                                        onclick="deleteCoverPhoto${rsId}(this)">
                                    <i class="feather-trash-2 me-1"></i>Remove Photo
                                </button>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="cover-change-${rsId}">
                                <i class="feather-refresh-cw me-1"></i>Change Photo
                            </button>
                        </div>
                        <input type="file" class="att-file-input d-none" id="cover-input-${rsId}"
                               accept="image/*">
                        <div id="cover-uploading-${rsId}" class="text-center mt-3 d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Uploading photo…
                        </div>`;
                    const newInput = document.getElementById('cover-input-' + rsId);
                    document.getElementById('cover-change-' + rsId)
                        ?.addEventListener('click', () => newInput?.click());
                    bindInput(newInput);
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

    bindInput(document.getElementById('cover-input-' + rsId));
    document.getElementById('cover-upload-btn-' + rsId)
        ?.addEventListener('click', () => document.getElementById('cover-input-' + rsId)?.click());
    document.getElementById('cover-change-' + rsId)
        ?.addEventListener('click', () => document.getElementById('cover-input-' + rsId)?.click());
})();

function deleteCoverPhoto{{ $rsId }}(btn) {
    if (!confirm('Remove this cover photo?')) return;
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    fetch(btn.dataset.deleteUrl, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).then(r => {
        if (!r.ok) { alert('Could not remove photo.'); return; }
        const wrap = document.getElementById('cover-photo-{{ $rsId }}');
        if (wrap) {
            wrap.innerHTML = `
                <div class="border border-dashed rounded p-5 text-center bg-light-subtle">
                    <i class="feather-camera fs-1 text-muted d-block mb-3"></i>
                    <p class="fw-semibold mb-1">Upload Cover Photo</p>
                    <p class="text-muted fs-13 mb-3">Select a single image to use as the cover for this inspection run</p>
                    <button type="button" class="btn btn-primary" id="cover-upload-btn-{{ $rsId }}">
                        <i class="feather-upload me-2"></i>Select Photo
                    </button>
                </div>
                <input type="file" class="att-file-input d-none" id="cover-input-{{ $rsId }}"
                       accept="image/*">
                <div id="cover-uploading-{{ $rsId }}" class="text-center mt-3 d-none">
                    <span class="spinner-border spinner-border-sm me-2"></span>Uploading photo…
                </div>`;
            // Re-wire the new upload button
            const newInput = document.getElementById('cover-input-{{ $rsId }}');
            document.getElementById('cover-upload-btn-{{ $rsId }}')
                ?.addEventListener('click', () => newInput?.click());
            if (window._bindCoverInput) window._bindCoverInput(newInput);
        }
    }).catch(() => alert('Network error.'));
}
</script>
@endpush
