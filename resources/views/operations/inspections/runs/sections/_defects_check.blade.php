{{-- Defects Recording (covers denim_textile_defects + defect_recording slugs) --}}
{{-- Expects: $runSection, $defects, $uploadUrl, $inspection, $run --}}
@php
    $d          = $runSection->data ?? [];
    $selections = collect($d['selections'] ?? [])->filter(fn($s) => !empty($s['selected']) && !empty($s['defect_id']))->values();
    $rsId       = $runSection->id;
    $attsByTask = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');
    $defectsMap = $defects->mapWithKeys(fn($def) => [
        $def->id => [
            'name'     => $def->defect_name,
            'category' => $def->category?->name ?? 'General',
        ],
    ]);
    $deleteUrlTpl = route('inspections.runs.attachments.delete', [$inspection, $run, '__ATT__']);
    $savedSelections = $selections->map(fn($s) => [
        'defect_id' => (int) $s['defect_id'],
        'severity'  => $s['severity'] ?? '',
        'comment'   => $s['comment'] ?? '',
        'attachments' => $attsByTask->get('defect_' . $s['defect_id'], collect())->map(fn($a) => [
            'id'      => $a->id,
            'url'     => $a->url,
            'isImage' => $a->isImage(),
            'name'    => $a->file_name,
        ])->values(),
    ])->values();
@endphp

@if($defects->isEmpty())
<div class="text-center py-5 text-muted">
    <i class="feather-alert-triangle fs-2 d-block mb-2 opacity-30"></i>
    <p class="mb-0">No defects configured.</p>
    <small>Add defects in Masters → Defects before using this section.</small>
</div>
@else
<p class="text-muted fs-13 mb-3">
    Search and add each defect found during inspection. Set its severity, add a comment,
    and attach photos as evidence.
</p>

<div class="d-flex gap-2 mb-3">
    <div class="flex-grow-1">
        <select id="defectAddDropdown-{{ $rsId }}" placeholder="Search defects…"></select>
    </div>
    <button type="button" id="addDefectBtn-{{ $rsId }}" class="btn btn-light-brand">
        <i class="feather-plus me-1"></i>Add
    </button>
</div>

<div id="defectsTableWrap-{{ $rsId }}" class="border rounded" style="{{ $selections->isEmpty() ? 'display:none' : '' }}">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:36px">#</th>
                <th>Defect</th>
                <th style="width:130px">Category</th>
                <th style="width:130px">Severity</th>
                <th style="width:220px">Comment</th>
                <th style="width:220px">Photos</th>
                <th style="width:40px"></th>
            </tr>
        </thead>
        <tbody id="defectsTableBody-{{ $rsId }}"></tbody>
    </table>
</div>
<div id="noDefectsMsg-{{ $rsId }}" class="text-muted fs-12 mt-1" style="{{ $selections->isEmpty() ? '' : 'display:none' }}">
    No defects recorded yet.
</div>
<div id="defectHiddenInputs-{{ $rsId }}"></div>

@once
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
@endpush
@endonce

@push('scripts')
<script>
(function () {
    const rsId        = {{ $rsId }};
    const uploadUrl   = @json($uploadUrl);
    const deleteUrlTpl = @json($deleteUrlTpl);
    const DEFECTS_MAP = @json($defectsMap);
    const SAVED_SELECTIONS = @json($savedSelections);

    let addedDefects = new Set();
    let rowNum = 0;

    const defectOptions = Object.entries(DEFECTS_MAP).map(([id, def]) => ({
        value: String(id),
        text:  def.name + ' (' + def.category + ')',
    }));

    const defectDropdown = new TomSelect('#defectAddDropdown-' + rsId, {
        options:     defectOptions,
        valueField:  'value',
        labelField:  'text',
        searchField: ['text'],
        placeholder: 'Search defects…',
        maxOptions:  null,
        create:      false,
    });

    const tableBody   = document.getElementById('defectsTableBody-' + rsId);
    const tableWrap   = document.getElementById('defectsTableWrap-' + rsId);
    const noMsg       = document.getElementById('noDefectsMsg-' + rsId);
    const hiddenWrap  = document.getElementById('defectHiddenInputs-' + rsId);

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function attachmentThumbHtml(att) {
        const inner = att.isImage
            ? `<img src="${att.url}" class="rounded border" style="width:40px;height:40px;object-fit:cover" alt="">`
            : `<div class="d-flex align-items-center justify-content-center border rounded bg-light" style="width:40px;height:40px"><i class="feather-file text-muted" style="font-size:13px"></i></div>`;
        return `<div class="att-thumb position-relative d-inline-block" id="att-${att.id}">
            ${inner}
            <button type="button" class="att-delete-btn btn btn-danger btn-sm p-0 position-absolute top-0 end-0 d-flex align-items-center justify-content-center"
                    style="width:14px;height:14px;font-size:8px;border-radius:50%;margin:-3px;z-index:1;"
                    data-delete-url="${deleteUrlTpl.replace('__ATT__', att.id)}"
                    data-thumb-id="att-${att.id}">×</button>
        </div>`;
    }

    function addDefectRow(id, saved) {
        id = String(id);
        if (addedDefects.has(id)) { defectDropdown.clear(); return; }
        const def = DEFECTS_MAP[id];
        if (!def) return;

        saved = saved || {};
        addedDefects.add(id);
        rowNum++;
        const idx      = rowNum - 1;
        const taskKey  = 'defect_' + id;
        const previews = (saved.attachments || []).map(attachmentThumbHtml).join('');

        const tr = document.createElement('tr');
        tr.dataset.defectId = id;
        tr.innerHTML = `
            <td class="text-muted row-num">${rowNum}</td>
            <td class="fw-semibold fs-13">
                ${escHtml(def.name)}
                <input type="hidden" name="sections[${rsId}][data][selections][${idx}][defect_id]" value="${id}">
                <input type="hidden" name="sections[${rsId}][data][selections][${idx}][selected]" value="1">
            </td>
            <td class="text-muted fs-12">${escHtml(def.category)}</td>
            <td>
                <select name="sections[${rsId}][data][selections][${idx}][severity]" class="form-select form-select-sm">
                    <option value="">— Severity —</option>
                    <option value="Critical" ${saved.severity === 'Critical' ? 'selected' : ''}>Critical</option>
                    <option value="Major" ${saved.severity === 'Major' ? 'selected' : ''}>Major</option>
                    <option value="Minor" ${saved.severity === 'Minor' ? 'selected' : ''}>Minor</option>
                </select>
            </td>
            <td>
                <input type="text" name="sections[${rsId}][data][selections][${idx}][comment]"
                       class="form-control form-control-sm" value="${escHtml(saved.comment || '')}" placeholder="Observation…">
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
                <button type="button" class="btn btn-sm btn-light-danger remove-defect-btn" data-id="${id}" title="Remove">
                    <i class="feather-x"></i>
                </button>
            </td>`;
        tableBody.appendChild(tr);

        tableWrap.style.display = '';
        noMsg.style.display     = 'none';
        defectDropdown.clear();

        if (window.initAttachmentArea) {
            tr.querySelectorAll('.attachment-area').forEach(area => window.initAttachmentArea(area));
        }
    }

    function removeDefectRow(id) {
        id = String(id);
        addedDefects.delete(id);
        tableBody.querySelectorAll(`[data-defect-id="${id}"]`).forEach(r => r.remove());
        renumberRows();
        if (addedDefects.size === 0) {
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

    document.getElementById('addDefectBtn-' + rsId).addEventListener('click', () => {
        const val = defectDropdown.getValue();
        if (val) addDefectRow(val);
    });
    tableBody.addEventListener('click', e => {
        const btn = e.target.closest('.remove-defect-btn');
        if (btn) removeDefectRow(btn.dataset.id);
    });

    SAVED_SELECTIONS.forEach(sel => addDefectRow(sel.defect_id, sel));
    hiddenWrap.remove();
})();
</script>
@endpush
@endif
