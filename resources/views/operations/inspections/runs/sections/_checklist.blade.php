{{-- Generic Checkpoint section — handles all checklist-type sections --}}
{{-- Supports standard items (label/result) + per-item attachments --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $items = $data['items'] ?? [];
    $secId = $runSection->section->slug;
    $rsId  = $runSection->id;
    $slug  = $runSection->section->slug;
    $hasUpload = isset($uploadUrl);

    $isCartonVerification = $slug === 'carton_verification';
    $cartonKeys = ['total_qty_ordered', 'total_qty_loaded', 'total_cartons_ordered', 'total_cartons_loaded'];
    $cv = fn(string $key) => old("sections.{$rsId}.data.{$key}", $data[$key] ?? '');

    // Group attachments by task_key for per-item attachment display
    $attsByTask = $runSection->attachments->groupBy(fn($a) => $a->task_key ?? '__none__');
@endphp

{{-- Carton Verification header fields --}}
@if($isCartonVerification)
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold fs-12">Total Quantity Ordered</label>
        <input type="number" name="sections[{{ $rsId }}][data][total_qty_ordered]"
               class="form-control form-control-sm" min="0" placeholder="0"
               value="{{ $cv('total_qty_ordered') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold fs-12">Total Loaded Quantity</label>
        <input type="number" name="sections[{{ $rsId }}][data][total_qty_loaded]"
               class="form-control form-control-sm" min="0" placeholder="0"
               value="{{ $cv('total_qty_loaded') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold fs-12">Total Cartons Ordered</label>
        <input type="number" name="sections[{{ $rsId }}][data][total_cartons_ordered]"
               class="form-control form-control-sm" min="0" placeholder="0"
               value="{{ $cv('total_cartons_ordered') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold fs-12">Total Cartons Loaded</label>
        <input type="number" name="sections[{{ $rsId }}][data][total_cartons_loaded]"
               class="form-control form-control-sm" min="0" placeholder="0"
               value="{{ $cv('total_cartons_loaded') }}">
    </div>
</div>
@endif

{{-- Extra top-level fields (except carton fields and items) --}}
@php
    $topFields = collect($data)->except('items');
    if ($isCartonVerification) {
        $topFields = $topFields->except($cartonKeys);
    }
@endphp

@if($topFields->isNotEmpty())
<div class="row g-3 mb-4">
    @foreach($topFields as $fieldKey => $fieldVal)
    @php $label = ucwords(str_replace('_', ' ', $fieldKey)); @endphp
    <div class="col-lg-4 col-md-6">
        <label class="form-label fw-semibold fs-12">{{ $label }}</label>
        <input type="text"
               name="sections[{{ $rsId }}][data][{{ $fieldKey }}]"
               class="form-control form-control-sm"
               value="{{ old("sections.{$rsId}.data.{$fieldKey}", $fieldVal) }}"
               placeholder="{{ $label }}…">
    </div>
    @endforeach
</div>
@endif

{{-- Checkpoint table --}}
@if(!empty($items))
<div class="table-responsive mb-3" data-checklist-wrapper="{{ $rsId }}">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3" style="width:36px">#</th>
                <th>Checkpoint</th>
                <th style="width:200px">Result</th>
                @if($hasUpload)
                    <th style="width:200px">Attachments</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @foreach($items as $idx => $item)
        @php
            $result   = old("sections.{$rsId}.data.items.{$idx}.result", $item['result'] ?? null);
            $taskKey  = 'checkpoint_' . $idx;
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
            @if($hasUpload)
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
            @endif
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@else
<p class="text-muted fst-italic">No checkpoints defined for this section.</p>
@endif
