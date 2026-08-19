{{-- Defects Recording (covers denim_textile_defects + defect_recording slugs) --}}
{{-- Expects: $runSection, $defects, $uploadUrl, $inspection, $run, $sizeOptions --}}
@php
    $rsId       = $runSection->id;
    $attsByTask = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');
    $defectsMap = $defects->mapWithKeys(fn($def) => [
        $def->id => [
            'name'     => $def->defect_name,
            'severity' => $def->severity ?? 'minor',
        ],
    ]);
    $deleteUrlTpl = route('inspections.runs.attachments.delete', [$inspection, $run, '__ATT__']);
    $sizeOptionList = collect($sizeOptions ?? [])->values();

    $savedRows = $runSection->defects->map(fn ($row) => [
        'id'                => $row->id,
        'defect_id'         => $row->defect_id,
        'severity'          => $row->severity,
        'size'              => $row->size,
        'qty'               => $row->qty,
        'carton_no'         => $row->carton_no,
        'status'            => $row->status,
        'disposition_code'  => $row->disposition_code,
        'notes'             => $row->notes,
        'attachments' => $attsByTask->get('defect_row_' . $row->id, collect())->map(fn($a) => [
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
    Search and add each defect found during inspection. A defect can be added more than once
    (e.g. found in different sizes or cartons) — set its size, quantity, carton, and status for each entry.
</p>

<div class="d-flex gap-2 mb-3">
    <div class="flex-grow-1">
        <select id="defectAddDropdown-{{ $rsId }}" placeholder="Search defects…"></select>
    </div>
    <button type="button" id="addDefectBtn-{{ $rsId }}" class="btn btn-light-brand">
        <i class="feather-plus me-1"></i>Add
    </button>
</div>

<div id="defectsTableWrap-{{ $rsId }}" class="border rounded" style="{{ $savedRows->isEmpty() ? 'display:none' : '' }}">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:32px">#</th>
                <th>Defect</th>
                <th style="width:90px">Severity</th>
                <th style="width:100px">Size</th>
                <th style="width:70px">Qty</th>
                <th style="width:100px">Carton #</th>
                <th style="width:110px">Status</th>
                <th style="width:110px">Disposition</th>
                <th style="width:160px">Notes</th>
                <th style="width:160px">Photos</th>
                <th style="width:36px"></th>
            </tr>
        </thead>
        <tbody id="defectsTableBody-{{ $rsId }}"></tbody>
    </table>
</div>
<div id="noDefectsMsg-{{ $rsId }}" class="text-muted fs-12 mt-1" style="{{ $savedRows->isEmpty() ? '' : 'display:none' }}">
    No defects recorded yet.
</div>
<div id="defectHiddenInputs-{{ $rsId }}"></div>

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
    const rsId        = {{ $rsId }};
    const uploadUrl   = @json($uploadUrl);
    const deleteUrlTpl = @json($deleteUrlTpl);
    const DEFECTS_MAP = @json($defectsMap);
    const SAVED_ROWS  = @json($savedRows);
    const SIZE_OPTIONS = @json($sizeOptionList);
    const STATUSES = ['open', 'rectified', 'rejected'];
    const DISPOSITION_CODES = ['MACDF', 'MACSO', 'MACDE'];

    let rowNum = 0;

    const SEVERITY_COLORS = { critical: 'danger', major: 'warning', minor: 'info', functional: 'secondary' };

    const defectOptions = Object.entries(DEFECTS_MAP).map(([id, def]) => ({
        value: String(id),
        text:  def.name + ' [' + (def.severity ?? 'unknown').charAt(0).toUpperCase() + (def.severity ?? 'unknown').slice(1) + ']',
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

    function sizeOptionsHtml(selected) {
        let html = '<option value="">—</option>';
        SIZE_OPTIONS.forEach(sz => {
            html += `<option value="${escHtml(sz)}"${selected === sz ? ' selected' : ''}>${escHtml(sz)}</option>`;
        });
        // Keep a saved value visible even if it's since fallen outside the declared size list.
        if (selected && !SIZE_OPTIONS.includes(selected)) {
            html += `<option value="${escHtml(selected)}" selected>${escHtml(selected)}</option>`;
        }
        return html;
    }

    function statusOptionsHtml(selected) {
        return STATUSES.map(s =>
            `<option value="${s}"${selected === s ? ' selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`
        ).join('');
    }

    function dispositionOptionsHtml(selected) {
        return '<option value="">—</option>' + DISPOSITION_CODES.map(c =>
            `<option value="${c}"${selected === c ? ' selected' : ''}>${c}</option>`
        ).join('');
    }

    function attachmentThumbHtml(att) {
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

    function toggleDispositionVisibility(tr) {
        const statusSel = tr.querySelector('.defect-status-select');
        const dispoTd = tr.querySelector('.defect-disposition-cell');
        if (!statusSel || !dispoTd) return;
        dispoTd.style.display = statusSel.value === 'rejected' ? '' : 'none';
    }

    function addDefectRow(defectId, saved) {
        defectId = String(defectId);
        const def = DEFECTS_MAP[defectId];
        if (!def) return;

        saved = saved || {};
        rowNum++;
        const idx      = rowNum - 1;
        // Photos attach to a specific saved row (task_key = defect_row_{id}).
        // A row that hasn't been saved yet has no id to key photos against,
        // so photo upload is only offered once this row exists in the DB —
        // save the run, then attach photos to it on the next edit.
        const taskKey  = saved.id ? ('defect_row_' + saved.id) : null;
        const previews = (saved.attachments || []).map(attachmentThumbHtml).join('');
        const status   = saved.status || 'open';

        const sev      = saved.severity || def.severity || 'minor';
        const sevColor = SEVERITY_COLORS[sev] ?? 'secondary';
        const sevLabel = sev.charAt(0).toUpperCase() + sev.slice(1);

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-muted row-num">${rowNum}</td>
            <td class="fw-semibold fs-13">
                ${escHtml(def.name)}
                <input type="hidden" name="sections[${rsId}][defects][${idx}][id]" value="${saved.id || ''}">
                <input type="hidden" name="sections[${rsId}][defects][${idx}][defect_id]" value="${defectId}">
                <input type="hidden" name="sections[${rsId}][defects][${idx}][severity]" value="${sev}">
            </td>
            <td><span class="badge bg-soft-${sevColor} text-${sevColor}">${sevLabel}</span></td>
            <td>
                <select name="sections[${rsId}][defects][${idx}][size]" class="form-select form-select-sm">
                    ${sizeOptionsHtml(saved.size || '')}
                </select>
            </td>
            <td>
                <input type="number" name="sections[${rsId}][defects][${idx}][qty]"
                       class="form-control form-control-sm text-center"
                       value="${saved.qty || 1}" min="1" placeholder="1">
            </td>
            <td>
                <input type="text" name="sections[${rsId}][defects][${idx}][carton_no]"
                       class="form-control form-control-sm" value="${escHtml(saved.carton_no || '')}" placeholder="e.g. 12">
            </td>
            <td>
                <select name="sections[${rsId}][defects][${idx}][status]" class="form-select form-select-sm defect-status-select">
                    ${statusOptionsHtml(status)}
                </select>
            </td>
            <td class="defect-disposition-cell" style="${status === 'rejected' ? '' : 'display:none'}">
                <select name="sections[${rsId}][defects][${idx}][disposition_code]" class="form-select form-select-sm">
                    ${dispositionOptionsHtml(saved.disposition_code || '')}
                </select>
            </td>
            <td>
                <input type="text" name="sections[${rsId}][defects][${idx}][notes]"
                       class="form-control form-control-sm" value="${escHtml(saved.notes || '')}" placeholder="Observation…">
            </td>
            <td>
                ${taskKey ? `
                <div class="attachment-area" data-upload-url="${uploadUrl}" data-task-key="${taskKey}">
                    <div class="att-previews d-flex flex-wrap gap-1 mb-1">${previews}</div>
                    <button type="button" class="add-files-btn btn btn-sm btn-light border" style="font-size:10px">
                        <i class="feather-camera me-1" style="font-size:10px"></i>Add
                    </button>
                    <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf">
                </div>
                ` : `<small class="text-muted fs-11">Save the run to attach photos</small>`}
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-light-danger remove-defect-btn" title="Remove">
                    <i class="feather-x"></i>
                </button>
            </td>`;
        tableBody.appendChild(tr);

        tr.querySelector('.defect-status-select')?.addEventListener('change', () => toggleDispositionVisibility(tr));

        tableWrap.style.display = '';
        noMsg.style.display     = 'none';
        defectDropdown.clear();

        if (window.initAttachmentArea) {
            tr.querySelectorAll('.attachment-area').forEach(area => window.initAttachmentArea(area));
        }
    }

    function removeDefectRow(tr) {
        tr.remove();
        renumberRows();
        if (tableBody.children.length === 0) {
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
                    /sections\[(\d+)\]\[defects\]\[\d+\]/,
                    `sections[${rsId}][defects][${i - 1}]`
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
        if (btn) removeDefectRow(btn.closest('tr'));
    });

    SAVED_ROWS.forEach(row => addDefectRow(row.defect_id, row));
    hiddenWrap.remove();
})();
</script>
@endpush
@endif
