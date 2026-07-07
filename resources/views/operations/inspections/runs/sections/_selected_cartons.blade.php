{{-- Selected Cartons SI --}}
{{-- Expects: $runSection; $colors, $sizes are shared from the parent edit view --}}
@php
    $cartons = $runSection->data['cartons'] ?? [['box_number'=>'','size'=>'','color'=>'','qty_inspected'=>'']];
    $rsId    = $runSection->id;
    $colorOptions = ($colors ?? collect())->pluck('name');
    $sizeOptions  = ($sizes  ?? collect())->pluck('name');
@endphp

<div class="mb-2">
    <div class="row g-2 mb-1 d-none d-md-flex">
        <div class="col-md-3"><small class="text-muted fw-semibold">Box #</small></div>
        <div class="col-md-3"><small class="text-muted fw-semibold">Size</small></div>
        <div class="col-md-3"><small class="text-muted fw-semibold">Color</small></div>
        <div class="col-md-2"><small class="text-muted fw-semibold">Qty Inspected</small></div>
        <div class="col-md-1"></div>
    </div>

    <div id="cartons-container-{{ $rsId }}">
        @foreach($cartons as $ci => $carton)
        <div class="carton-row mb-2" data-idx="{{ $ci }}">
            <div class="row g-2 align-items-center">
                <div class="col-6 col-md-3">
                    <input type="text"
                           name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][box_number]"
                           class="form-control form-control-sm"
                           value="{{ $carton['box_number'] ?? '' }}"
                           placeholder="Box #">
                </div>
                <div class="col-6 col-md-3">
                    <select name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][size]" class="form-select form-select-sm">
                        <option value="">— Size —</option>
                        @foreach($sizeOptions as $sz)
                        <option value="{{ $sz }}" @selected(($carton['size'] ?? '') === $sz)>{{ $sz }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][color]" class="form-select form-select-sm">
                        <option value="">— Color —</option>
                        @foreach($colorOptions as $cl)
                        <option value="{{ $cl }}" @selected(($carton['color'] ?? '') === $cl)>{{ $cl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-5 col-md-2">
                    <input type="number"
                           name="sections[{{ $rsId }}][data][cartons][{{ $ci }}][qty_inspected]"
                           class="form-control form-control-sm"
                           value="{{ $carton['qty_inspected'] ?? '' }}"
                           placeholder="Qty"
                           min="0">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-carton-btn p-1" title="Remove row">
                        <i class="feather-trash-2" style="font-size:13px"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-carton-{{ $rsId }}">
            <i class="feather-plus me-1"></i>Add Carton
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="clone-carton-{{ $rsId }}">
            <i class="feather-copy me-1"></i>Clone Last Row
        </button>
    </div>

    <div class="mt-3 text-muted fs-12" id="carton-count-{{ $rsId }}">
        {{ count($cartons) }} carton(s) logged
    </div>
</div>

@push('scripts')
<script>
(function () {
    const rsId      = {{ $rsId }};
    const container = document.getElementById('cartons-container-' + rsId);
    const countEl   = document.getElementById('carton-count-' + rsId);
    if (!container) return;

    const CARTON_COLORS = @json($colorOptions->values());
    const CARTON_SIZES  = @json($sizeOptions->values());

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function buildSizeOptions(selected) {
        return '<option value="">— Size —</option>' +
            CARTON_SIZES.map(s => `<option value="${escHtml(s)}"${selected === s ? ' selected' : ''}>${escHtml(s)}</option>`).join('');
    }

    function buildColorOptions(selected) {
        return '<option value="">— Color —</option>' +
            CARTON_COLORS.map(c => `<option value="${escHtml(c)}"${selected === c ? ' selected' : ''}>${escHtml(c)}</option>`).join('');
    }

    function getCount() {
        return container.querySelectorAll('.carton-row').length;
    }

    function updateCount() {
        if (countEl) countEl.textContent = getCount() + ' carton(s) logged';
        // Completion status for this section is owned solely by the "Mark as Complete"
        // button (see applyStatusEverywhere() in edit.blade.php). This row-count display
        // must not touch hidden-status-*/status-badge-* — doing so previously forced the
        // section back to "pending" on every page load, even after it had been saved as
        // "complete".
    }

    function reindex() {
        container.querySelectorAll('.carton-row').forEach((row, i) => {
            row.dataset.idx = i;
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(
                    /sections\[(\d+)\]\[data\]\[cartons\]\[\d+\]/,
                    `sections[${rsId}][data][cartons][${i}]`
                );
            });
        });
        updateCount();
    }

    function buildRow(data) {
        const idx = getCount();
        const row = document.createElement('div');
        row.className = 'carton-row mb-2';
        row.dataset.idx = idx;
        row.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-6 col-md-3">
                    <input type="text" name="sections[${rsId}][data][cartons][${idx}][box_number]"
                           class="form-control form-control-sm" value="${data.box_number ?? ''}" placeholder="Box #">
                </div>
                <div class="col-6 col-md-3">
                    <select name="sections[${rsId}][data][cartons][${idx}][size]" class="form-select form-select-sm">
                        ${buildSizeOptions(data.size ?? '')}
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="sections[${rsId}][data][cartons][${idx}][color]" class="form-select form-select-sm">
                        ${buildColorOptions(data.color ?? '')}
                    </select>
                </div>
                <div class="col-5 col-md-2">
                    <input type="number" name="sections[${rsId}][data][cartons][${idx}][qty_inspected]"
                           class="form-control form-control-sm" value="${data.qty_inspected ?? ''}" placeholder="Qty" min="0">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-carton-btn p-1" title="Remove">
                        <i class="feather-trash-2" style="font-size:13px"></i>
                    </button>
                </div>
            </div>`;
        bindRemove(row);
        return row;
    }

    function bindRemove(row) {
        row.querySelector('.remove-carton-btn')?.addEventListener('click', () => {
            if (getCount() <= 1) { alert('At least one carton row is required.'); return; }
            row.remove();
            reindex();
        });
    }

    // Bind existing remove buttons
    container.querySelectorAll('.remove-carton-btn').forEach(btn => {
        bindRemove(btn.closest('.carton-row'));
    });

    document.getElementById('add-carton-' + rsId)?.addEventListener('click', () => {
        container.appendChild(buildRow({}));
        updateCount();
    });

    document.getElementById('clone-carton-' + rsId)?.addEventListener('click', () => {
        const rows = container.querySelectorAll('.carton-row');
        if (!rows.length) { container.appendChild(buildRow({})); updateCount(); return; }
        const last = rows[rows.length - 1];
        const get  = sel => last.querySelector(sel)?.value ?? '';
        container.appendChild(buildRow({
            box_number:    get('[name*="box_number"]'),
            size:          get('[name*="[size]"]'),
            color:         get('[name*="[color]"]'),
            qty_inspected: '',
        }));
        updateCount();
    });

    updateCount();
})();
</script>
@endpush
