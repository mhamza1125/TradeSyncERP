{{-- Loading Schedule & Timing Section --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $v     = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
    $items = $data['items'] ?? $runSection->section->default_data['items'] ?? [];
    $attsByTask = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');
@endphp

{{-- Date & time fields --}}
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Planned Loading Date</label>
        <input type="date"
               name="sections[{{ $secId }}][data][planned_loading_date]"
               class="form-control form-control-sm"
               value="{{ $v('planned_loading_date') }}">
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Actual Loading Date</label>
        <input type="date"
               name="sections[{{ $secId }}][data][actual_loading_date]"
               class="form-control form-control-sm"
               value="{{ $v('actual_loading_date') }}">
    </div>
    <div class="col-lg-2 col-md-6">
        <label class="form-label fw-semibold fs-12">Loading Start Time</label>
        <input type="time"
               name="sections[{{ $secId }}][data][loading_start_time]"
               class="form-control form-control-sm"
               value="{{ $v('loading_start_time') }}">
    </div>
    <div class="col-lg-2 col-md-6">
        <label class="form-label fw-semibold fs-12">Loading End Time</label>
        <input type="time"
               name="sections[{{ $secId }}][data][loading_end_time]"
               class="form-control form-control-sm"
               value="{{ $v('loading_end_time') }}">
    </div>
</div>

{{-- Checklist items --}}
@if(!empty($items))
<div class="table-responsive" data-checklist-wrapper="{{ $secId }}">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3" style="width:36px">#</th>
                <th>Checkpoint</th>
                <th style="width:240px">Result</th>
                <th style="width:280px">Attachments</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $idx => $item)
        @php
            $result   = old("sections.{$secId}.data.items.{$idx}.result", $item['result'] ?? null);
            $rowClass = match($result) {
                'Pass' => 'table-success',
                'Fail' => 'table-danger',
                'N/A'  => 'table-light text-muted',
                default => '',
            };
            $taskKey  = 'checkpoint_' . $idx;
            $itemAtts = $attsByTask->get($taskKey, collect());
        @endphp
        <tr class="{{ $rowClass }}" data-result-row style="--bs-table-bg-type: transparent;">
            <td class="text-muted ps-3">{{ $idx + 1 }}</td>
            <td class="fw-semibold fs-13">
                <input type="hidden" name="sections[{{ $secId }}][data][items][{{ $idx }}][label]" value="{{ $item['label'] ?? '' }}">
                {{ $item['label'] ?? '' }}
            </td>
            <td>
                @include('operations.inspections.runs.sections._result_toggle', [
                    'name'  => "sections[{$secId}][data][items][{$idx}][result]",
                    'value' => $result,
                ])
            </td>
            <td>
                <div class="attachment-area" data-upload-url="{{ $uploadUrl }}" data-task-key="{{ $taskKey }}">
                    <div class="att-previews d-flex flex-wrap gap-1 mb-1">
                        @foreach($itemAtts as $att)
                        <div class="att-thumb position-relative d-inline-block" id="att-{{ $att->id }}">
                            @if($att->isImage())
                                <a href="{{ $att->url }}" target="_blank" rel="noopener noreferrer">
                                    <img src="{{ $att->url }}" class="rounded border"
                                         style="width:40px;height:40px;object-fit:cover" alt="">
                                </a>
                            @else
                                <a href="{{ $att->url }}" target="_blank" rel="noopener noreferrer">
                                    <div class="d-flex align-items-center justify-content-center border rounded bg-light"
                                         style="width:40px;height:40px">
                                        <i class="feather-file text-muted" style="font-size:13px"></i>
                                    </div>
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
                        <i class="feather-plus me-1"></i>Attach
                    </button>
                    <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf,.doc,.docx">
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
