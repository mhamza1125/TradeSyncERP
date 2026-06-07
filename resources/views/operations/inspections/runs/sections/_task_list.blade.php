{{-- Generic Task List Section --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $tasks    = $runSection->section->default_data['tasks'] ?? [];
    $taskData = $runSection->data['tasks'] ?? [];
    $rsId     = $runSection->id;

    // Group existing attachments by task_key
    $attsByTask = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');
@endphp

@if(empty($tasks))
<div class="text-center py-4 text-muted">
    <i class="feather-sliders fs-2 d-block mb-2 opacity-50"></i>
    <p class="mb-0 fs-13">No tasks defined for this section.</p>
    <small>Edit the section template to add tasks.</small>
</div>
@else
<div data-section-wrapper="{{ $rsId }}">
    @foreach($tasks as $task)
    @php
        $key      = $task['key'];
        $options  = $task['options'] ?? ['Yes', 'No'];
        $selected = old("sections.{$rsId}.data.tasks.{$key}.selected", $taskData[$key]['selected'] ?? '');
        $comment  = old("sections.{$rsId}.data.tasks.{$key}.comment",  $taskData[$key]['comment']  ?? '');
        $taskAtts = $attsByTask->get($key, collect());
        $isDone   = !empty($selected);
    @endphp

    <div class="task-card p-3 mb-3 rounded border {{ $isDone ? 'border-success bg-soft-success' : 'border-light bg-white' }} transition"
         style="transition:background .2s"
         data-task-key="{{ $key }}">

        {{-- Task label row --}}
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="fw-semibold fs-14">{{ $task['label'] }}</span>
            @if($isDone)
                <i class="feather-check-circle text-success fs-5"></i>
            @else
                <i class="feather-circle text-muted fs-5" style="opacity:.4"></i>
            @endif
        </div>

        {{-- Option buttons --}}
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($options as $opt)
            <label class="task-option-label btn btn-sm {{ $selected === $opt ? 'btn-primary' : 'btn-outline-secondary' }}"
                   style="font-size:13px;min-width:80px;cursor:pointer">
                <input type="radio"
                       name="sections[{{ $rsId }}][data][tasks][{{ $key }}][selected]"
                       value="{{ $opt }}"
                       class="task-radio d-none"
                       {{ $selected === $opt ? 'checked' : '' }}>
                {{ $opt }}
            </label>
            @endforeach
        </div>

        {{-- Comment --}}
        @if($task['has_comment'] ?? true)
        <div class="mb-3">
            <input type="text"
                   name="sections[{{ $rsId }}][data][tasks][{{ $key }}][comment]"
                   class="form-control form-control-sm"
                   value="{{ $comment }}"
                   placeholder="Comments (optional)…">
        </div>
        @endif

        {{-- Attachments --}}
        @if($task['has_attachments'] ?? true)
        <div class="attachment-area" data-upload-url="{{ $uploadUrl }}" data-task-key="{{ $key }}">
            {{-- Existing attachments --}}
            <div class="att-previews d-flex flex-wrap gap-2 mb-2">
                @foreach($taskAtts as $att)
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
            {{-- Add files button --}}
            <button type="button" class="add-files-btn btn btn-sm btn-light border">
                <i class="feather-plus me-1"></i>Add Files
            </button>
            <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf,.doc,.docx">
        </div>
        @endif

    </div>
    @endforeach
</div>
@endif

@push('scripts')
<script>
(function () {
    // Highlight task option buttons when radio changes
    const wrapper{{ $rsId }} = document.querySelector('[data-section-wrapper="{{ $rsId }}"]');
    if (!wrapper{{ $rsId }}) return;

    wrapper{{ $rsId }}.querySelectorAll('.task-option-label').forEach(lbl => {
        const radio = lbl.querySelector('.task-radio');
        radio?.addEventListener('change', function () {
            // Update button states in this task card
            const card = this.closest('[data-task-key]');
            card.querySelectorAll('.task-option-label').forEach(l => {
                const isChecked = l.querySelector('.task-radio')?.checked;
                l.classList.toggle('btn-primary', isChecked);
                l.classList.toggle('btn-outline-secondary', !isChecked);
            });
            // Toggle done state on card
            card.classList.toggle('border-success', true);
            card.classList.toggle('bg-soft-success', true);
            card.classList.toggle('border-light', false);
            card.querySelector('.feather-circle')?.classList.replace('feather-circle', 'feather-check-circle');
        });
    });
})();
</script>
@endpush
