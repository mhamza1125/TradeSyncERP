{{-- Article Inspection Results Table --}}
@php
    $data     = $runSection->data ?? $runSection->section->default_data ?? [];
    $articles = $data['articles'] ?? [['article_no'=>'','color'=>'','size'=>'','qty_ordered'=>'','qty_inspected'=>'','result'=>null,'remarks'=>'']];
    $rsId     = $runSection->id;
@endphp

<div class="mb-2">
    <div class="row g-2 mb-1 d-none d-md-flex">
        <div class="col-md-2"><small class="text-muted fw-semibold">Article / Style No.</small></div>
        <div class="col-md-2"><small class="text-muted fw-semibold">Color</small></div>
        <div class="col-md-1"><small class="text-muted fw-semibold">Size</small></div>
        <div class="col-md-2"><small class="text-muted fw-semibold">Qty Ordered</small></div>
        <div class="col-md-2"><small class="text-muted fw-semibold">Qty Inspected</small></div>
        <div class="col-md-2"><small class="text-muted fw-semibold">Result</small></div>
        <div class="col-md-1"></div>
    </div>

    <div id="articles-container-{{ $rsId }}">
        @foreach($articles as $ai => $article)
        @php $result = $article['result'] ?? null; @endphp
        @php
            $rowClass = match($result) {
                'Pass' => 'table-success',
                'Fail' => 'table-danger',
                'N/A'  => 'table-light text-muted',
                default => '',
            };
        @endphp
        <div class="article-row mb-2 rounded {{ $rowClass }}" data-idx="{{ $ai }}" data-result-row>
            <div class="row g-2 align-items-center">
                <div class="col-6 col-md-2">
                    <input type="text" name="sections[{{ $rsId }}][data][articles][{{ $ai }}][article_no]"
                           class="form-control form-control-sm"
                           value="{{ $article['article_no'] ?? '' }}" placeholder="Article / Style #">
                </div>
                <div class="col-6 col-md-2">
                    <input type="text" name="sections[{{ $rsId }}][data][articles][{{ $ai }}][color]"
                           class="form-control form-control-sm"
                           value="{{ $article['color'] ?? '' }}" placeholder="Color">
                </div>
                <div class="col-4 col-md-1">
                    <input type="text" name="sections[{{ $rsId }}][data][articles][{{ $ai }}][size]"
                           class="form-control form-control-sm"
                           value="{{ $article['size'] ?? '' }}" placeholder="Size">
                </div>
                <div class="col-4 col-md-2">
                    <input type="number" name="sections[{{ $rsId }}][data][articles][{{ $ai }}][qty_ordered]"
                           class="form-control form-control-sm"
                           value="{{ $article['qty_ordered'] ?? '' }}" placeholder="Qty" min="0">
                </div>
                <div class="col-4 col-md-2">
                    <input type="number" name="sections[{{ $rsId }}][data][articles][{{ $ai }}][qty_inspected]"
                           class="form-control form-control-sm"
                           value="{{ $article['qty_inspected'] ?? '' }}" placeholder="Qty" min="0">
                </div>
                <div class="col-6 col-md-2">
                    @include('operations.inspections.runs.sections._result_toggle', [
                        'name'  => "sections[{$rsId}][data][articles][{$ai}][result]",
                        'value' => $result,
                    ])
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-article-btn p-1" title="Remove row">
                        <i class="feather-trash-2" style="font-size:13px"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="button" class="btn btn-sm btn-outline-primary" id="add-article-{{ $rsId }}">
            <i class="feather-plus me-1"></i>Add Article
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="clone-article-{{ $rsId }}">
            <i class="feather-copy me-1"></i>Clone Last Row
        </button>
    </div>

    <div class="mt-3 text-muted fs-12" id="article-count-{{ $rsId }}">
        {{ count($articles) }} article(s) logged
    </div>
</div>

@push('scripts')
<script>
(function () {
    const rsId      = {{ $rsId }};
    const container = document.getElementById('articles-container-' + rsId);
    const countEl   = document.getElementById('article-count-' + rsId);
    if (!container) return;

    function getCount() {
        return container.querySelectorAll('.article-row').length;
    }

    function updateCount() {
        if (countEl) countEl.textContent = getCount() + ' article(s) logged';
    }

    function reindex() {
        container.querySelectorAll('.article-row').forEach((row, i) => {
            row.dataset.idx = i;
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(
                    /sections\[(\d+)\]\[data\]\[articles\]\[\d+\]/,
                    `sections[${rsId}][data][articles][${i}]`
                );
            });
        });
        updateCount();
    }

    function buildRow(d) {
        const idx = getCount();
        const row = document.createElement('div');
        row.className = 'article-row mb-2 rounded';
        row.dataset.idx = idx;
        row.setAttribute('data-result-row', '');
        row.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-6 col-md-2">
                    <input type="text" name="sections[${rsId}][data][articles][${idx}][article_no]"
                           class="form-control form-control-sm" value="${d.article_no ?? ''}" placeholder="Article / Style #">
                </div>
                <div class="col-6 col-md-2">
                    <input type="text" name="sections[${rsId}][data][articles][${idx}][color]"
                           class="form-control form-control-sm" value="${d.color ?? ''}" placeholder="Color">
                </div>
                <div class="col-4 col-md-1">
                    <input type="text" name="sections[${rsId}][data][articles][${idx}][size]"
                           class="form-control form-control-sm" value="${d.size ?? ''}" placeholder="Size">
                </div>
                <div class="col-4 col-md-2">
                    <input type="number" name="sections[${rsId}][data][articles][${idx}][qty_ordered]"
                           class="form-control form-control-sm" value="${d.qty_ordered ?? ''}" placeholder="Qty" min="0">
                </div>
                <div class="col-4 col-md-2">
                    <input type="number" name="sections[${rsId}][data][articles][${idx}][qty_inspected]"
                           class="form-control form-control-sm" value="${d.qty_inspected ?? ''}" placeholder="Qty" min="0">
                </div>
                <div class="col-6 col-md-2">
                    <div class="d-flex flex-wrap gap-1 result-toggle-group" data-row-class-map='{"Pass":"table-success","Fail":"table-danger","N/A":"table-light text-muted"}'>
                        ${['Pass','Fail','N/A'].map(opt => {
                            const color = opt === 'Pass' ? 'success' : (opt === 'Fail' ? 'danger' : 'secondary');
                            return `<label class="result-toggle-label btn btn-sm btn-outline-secondary" style="font-size:12px;min-width:56px;cursor:pointer" data-color="${color}">
                                <input type="radio" name="sections[${rsId}][data][articles][${idx}][result]" value="${opt}" class="result-toggle-radio d-none">
                                ${opt}
                            </label>`;
                        }).join('')}
                    </div>
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-article-btn p-1" title="Remove">
                        <i class="feather-trash-2" style="font-size:13px"></i>
                    </button>
                </div>
            </div>`;
        bindRemove(row);
        return row;
    }

    function bindRemove(row) {
        row.querySelector('.remove-article-btn')?.addEventListener('click', () => {
            if (getCount() <= 1) { alert('At least one article row is required.'); return; }
            row.remove();
            reindex();
        });
    }

    container.querySelectorAll('.remove-article-btn').forEach(btn => bindRemove(btn.closest('.article-row')));

    document.getElementById('add-article-' + rsId)?.addEventListener('click', () => {
        container.appendChild(buildRow({}));
        updateCount();
    });

    document.getElementById('clone-article-' + rsId)?.addEventListener('click', () => {
        const rows = container.querySelectorAll('.article-row');
        if (!rows.length) { container.appendChild(buildRow({})); updateCount(); return; }
        const last = rows[rows.length - 1];
        const get  = sel => last.querySelector(sel)?.value ?? '';
        container.appendChild(buildRow({
            article_no:  get('[name*="article_no"]'),
            color:       get('[name*="[color]"]'),
            size:        get('[name*="[size]"]'),
            qty_ordered: get('[name*="qty_ordered"]'),
        }));
        updateCount();
    });

    updateCount();
})();
</script>
@endpush
