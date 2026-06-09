{{-- Overall Carton Condition Section — checkpoint-style with attachment --}}
{{-- Expects: $runSection, $uploadUrl, $inspection, $run --}}
@php
    $data    = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId   = $runSection->id;
    $result  = old("sections.{$secId}.data.overall_condition", $data['overall_condition'] ?? null);
    $remarks = old("sections.{$secId}.data.remarks", $data['remarks'] ?? '');
@endphp

<div class="table-responsive mb-4">
    <table class="table table-sm table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="ps-3">Checkpoint</th>
                <th style="width:200px">Result</th>
                <th style="width:220px">Attachments</th>
            </tr>
        </thead>
        <tbody>
        <tr class="{{ match($result) { 'Pass' => 'table-success', 'Fail' => 'table-danger', 'N/A' => 'table-light text-muted', default => '' } }}"
            data-result-row style="--bs-table-bg-type: transparent;">
            <td class="fw-semibold fs-13 ps-3">Overall Carton Condition</td>
            <td>
                @include('operations.inspections.runs.sections._result_toggle', [
                    'name'  => "sections[{$secId}][data][overall_condition]",
                    'value' => $result,
                ])
            </td>
            <td>
                <div class="attachment-area" data-upload-url="{{ $uploadUrl }}" data-task-key="carton_condition">
                    <div class="att-previews d-flex flex-wrap gap-1 mb-1">
                        @foreach($runSection->attachments->where('task_key', 'carton_condition') as $att)
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
                    <input type="file" class="att-file-input d-none" multiple accept="image/*,.pdf">
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div>
    <label class="form-label fw-semibold fs-12">Remarks</label>
    <textarea name="sections[{{ $secId }}][data][remarks]"
              rows="2"
              class="form-control form-control-sm"
              placeholder="Remarks about the overall condition of loaded cartons…">{{ $remarks }}</textarea>
</div>
