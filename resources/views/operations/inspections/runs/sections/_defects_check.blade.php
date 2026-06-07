{{-- Denim & Textile Defects Section --}}
{{-- Expects: $runSection, $defects, $uploadUrl, $inspection, $run --}}
@php
    $d          = $runSection->data ?? [];
    $selections = collect($d['selections'] ?? [])->keyBy('defect_id');
    $rsId       = $runSection->id;
    $attsByTask = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');
    $grouped    = $defects->groupBy(fn($d) => $d->category?->name ?? 'General');
@endphp

@if($defects->isEmpty())
<div class="text-center py-5 text-muted">
    <i class="feather-alert-triangle fs-2 d-block mb-2 opacity-30"></i>
    <p class="mb-0">No defects configured.</p>
    <small>Add defects in Masters → Defects before using this section.</small>
</div>
@else
<p class="text-muted fs-13 mb-3">
    Check all defects found during inspection. Leave unchecked if not applicable.
    Add severity and photos for each selected defect.
</p>

<div data-section-wrapper="{{ $rsId }}">
    @foreach($grouped as $category => $categoryDefects)
    <div class="mb-4">
        <div class="fw-semibold text-uppercase fs-11 text-muted mb-2 border-bottom pb-1">
            {{ $category }}
        </div>

        @foreach($categoryDefects as $defect)
        @php
            $uid      = $rsId . '_' . $defect->id;
            $sel      = $selections->get($defect->id);
            $isChecked = !empty($sel['selected']);
            $severity  = $sel['severity'] ?? '';
            $comment   = $sel['comment']  ?? '';
            $defIdx    = $defects->search(fn($d) => $d->id === $defect->id);
            $taskKey   = 'defect_' . $defect->id;
            $defAtts   = $attsByTask->get($taskKey, collect());
        @endphp

        <div class="defect-card card mb-2 {{ $isChecked ? 'border-warning' : 'border-light' }}"
             id="defect-card-{{ $uid }}">
            <div class="card-body py-2 px-3">

                {{-- Defect header row --}}
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check mb-0">
                        <input type="hidden"
                               name="sections[{{ $rsId }}][data][selections][{{ $defIdx }}][defect_id]"
                               value="{{ $defect->id }}">
                        <input type="checkbox"
                               class="form-check-input defect-toggle"
                               id="defect-{{ $uid }}"
                               name="sections[{{ $rsId }}][data][selections][{{ $defIdx }}][selected]"
                               value="1"
                               data-uid="{{ $uid }}"
                               {{ $isChecked ? 'checked' : '' }}>
                    </div>
                    <label for="defect-{{ $uid }}" class="fw-semibold flex-grow-1 mb-0 cursor-pointer fs-13">
                        {{ $defect->defect_name }}
                    </label>
                    @if($isChecked)
                    <span class="badge bg-soft-warning text-warning fs-11">Found</span>
                    @endif
                </div>

                {{-- Defect details (shown when checked) --}}
                <div id="dd-{{ $uid }}" class="{{ $isChecked ? '' : 'd-none' }} mt-2 ps-4">
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <select name="sections[{{ $rsId }}][data][selections][{{ $defIdx }}][severity]"
                                    class="form-select form-select-sm">
                                <option value="">— Severity —</option>
                                @foreach(['Critical','Major','Minor'] as $sev)
                                <option value="{{ $sev }}"
                                        @selected($severity === $sev)
                                        class="{{ $sev === 'Critical' ? 'text-danger' : ($sev === 'Major' ? 'text-warning' : 'text-info') }}">
                                    {{ $sev }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <input type="text"
                                   name="sections[{{ $rsId }}][data][selections][{{ $defIdx }}][comment]"
                                   class="form-control form-control-sm"
                                   value="{{ $comment }}"
                                   placeholder="Observation / comment…">
                        </div>
                    </div>

                    {{-- Per-defect attachments --}}
                    <div class="attachment-area" data-upload-url="{{ $uploadUrl }}" data-task-key="{{ $taskKey }}">
                        <div class="att-previews d-flex flex-wrap gap-2 mb-1">
                            @foreach($defAtts as $att)
                            <div class="att-thumb position-relative d-inline-block" id="att-{{ $att->id }}">
                                @if($att->isImage())
                                    <img src="{{ $att->url }}" class="rounded border"
                                         style="width:56px;height:56px;object-fit:cover" alt="">
                                @else
                                    <div class="d-flex flex-column align-items-center justify-content-center border rounded bg-light"
                                         style="width:56px;height:56px">
                                        <i class="feather-file text-muted" style="font-size:16px"></i>
                                        <small class="text-muted mt-1"
                                               style="font-size:8px;max-width:52px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                            {{ $att->file_name }}
                                        </small>
                                    </div>
                                @endif
                                <button type="button"
                                        class="att-delete-btn btn btn-danger btn-sm p-0 position-absolute top-0 end-0 d-flex align-items-center justify-content-center"
                                        style="width:16px;height:16px;font-size:9px;border-radius:50%;margin:-3px;z-index:1;"
                                        data-delete-url="{{ route('inspections.runs.attachments.delete', [$inspection, $run, $att]) }}"
                                        data-thumb-id="att-{{ $att->id }}">×</button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="add-files-btn btn btn-sm btn-light border" style="font-size:11px">
                            <i class="feather-camera me-1" style="font-size:11px"></i>Add Photos
                        </button>
                        <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf">
                    </div>
                </div>

            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endif
