{{-- Variations vs. Approved Sample — checklist checkpoints + general comparison photos --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $data       = $runSection->data ?? $runSection->section->default_data ?? [];
    $items      = $data['items'] ?? [];
    $rsId       = $runSection->id;
    $attsByTask = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');
@endphp

@if(!empty($items))
<div class="table-responsive mb-4" data-checklist-wrapper="{{ $rsId }}">
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
            $result  = old("sections.{$rsId}.data.items.{$idx}.result", $item['result'] ?? null);
            $taskKey = 'checkpoint_' . $idx;
            $itemAtts = $attsByTask->get($taskKey, collect());
            $rowClass = match($result) {
                'Pass' => 'table-success',
                'Fail' => 'table-danger',
                'N/A'  => 'table-light text-muted',
                default => '',
            };
        @endphp
        <tr class="{{ $rowClass }}" data-result-row style="--bs-table-bg-type: transparent;">
            <td class="text-muted ps-3">{{ $idx + 1 }}</td>
            <td class="fw-semibold fs-13">
                <input type="hidden" name="sections[{{ $rsId }}][data][items][{{ $idx }}][label]"
                       value="{{ $item['label'] ?? '' }}">
                {{ $item['label'] ?? '' }}
            </td>
            <td>
                @include('operations.inspections.runs.sections._result_toggle', [
                    'name'  => "sections[{$rsId}][data][items][{$idx}][result]",
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
@else
<p class="text-muted fst-italic mb-4">No checkpoints defined for this section.</p>
@endif

{{-- General comparison photo area --}}
<div class="mb-3">
    <h6 class="fw-semibold mb-2 fs-13">
        <i class="feather-camera me-1 text-muted"></i>Sample Comparison Photos
    </h6>
    <p class="text-muted fs-12 mb-2">Upload side-by-side or comparison photos of production vs. approved sample.</p>
    @include('operations.inspections.runs.sections._photo_upload', [
        'runSection' => $runSection,
        'uploadUrl'  => $uploadUrl,
        'inspection' => $inspection,
        'run'        => $run,
        'taskKey'    => 'sample_comparison_photos',
    ])
</div>
