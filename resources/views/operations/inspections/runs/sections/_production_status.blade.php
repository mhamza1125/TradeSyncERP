{{-- Production Status — multi-row stage tracking (mirrors Defect Recording pattern) --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $d          = $runSection->data ?? [];
    $selections = collect($d['selections'] ?? [])->filter(fn($s) => !empty($s['stage']))->values();
    $rsId       = $runSection->id;
    $notes      = old("sections.{$rsId}.data.notes", $d['notes'] ?? '');
    $attsByTask = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');

    $stageKey = fn(string $stage) => 'ps_' . preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', $stage)));

    $deleteUrlTpl    = route('inspections.runs.attachments.delete', [$inspection, $run, '__ATT__']);
    $savedSelections = $selections->map(fn($s) => [
        'stage'       => $s['stage'],
        'percentage'  => $s['percentage'] ?? '',
        'quantity'    => $s['quantity']   ?? '',
        'comment'     => $s['comment']    ?? '',
        'attachments' => $attsByTask->get($stageKey($s['stage']), collect())->map(fn($a) => [
            'id'      => $a->id,
            'url'     => $a->url,
            'isImage' => $a->isImage(),
            'name'    => $a->file_name,
        ])->values(),
    ])->values();

    $availableStages = [
        'AT CUTTING STAGE',
        'AT PRINTING STAGE',
        'AT EMBROIDERY STAGE',
        'AT STITCHING STAGE',
        'AT INTERNAL QC STAGE',
        'AT PACKING STAGE',
        'AT FINISHING STAGE',
        'AT WASHING STAGE',
        'AT IRONING STAGE',
        'MATERIAL EVALUATION',
        'AT BUTTONING STAGE',
        'AT ASSEMBLY STAGE',
        'AT COLLAR STAGE',
        'AT PRESS STAGE',
        'AT LASTING STAGE',
    ];
@endphp

<p class="text-muted fs-13 mb-3">
    Select each production stage to record and track. Each stage can capture percentage completion,
    output quantity, status notes, and photo evidence.
</p>

<div class="d-flex gap-2 mb-3">
    <div class="flex-grow-1">
        <select id="stageAddDropdown-{{ $rsId }}" placeholder="Search production stages…"></select>
    </div>
    <button type="button" id="addStageBtn-{{ $rsId }}" class="btn btn-light-brand">
        <i class="feather-plus me-1"></i>Add Stage
    </button>
</div>

<div id="stagesTableWrap-{{ $rsId }}" class="border rounded" style="{{ $selections->isEmpty() ? 'display:none' : '' }}">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:36px">#</th>
                <th>Production Stage</th>
                <th style="width:115px">% Complete</th>
                <th style="width:115px">Qty / Output</th>
                <th style="width:210px">Status Notes</th>
                <th style="width:190px">Photos</th>
                <th style="width:40px"></th>
            </tr>
        </thead>
        <tbody id="stagesTableBody-{{ $rsId }}"></tbody>
    </table>
</div>
<div id="noStagesMsg-{{ $rsId }}" class="text-muted fs-12 mt-1" style="{{ $selections->isEmpty() ? '' : 'display:none' }}">
    No production stages added yet. Use the dropdown above to select a stage.
</div>

<div class="mt-4">
    <label class="form-label fw-semibold fs-12">General Remarks</label>
    <textarea name="sections[{{ $rsId }}][data][notes]"
              rows="2"
              class="form-control form-control-sm"
              placeholder="Overall production notes or observations…">{{ $notes }}</textarea>
</div>

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/tom-select/tom-select.bootstrap5.min.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('assets/vendor/tom-select/tom-select.complete.min.js') }}"></script>
@endpush
@endonce

@push('scripts')
<script>
(function () {
    const rsId         = {{ $rsId }};
    const uploadUrl    = @json($uploadUrl);
    const deleteUrlTpl = @json($deleteUrlTpl);
    const SAVED        = @json($savedSelections);

    let rowNum      = 0;
    const addedStages = new Set();

    function stageKey(stage) {
        return 'ps_' + stage.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
    }

    const stageOptions = @json($availableStages).map(s => ({ value: s, text: s }));

    const stageDropdown = new TomSelect('#stageAddDropdown-' + rsId, {
        options:     stageOptions,
        valueField:  'value',
        labelField:  'text',
        searchField: ['text'],
        placeholder: 'Search production stages…',
        maxOptions:  null,
        create:      false,
    });

    const tableBody = document.getElementById('stagesTableBody-' + rsId);
    const tableWrap = document.getElementById('stagesTableWrap-'  + rsId);
    const noMsg     = document.getElementById('noStagesMsg-'      + rsId);

    function escHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function thumbHtml(att) {
        const inner = att.isImage
            ? `<a href="${att.url}" target="_blank" rel="noopener noreferrer"><img src="${att.url}" class="rounded border" style="width:36px;height:36px;object-fit:cover" alt=""></a>`
            : `<a href="${att.url}" target="_blank" rel="noopener noreferrer" class="d-flex align-items-center justify-content-center border rounded bg-light text-decoration-none" style="width:36px;height:36px"><i class="feather-file text-muted" style="font-size:12px"></i></a>`;
        return `<div class="att-thumb position-relative d-inline-block" id="att-${att.id}">
            ${inner}
            <button type="button" class="att-delete-btn btn btn-danger btn-sm p-0 position-absolute top-0 end-0 d-flex align-items-center justify-content-center"
                    style="width:14px;height:14px;font-size:8px;border-radius:50%;margin:-3px;z-index:1;"
                    data-delete-url="${deleteUrlTpl.replace('__ATT__', att.id)}"
                    data-thumb-id="att-${att.id}">×</button>
        </div>`;
    }

    function addStageRow(stage, saved) {
        if (addedStages.has(stage)) { stageDropdown.clear(); return; }
        saved = saved || {};
        addedStages.add(stage);
        rowNum++;
        const idx     = rowNum - 1;
        const taskKey = stageKey(stage);
        const previews = (saved.attachments || []).map(thumbHtml).join('');

        const tr = document.createElement('tr');
        tr.dataset.stage = stage;
        tr.innerHTML = `
            <td class="text-muted row-num">${rowNum}</td>
            <td class="fw-semibold fs-13">
                ${escHtml(stage)}
                <input type="hidden" name="sections[${rsId}][data][selections][${idx}][stage]" value="${escHtml(stage)}">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" min="0" max="100"
                           name="sections[${rsId}][data][selections][${idx}][percentage]"
                           class="form-control form-control-sm text-center"
                           value="${escHtml(saved.percentage ?? '')}" placeholder="0">
                    <span class="input-group-text">%</span>
                </div>
            </td>
            <td>
                <input type="text"
                       name="sections[${rsId}][data][selections][${idx}][quantity]"
                       class="form-control form-control-sm"
                       value="${escHtml(saved.quantity ?? '')}" placeholder="e.g. 5000 pcs">
            </td>
            <td>
                <input type="text"
                       name="sections[${rsId}][data][selections][${idx}][comment]"
                       class="form-control form-control-sm"
                       value="${escHtml(saved.comment ?? '')}" placeholder="Status notes…">
            </td>
            <td>
                <div class="attachment-area" data-upload-url="${uploadUrl}" data-task-key="${taskKey}">
                    <div class="att-previews d-flex flex-wrap gap-1 mb-1">${previews}</div>
                    <button type="button" class="add-files-btn btn btn-sm btn-light border" style="font-size:10px">
                        <i class="feather-camera me-1" style="font-size:10px"></i>Add
                    </button>
                    <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf">
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-light-danger remove-stage-btn" title="Remove">
                    <i class="feather-x"></i>
                </button>
            </td>`;

        tableBody.appendChild(tr);
        tableWrap.style.display = '';
        noMsg.style.display     = 'none';
        stageDropdown.clear();

        if (window.initAttachmentArea) {
            tr.querySelectorAll('.attachment-area').forEach(area => window.initAttachmentArea(area));
        }
    }

    function removeStageRow(tr) {
        const stage = tr.dataset.stage;
        addedStages.delete(stage);
        tr.remove();
        renumberRows();
        if (addedStages.size === 0) {
            tableWrap.style.display = 'none';
            noMsg.style.display     = '';
        }
    }

    function renumberRows() {
        let i = 0;
        tableBody.querySelectorAll('tr').forEach(tr => {
            i++;
            const numCell = tr.querySelector('.row-num');
            if (numCell) numCell.textContent = i;
            tr.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(
                    /sections\[(\d+)\]\[data\]\[selections\]\[\d+\]/,
                    `sections[${rsId}][data][selections][${i - 1}]`
                );
            });
        });
        rowNum = i;
    }

    document.getElementById('addStageBtn-' + rsId).addEventListener('click', () => {
        const val = stageDropdown.getValue();
        if (val) addStageRow(val);
    });

    tableBody.addEventListener('click', e => {
        const btn = e.target.closest('.remove-stage-btn');
        if (btn) removeStageRow(btn.closest('tr'));
    });

    SAVED.forEach(sel => addStageRow(sel.stage, sel));
})();
</script>
@endpush
