@extends('index')

@section('title', 'Run ' . ($loop->index ?? '') . ' — ' . $inspection->report_number . ' — TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Inspection Run</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.edit', $inspection) }}">{{ $inspection->report_number }}</a></li>
                <li class="breadcrumb-item">
                    {{ $run->inspectionType?->name ?? 'Run' }}
                </li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back to Inspection
                </a>
                <button type="submit" form="runForm" class="btn btn-primary">
                    <i class="feather-save me-2"></i>Save Results
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="runForm"
              action="{{ route('inspections.runs.update', [$inspection, $run]) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- ── Run Header ─────────────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Run Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label">Inspection Type</label>
                            <select name="inspection_type_id" class="form-select @error('inspection_type_id') is-invalid @enderror">
                                <option value="">— None —</option>
                                @foreach($inspectionTypes as $t)
                                    <option value="{{ $t->id }}" @selected(old('inspection_type_id', $run->inspection_type_id) == $t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('inspection_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-7">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks"
                                   class="form-control @error('remarks') is-invalid @enderror"
                                   value="{{ old('remarks', $run->remarks) }}"
                                   placeholder="Optional run note…">
                            @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Testing Parameters ────────────────────────────────────── --}}
            @php $rowIdx = 0; @endphp

            @if($inspection->samples->isEmpty())
            <div class="card mb-4">
                <div class="card-body text-center py-5 text-muted">
                    <i class="feather-alert-circle" style="font-size:2rem;opacity:.3"></i>
                    <p class="mt-2 mb-0">No samples linked to this inspection.
                        <a href="{{ route('inspections.edit', $inspection) }}">Add samples</a> first.
                    </p>
                </div>
            </div>
            @else
                @foreach($inspection->samples as $sample)
                @php
                    $params = $sample->category?->testingParameters ?? collect();
                @endphp
                @if($params->isEmpty()) @continue @endif

                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="feather-package text-muted"></i>
                            <span class="fw-semibold">{{ $sample->sample_code }}</span>
                            @if($sample->product_name)
                                <span class="text-muted">— {{ $sample->product_name }}</span>
                            @endif
                            @if($sample->customer)
                                <span class="badge bg-soft-secondary text-secondary ms-1">{{ $sample->customer->customer_name }}</span>
                            @endif
                            @if($sample->category)
                                <span class="badge bg-soft-primary text-primary ms-auto">{{ $sample->category->category_name }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width:220px">Testing Parameter</th>
                                        <th style="width:140px">Status</th>
                                        <th style="width:180px">Defect</th>
                                        <th>Remarks</th>
                                        <th style="width:200px">Photos / Files</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($params as $param)
                                @php
                                    $key     = "{$sample->id}_{$param->id}";
                                    $result  = $resultsMap[$key] ?? null;
                                    $status  = old("results.{$rowIdx}.status",  $result?->status  ?? 'Pending');
                                    $defectId = old("results.{$rowIdx}.defect_id", $result?->defect_id);
                                    $remarks = old("results.{$rowIdx}.remarks", $result?->remarks ?? '');
                                    $attachments = $result?->attachments ?? collect();
                                @endphp
                                <tr class="{{ $status === 'Rejected' ? 'table-danger' : ($status === 'Fail' ? 'table-warning' : ($status === 'Pass' ? 'table-success' : '')) }}" style="--bs-table-bg-type: transparent;">
                                    <td class="ps-4 fw-semibold fs-13">
                                        <input type="hidden" name="results[{{ $rowIdx }}][sample_id]"            value="{{ $sample->id }}">
                                        <input type="hidden" name="results[{{ $rowIdx }}][testing_parameter_id]" value="{{ $param->id }}">
                                        {{ $param->parameter_name }}
                                        @if($param->description)
                                            <small class="text-muted d-block fw-normal">{{ $param->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="results[{{ $rowIdx }}][status]"
                                                class="form-select form-select-sm result-status"
                                                data-row="{{ $rowIdx }}">
                                            @foreach(['Pending','Pass','Fail','Rejected'] as $s)
                                                <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="results[{{ $rowIdx }}][defect_id]"
                                                class="form-select form-select-sm result-defect defect-{{ $rowIdx }}"
                                                style="{{ $status === 'Rejected' ? '' : 'display:none' }}">
                                            <option value="">— Select —</option>
                                            @foreach($defects as $d)
                                                <option value="{{ $d->id }}" @selected($defectId == $d->id)>{{ $d->defect_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="results[{{ $rowIdx }}][remarks]"
                                               class="form-control form-control-sm"
                                               value="{{ $remarks }}"
                                               placeholder="Optional…">
                                    </td>
                                    <td>
                                        {{-- Existing attachments --}}
                                        @if($attachments->count())
                                        <div class="d-flex flex-wrap gap-1 mb-2" id="thumbs-{{ $rowIdx }}">
                                            @foreach($attachments as $att)
                                            <div class="position-relative" style="width:48px;height:48px;" id="att-{{ $att->id }}">
                                                @if($att->isImage())
                                                    <a href="{{ $att->url }}" target="_blank">
                                                        <img src="{{ $att->url }}" alt="{{ $att->title }}"
                                                             class="rounded border object-fit-cover"
                                                             style="width:48px;height:48px;object-fit:cover;">
                                                    </a>
                                                @else
                                                    <a href="{{ $att->url }}" target="_blank"
                                                       class="d-flex align-items-center justify-content-center border rounded bg-light text-muted"
                                                       style="width:48px;height:48px;" title="{{ $att->file_name }}">
                                                        <i class="feather-file" style="font-size:18px"></i>
                                                    </a>
                                                @endif
                                                <button type="button"
                                                        class="btn btn-danger p-0 position-absolute top-0 end-0 rounded-circle delete-attachment"
                                                        style="width:16px;height:16px;font-size:9px;line-height:1;"
                                                        data-att-id="{{ $att->id }}"
                                                        data-target="att-{{ $att->id }}"
                                                        title="Remove">×</button>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif

                                        {{-- File upload input --}}
                                        <label class="btn btn-xs btn-light-brand w-100 mb-0" style="cursor:pointer;">
                                            <i class="feather-paperclip me-1"></i>Add Files
                                            <input type="file" name="files[{{ $rowIdx }}][]"
                                                   multiple accept="image/*,.pdf,.doc,.docx"
                                                   class="d-none file-input"
                                                   data-row="{{ $rowIdx }}">
                                        </label>
                                        <div id="file-preview-{{ $rowIdx }}" class="d-flex flex-wrap gap-1 mt-1"></div>
                                    </td>
                                </tr>
                                @php $rowIdx++; @endphp
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach

                @if($rowIdx === 0)
                <div class="card mb-4">
                    <div class="card-body text-center py-5 text-muted">
                        <p class="mb-0">The linked samples have no testing parameters configured in their categories.</p>
                        <a href="{{ route('masters.parameters.index') }}" class="btn btn-sm btn-light-brand mt-3">
                            <i class="feather-settings me-1"></i>Manage Parameters
                        </a>
                    </div>
                </div>
                @endif
            @endif

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ── Status → row highlight + defect select visibility ──────────────────
    function applyStatus(sel) {
        const row      = sel.closest('tr');
        const rowIdx   = sel.dataset.row;
        const defect   = document.querySelector('.defect-' + rowIdx);
        const status   = sel.value;

        // Row highlight
        row.classList.remove('table-success', 'table-warning', 'table-danger');
        if (status === 'Pass')                        row.classList.add('table-success');
        else if (status === 'Fail')                   row.classList.add('table-warning');
        else if (status === 'Rejected')               row.classList.add('table-danger');

        // Defect visibility
        if (defect) defect.style.display = status === 'Rejected' ? '' : 'none';
    }

    document.querySelectorAll('.result-status').forEach(sel => {
        sel.addEventListener('change', () => applyStatus(sel));
        applyStatus(sel); // apply on page load
    });

    // ── File input preview ─────────────────────────────────────────────────
    document.querySelectorAll('.file-input').forEach(input => {
        input.addEventListener('change', function () {
            const rowIdx  = this.dataset.row;
            const preview = document.getElementById('file-preview-' + rowIdx);
            preview.innerHTML = '';

            [...this.files].forEach(file => {
                const wrap = document.createElement('div');
                wrap.style.cssText = 'width:48px;height:48px;position:relative;';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.className = 'rounded border object-fit-cover';
                    img.style.cssText = 'width:48px;height:48px;object-fit:cover;';
                    const reader = new FileReader();
                    reader.onload = e => img.src = e.target.result;
                    reader.readAsDataURL(file);
                    wrap.appendChild(img);
                } else {
                    wrap.innerHTML = `<div class="d-flex align-items-center justify-content-center border rounded bg-light text-muted" style="width:48px;height:48px;"><i class="feather-file" style="font-size:18px"></i></div>`;
                }

                const badge = document.createElement('small');
                badge.className = 'text-muted';
                badge.style.cssText = 'display:block;font-size:9px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:48px;';
                badge.textContent = file.name;

                const outer = document.createElement('div');
                outer.style.cssText = 'display:flex;flex-direction:column;align-items:center;';
                outer.appendChild(wrap);
                outer.appendChild(badge);
                preview.appendChild(outer);
            });
        });
    });

    // ── Attachment delete (AJAX) ───────────────────────────────────────────
    document.querySelectorAll('.delete-attachment').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Remove this attachment?')) return;
            const attId  = this.dataset.attId;
            const target = document.getElementById(this.dataset.target);

            fetch(`/attachments/${attId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.ok ? target?.remove() : alert('Could not delete attachment.'))
            .catch(() => alert('Network error.'));
        });
    });
})();
</script>
@endpush
