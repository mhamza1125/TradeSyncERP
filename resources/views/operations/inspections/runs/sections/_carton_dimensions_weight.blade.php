{{-- Carton Dimensions & Weight — multi-row table (one row per carton type / variant) --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $data        = $runSection->data ?? $runSection->section->default_data ?? [];
    $rsId        = $runSection->id;
    $dimUnit     = $data['dim_unit']    ?? 'cm';
    $weightUnit  = $data['weight_unit'] ?? 'kg';
    $cartons     = $data['cartons']     ?? [['carton_type' => '', 'length' => '', 'width' => '', 'height' => '', 'gross_weight' => '', 'net_weight' => '', 'remarks' => '']];
    $attsByTask  = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');
    $deleteUrlTpl = route('inspections.runs.attachments.delete', [$inspection, $run, '__ATT__']);
@endphp

{{-- Global unit selectors --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <label class="form-label fw-semibold fs-12">Dimension Unit</label>
        <select name="sections[{{ $rsId }}][data][dim_unit]" class="form-select form-select-sm">
            @foreach(['cm', 'mm', 'inch', 'ft', 'm'] as $u)
                <option value="{{ $u }}" @selected($dimUnit === $u)>{{ $u }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 col-sm-6">
        <label class="form-label fw-semibold fs-12">Weight Unit</label>
        <select name="sections[{{ $rsId }}][data][weight_unit]" class="form-select form-select-sm">
            @foreach(['kg', 'g', 'lb', 'oz'] as $u)
                <option value="{{ $u }}" @selected($weightUnit === $u)>{{ $u }}</option>
            @endforeach
        </select>
    </div>
</div>

<p class="text-muted fs-13 mb-2">Add a row for each carton type or variant. Attach measurement proof photos per row.</p>

<div class="table-responsive mb-3">
    <table class="table table-sm table-bordered align-middle" style="min-width:920px">
        <thead class="table-light">
            <tr>
                <th style="width:36px" class="text-center">#</th>
                <th style="min-width:145px">Carton Type / Variant</th>
                <th style="width:82px">Length</th>
                <th style="width:82px">Width</th>
                <th style="width:82px">Height</th>
                <th style="width:95px">Gross Wt</th>
                <th style="width:95px">Net Wt</th>
                <th style="min-width:120px">Remarks</th>
                <th style="min-width:170px">Photos</th>
                <th style="width:42px"></th>
            </tr>
        </thead>
        <tbody id="carton-dim-body-{{ $rsId }}">
            @foreach($cartons as $ci => $carton)
            @php $taskKey = 'carton_dim_' . $ci; $cartonAtts = $attsByTask->get($taskKey, collect()); @endphp
            <tr data-idx="{{ $ci }}">
                <td class="text-muted text-center row-num">{{ $ci + 1 }}</td>
                <td>
                    <input type="text"
                           name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][carton_type]"
                           class="form-control form-control-sm"
                           value="{{ $carton['carton_type'] ?? '' }}"
                           placeholder="e.g. Master, Inner…">
                </td>
                <td>
                    <input type="number" step="0.01" min="0"
                           name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][length]"
                           class="form-control form-control-sm"
                           value="{{ $carton['length'] ?? '' }}" placeholder="0.00">
                </td>
                <td>
                    <input type="number" step="0.01" min="0"
                           name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][width]"
                           class="form-control form-control-sm"
                           value="{{ $carton['width'] ?? '' }}" placeholder="0.00">
                </td>
                <td>
                    <input type="number" step="0.01" min="0"
                           name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][height]"
                           class="form-control form-control-sm"
                           value="{{ $carton['height'] ?? '' }}" placeholder="0.00">
                </td>
                <td>
                    <input type="number" step="0.001" min="0"
                           name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][gross_weight]"
                           class="form-control form-control-sm"
                           value="{{ $carton['gross_weight'] ?? '' }}" placeholder="0.000">
                </td>
                <td>
                    <input type="number" step="0.001" min="0"
                           name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][net_weight]"
                           class="form-control form-control-sm"
                           value="{{ $carton['net_weight'] ?? '' }}" placeholder="0.000">
                </td>
                <td>
                    <input type="text"
                           name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][remarks]"
                           class="form-control form-control-sm"
                           value="{{ $carton['remarks'] ?? '' }}" placeholder="Optional…">
                </td>
                <td>
                    <div class="attachment-area" data-upload-url="{{ $uploadUrl }}" data-task-key="{{ $taskKey }}">
                        <div class="att-previews d-flex flex-wrap gap-1 mb-1">
                            @foreach($cartonAtts as $att)
                            <div class="att-thumb position-relative d-inline-block" id="att-{{ $att->id }}">
                                @if($att->isImage())
                                    <a href="{{ $att->url }}" target="_blank" rel="noopener noreferrer">
                                        <img src="{{ $att->url }}" class="rounded border"
                                             style="width:36px;height:36px;object-fit:cover" alt="">
                                    </a>
                                @else
                                    <a href="{{ $att->url }}" target="_blank" rel="noopener noreferrer"
                                       class="d-flex align-items-center justify-content-center border rounded bg-light text-decoration-none"
                                       style="width:36px;height:36px">
                                        <i class="feather-file text-muted" style="font-size:12px"></i>
                                    </a>
                                @endif
                                <button type="button"
                                        class="att-delete-btn btn btn-danger btn-sm p-0 position-absolute top-0 end-0 d-flex align-items-center justify-content-center"
                                        style="width:14px;height:14px;font-size:8px;border-radius:50%;margin:-3px;z-index:1;"
                                        data-delete-url="{{ route('inspections.runs.attachments.delete', [$inspection, $run, $att]) }}"
                                        data-thumb-id="att-{{ $att->id }}">×</button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="add-files-btn btn btn-sm btn-light border" style="font-size:10px">
                            <i class="feather-camera me-1" style="font-size:10px"></i>Add
                        </button>
                        <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-light-danger remove-carton-btn p-1" title="Remove row">
                        <i class="feather-x" style="font-size:13px"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-carton-btn-{{ $rsId }}">
        <i class="feather-plus me-1"></i>Add Carton
    </button>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="clone-carton-btn-{{ $rsId }}">
        <i class="feather-copy me-1"></i>Clone Last
    </button>
</div>

@push('scripts')
<script>
(function () {
    const rsId        = {{ $rsId }};
    const uploadUrl   = @json($uploadUrl);
    const deleteUrlTpl = @json($deleteUrlTpl);
    const tbody       = document.getElementById('carton-dim-body-' + rsId);

    function getCount() {
        return tbody.querySelectorAll('tr').length;
    }

    function reindex() {
        tbody.querySelectorAll('tr').forEach((tr, i) => {
            tr.dataset.idx = i;
            tr.querySelector('.row-num').textContent = i + 1;
            tr.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(
                    /sections\[(\d+)\]\[data\]\[cartons\]\[\d+\]/,
                    `sections[${rsId}][data][cartons][${i}]`
                );
            });
            const attArea = tr.querySelector('.attachment-area');
            if (attArea) attArea.dataset.taskKey = 'carton_dim_' + i;
        });
    }

    function buildRow(d) {
        const idx     = getCount();
        const taskKey = 'carton_dim_' + idx;
        const tr      = document.createElement('tr');
        tr.dataset.idx = idx;
        tr.innerHTML = `
            <td class="text-muted text-center row-num">${idx + 1}</td>
            <td><input type="text" name="sections[${rsId}][data][cartons][${idx}][carton_type]" class="form-control form-control-sm" value="${d.carton_type ?? ''}" placeholder="e.g. Master, Inner…"></td>
            <td><input type="number" step="0.01" min="0" name="sections[${rsId}][data][cartons][${idx}][length]" class="form-control form-control-sm" value="${d.length ?? ''}" placeholder="0.00"></td>
            <td><input type="number" step="0.01" min="0" name="sections[${rsId}][data][cartons][${idx}][width]" class="form-control form-control-sm" value="${d.width ?? ''}" placeholder="0.00"></td>
            <td><input type="number" step="0.01" min="0" name="sections[${rsId}][data][cartons][${idx}][height]" class="form-control form-control-sm" value="${d.height ?? ''}" placeholder="0.00"></td>
            <td><input type="number" step="0.001" min="0" name="sections[${rsId}][data][cartons][${idx}][gross_weight]" class="form-control form-control-sm" value="${d.gross_weight ?? ''}" placeholder="0.000"></td>
            <td><input type="number" step="0.001" min="0" name="sections[${rsId}][data][cartons][${idx}][net_weight]" class="form-control form-control-sm" value="${d.net_weight ?? ''}" placeholder="0.000"></td>
            <td><input type="text" name="sections[${rsId}][data][cartons][${idx}][remarks]" class="form-control form-control-sm" value="${d.remarks ?? ''}" placeholder="Optional…"></td>
            <td>
                <div class="attachment-area" data-upload-url="${uploadUrl}" data-task-key="${taskKey}">
                    <div class="att-previews d-flex flex-wrap gap-1 mb-1"></div>
                    <button type="button" class="add-files-btn btn btn-sm btn-light border" style="font-size:10px">
                        <i class="feather-camera me-1" style="font-size:10px"></i>Add
                    </button>
                    <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf">
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-light-danger remove-carton-btn p-1" title="Remove row">
                    <i class="feather-x" style="font-size:13px"></i>
                </button>
            </td>`;
        if (window.initAttachmentArea) {
            tr.querySelectorAll('.attachment-area').forEach(area => window.initAttachmentArea(area));
        }
        return tr;
    }

    tbody.addEventListener('click', e => {
        const btn = e.target.closest('.remove-carton-btn');
        if (!btn) return;
        if (getCount() <= 1) { alert('At least one carton row is required.'); return; }
        btn.closest('tr').remove();
        reindex();
    });

    document.getElementById('add-carton-btn-' + rsId)?.addEventListener('click', () => {
        tbody.appendChild(buildRow({}));
    });

    document.getElementById('clone-carton-btn-' + rsId)?.addEventListener('click', () => {
        const rows = tbody.querySelectorAll('tr');
        if (!rows.length) { tbody.appendChild(buildRow({})); return; }
        const last = rows[rows.length - 1];
        const get  = key => last.querySelector(`[name*="[${key}]"]`)?.value ?? '';
        tbody.appendChild(buildRow({
            carton_type:  get('carton_type'),
            length:       get('length'),
            width:        get('width'),
            height:       get('height'),
            gross_weight: get('gross_weight'),
            net_weight:   get('net_weight'),
        }));
    });

    tbody.querySelectorAll('.attachment-area').forEach(area => {
        if (window.initAttachmentArea) window.initAttachmentArea(area);
    });
})();
</script>
@endpush
