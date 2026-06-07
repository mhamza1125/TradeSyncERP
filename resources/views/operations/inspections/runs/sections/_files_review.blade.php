{{-- Files To Review Section --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $d            = $runSection->data ?? [];
    $acknowledged = (bool) ($d['acknowledged'] ?? false);
    $notes        = $d['notes'] ?? '';
    $rsId         = $runSection->id;
    $existingFiles = $runSection->attachments;
@endphp

<div data-section-wrapper="{{ $rsId }}">

    {{-- Document attachment area --}}
    <div class="mb-4">
        <h6 class="fw-semibold mb-1">Reference Documents</h6>
        <p class="text-muted fs-13 mb-3">
            Attach PDFs, specifications, or reference documents that the inspector needs to review before proceeding.
        </p>

        <div class="attachment-area" data-upload-url="{{ $uploadUrl }}" data-task-key="review_files">
            <div class="att-previews d-flex flex-wrap gap-2 mb-2">
                @forelse($existingFiles as $att)
                <div class="att-thumb position-relative d-inline-block" id="att-{{ $att->id }}">
                    @if($att->isImage())
                        <img src="{{ $att->url }}" class="rounded border"
                             style="width:72px;height:72px;object-fit:cover" alt="">
                    @else
                        <a href="{{ $att->url }}" target="_blank"
                           class="d-flex flex-column align-items-center justify-content-center border rounded text-decoration-none bg-light"
                           style="width:72px;height:72px">
                            <i class="feather-file-text text-danger" style="font-size:22px"></i>
                            <small class="text-muted mt-1 text-center"
                                   style="font-size:9px;max-width:68px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                {{ $att->file_name }}
                            </small>
                        </a>
                    @endif
                    <button type="button"
                            class="att-delete-btn btn btn-danger btn-sm p-0 position-absolute top-0 end-0 d-flex align-items-center justify-content-center"
                            style="width:18px;height:18px;font-size:10px;border-radius:50%;margin:-4px;z-index:1;"
                            data-delete-url="{{ route('inspections.runs.attachments.delete', [$inspection, $run, $att]) }}"
                            data-thumb-id="att-{{ $att->id }}">×</button>
                </div>
                @empty
                <p class="text-muted fs-13 fst-italic">No documents attached yet.</p>
                @endforelse
            </div>
            <button type="button" class="add-files-btn btn btn-sm btn-outline-primary">
                <i class="feather-upload me-1"></i>Attach Documents / PDFs
            </button>
            <input type="file" class="att-file-input d-none" multiple
                   accept=".pdf,.doc,.docx,.xls,.xlsx,image/*">
        </div>
    </div>

    <hr class="my-3">

    {{-- Acknowledgment checkbox --}}
    <div class="p-3 rounded {{ $acknowledged ? 'bg-soft-success border border-success' : 'bg-soft-warning border border-warning' }} mb-3">
        <div class="form-check mb-0">
            <input type="hidden"
                   name="sections[{{ $rsId }}][data][acknowledged]"
                   value="0">
            <input type="checkbox"
                   class="form-check-input files-ack-check"
                   id="ack-{{ $rsId }}"
                   name="sections[{{ $rsId }}][data][acknowledged]"
                   value="1"
                   data-section-id="{{ $rsId }}"
                   {{ $acknowledged ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="ack-{{ $rsId }}">
                <i class="feather-check-square me-1"></i>
                I confirm that I have reviewed all attached documents above
            </label>
        </div>
    </div>

    {{-- Notes --}}
    <div>
        <label class="form-label fw-semibold">Review Notes <span class="text-muted fw-normal">(optional)</span></label>
        <textarea name="sections[{{ $rsId }}][data][notes]"
                  class="form-control"
                  rows="3"
                  placeholder="Notes about the reviewed documents…">{{ old("sections.{$rsId}.data.notes", $notes) }}</textarea>
    </div>

</div>

@push('scripts')
<script>
(function () {
    const rsId = {{ $rsId }};
    document.querySelectorAll('.files-ack-check[data-section-id="' + rsId + '"]').forEach(cb => {
        cb.addEventListener('change', function () {
            const container = this.closest('.p-3.rounded');
            if (this.checked) {
                container.classList.replace('bg-soft-warning', 'bg-soft-success');
                container.classList.replace('border-warning', 'border-success');
            } else {
                container.classList.replace('bg-soft-success', 'bg-soft-warning');
                container.classList.replace('border-success', 'border-warning');
            }
            // Update status badge
            const hidden = document.getElementById('hidden-status-' + rsId);
            const badge  = document.getElementById('status-badge-' + rsId);
            if (hidden) hidden.value = this.checked ? 'complete' : 'pending';
            if (badge) {
                badge.className = this.checked ? 'badge bg-soft-success text-success fs-11' : 'badge bg-soft-secondary text-secondary fs-11';
                badge.textContent = this.checked ? 'Acknowledged' : 'Pending Review';
            }
        });
    });
})();
</script>
@endpush
