{{-- Generic Task List Section --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $tasks    = $runSection->section->default_data['tasks'] ?? [];
    $taskData = $runSection->data['tasks'] ?? [];
    $rsId     = $runSection->id;

    // Group existing attachments by task_key
    $attsByTask = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');

    $colorFor = fn($opt) => match (strtolower($opt)) {
        'yes', 'pass', 'acceptable' => 'success',
        'no', 'fail'                => 'danger',
        'n/a'                       => 'secondary',
        default                     => 'warning',
    };
    $rowClassFor = fn($opt) => match (strtolower((string) $opt)) {
        'yes', 'pass', 'acceptable' => 'table-success',
        'no', 'fail'                => 'table-danger',
        'n/a'                       => 'table-light text-muted',
        default                     => '',
    };
@endphp

@if(empty($tasks))
<div class="text-center py-4 text-muted">
    <i class="feather-sliders fs-2 d-block mb-2 opacity-50"></i>
    <p class="mb-0 fs-13">No tasks defined for this section.</p>
    <small>Edit the section template to add tasks.</small>
</div>
@else
<div class="table-responsive" data-section-wrapper="{{ $rsId }}">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3" style="width:36px">#</th>
                <th>Task</th>
                <th style="width:240px">Status</th>
                <th style="width:280px">Attachments</th>
            </tr>
        </thead>
        <tbody>
        @foreach($tasks as $idx => $task)
        @php
            $key      = $task['key'];
            $options  = $task['options'] ?? ['Yes', 'No'];
            $selected = old("sections.{$rsId}.data.tasks.{$key}.selected", $taskData[$key]['selected'] ?? '');
            $taskAtts = $attsByTask->get($key, collect());
            $rowClass = $rowClassFor($selected);
        @endphp
        <tr class="{{ $rowClass }}" data-task-key="{{ $key }}">
            <td class="text-muted ps-3">{{ $idx + 1 }}</td>
            <td class="fw-semibold fs-13">
                {{ $task['label'] }}
                @if(!empty($task['required']))
                    <span class="text-danger ms-1" title="Required">*</span>
                @endif
            </td>
            <td>
                <div class="d-flex flex-wrap gap-1 task-toggle-group">
                    @foreach($options as $opt)
                    @php $color = $colorFor($opt); @endphp
                    <label class="task-option-label btn btn-sm {{ $selected === $opt ? "btn-{$color}" : 'btn-outline-secondary' }}"
                           style="font-size:12px;min-width:64px;cursor:pointer"
                           data-color="{{ $color }}"
                           data-row-class="{{ $rowClassFor($opt) }}">
                        <input type="radio"
                               name="sections[{{ $rsId }}][data][tasks][{{ $key }}][selected]"
                               value="{{ $opt }}"
                               class="task-radio d-none"
                               {{ $selected === $opt ? 'checked' : '' }}>
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>
            </td>
            <td>
                @if($task['has_attachments'] ?? true)
                <div class="attachment-area" data-upload-url="{{ $uploadUrl }}" data-task-key="{{ $key }}">
                    <div class="att-previews d-flex flex-wrap gap-2 mb-1">
                        @foreach($taskAtts as $att)
                        <div class="att-thumb position-relative d-inline-block" id="att-{{ $att->id }}">
                            @if($att->isImage())
                                <a href="{{ $att->url }}" target="_blank" rel="noopener noreferrer">
                                    <img src="{{ $att->url }}" class="rounded border"
                                         style="width:40px;height:40px;object-fit:cover" alt="">
                                </a>
                            @else
                                <a href="{{ $att->url }}" target="_blank" rel="noopener noreferrer"
                                   class="d-flex align-items-center justify-content-center border rounded bg-light text-decoration-none"
                                   style="width:40px;height:40px">
                                    <i class="feather-file text-muted" style="font-size:14px"></i>
                                </a>
                            @endif
                            <button type="button"
                                    class="att-delete-btn btn btn-danger btn-sm p-0 position-absolute top-0 end-0 d-flex align-items-center justify-content-center"
                                    style="width:16px;height:16px;font-size:9px;border-radius:50%;margin:-4px;z-index:1;"
                                    data-delete-url="{{ route('inspections.runs.attachments.delete', [$inspection, $run, $att]) }}"
                                    data-thumb-id="att-{{ $att->id }}">×</button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="add-files-btn btn btn-sm btn-light border py-0 px-2" style="font-size:11px">
                        <i class="feather-plus me-1"></i>Add Files
                    </button>
                    <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf,.doc,.docx">
                </div>
                @else
                <span class="text-muted fs-12">—</span>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

@push('scripts')
<script>
(function () {
    // Highlight task option buttons and recolor the row when a status radio changes
    const wrapper{{ $rsId }} = document.querySelector('[data-section-wrapper="{{ $rsId }}"]');
    if (!wrapper{{ $rsId }}) return;

    wrapper{{ $rsId }}.querySelectorAll('.task-option-label').forEach(lbl => {
        const radio = lbl.querySelector('.task-radio');
        radio?.addEventListener('change', function () {
            const row = this.closest('tr[data-task-key]');
            if (!row) return;

            row.querySelectorAll('.task-option-label').forEach(l => {
                const r = l.querySelector('.task-radio');
                const color = l.dataset.color || 'secondary';
                l.classList.toggle(`btn-${color}`, r.checked);
                l.classList.toggle('btn-outline-secondary', !r.checked);
            });

            row.classList.remove('table-success', 'table-danger', 'table-light', 'text-muted');
            const rowClass = lbl.dataset.rowClass || '';
            if (rowClass) rowClass.split(' ').forEach(c => row.classList.add(c));
        });
    });
})();
</script>
@endpush
