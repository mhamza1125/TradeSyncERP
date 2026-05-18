{{-- Workmanship Check — renders existing inspection_results testing parameter rows --}}
@php $rowIdx = 0; @endphp

@if($inspection->samples->isEmpty())
    <div class="alert alert-soft-warning mb-0">
        No samples linked to this inspection.
        <a href="{{ route('inspections.edit', $inspection) }}">Add samples</a> first.
    </div>
@else
    @foreach($inspection->samples as $sample)
    @php
        $params = $sample->category?->testingParameters ?? collect();
    @endphp
    @if($params->isEmpty()) @continue @endif

    <h6 class="fw-semibold mb-2 mt-3 d-flex align-items-center gap-2">
        <i class="feather-package text-muted"></i>
        {{ $sample->sample_code }}
        @if($sample->product_name)
            <span class="text-muted fw-normal">— {{ $sample->product_name }}</span>
        @endif
        @if($sample->customer)
            <span class="badge bg-soft-secondary text-secondary">{{ $sample->customer->customer_name }}</span>
        @endif
        @if($sample->category)
            <span class="badge bg-soft-primary text-primary ms-auto">{{ $sample->category->category_name }}</span>
        @endif
    </h6>

    <div class="table-responsive mb-4">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3" style="width:200px">Parameter</th>
                    <th style="width:130px">Status</th>
                    <th style="width:160px" class="defect-col">Defect</th>
                    <th style="width:130px" class="defect-col">Severity</th>
                    <th>Remarks</th>
                    <th style="width:180px">Photos / Files</th>
                </tr>
            </thead>
            <tbody>
            @foreach($params as $param)
            @php
                $key        = "{$sample->id}_{$param->id}";
                $result     = $resultsMap[$key] ?? null;
                $status     = old("results.{$rowIdx}.status", $result?->status ?? 'Pending');
                $defectId   = old("results.{$rowIdx}.defect_id", $result?->defect_id);
                $severity   = old("results.{$rowIdx}.defect_severity", $result?->defect_severity);
                $remarks    = old("results.{$rowIdx}.remarks", $result?->remarks ?? '');
                $attachments= $result?->attachments ?? collect();
                $showDefect = in_array($status, ['Fail', 'Rejected']);
            @endphp

            <tr class="{{ $status === 'Rejected' ? 'table-danger' : ($status === 'Fail' ? 'table-warning' : ($status === 'Pass' ? 'table-success' : '')) }}"
                style="--bs-table-bg-type: transparent;">
                <td class="ps-3 fw-semibold fs-13">
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
                    <div class="defect-wrap {{ $showDefect ? '' : 'd-none' }}">
                        <select name="results[{{ $rowIdx }}][defect_id]"
                                class="form-select form-select-sm defect-select-{{ $rowIdx }}">
                            <option value="">— Select —</option>
                            @foreach($defects as $d)
                                <option value="{{ $d->id }}"
                                        @selected($defectId == $d->id)>
                                    {{ $d->defect_name }}
                                    @if($d->category) ({{ $d->category->code }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>

                <td>
                    <div class="severity-wrap {{ $showDefect ? '' : 'd-none' }}">
                        <select name="results[{{ $rowIdx }}][defect_severity]"
                                class="form-select form-select-sm severity-select-{{ $rowIdx }}">
                            <option value="">— Severity —</option>
                            @foreach(['Critical','Major','Minor','Functional'] as $sv)
                                <option value="{{ $sv }}" @selected($severity === $sv)>{{ $sv }}</option>
                            @endforeach
                        </select>
                    </div>
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
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @foreach($attachments as $att)
                        <div class="position-relative" style="width:48px;height:48px;" id="att-{{ $att->id }}">
                            @if($att->isImage())
                                <a href="{{ $att->url }}" target="_blank">
                                    <img src="{{ $att->url }}" class="rounded border" style="width:48px;height:48px;object-fit:cover;" alt="{{ $att->title }}">
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

                    <label class="btn btn-xs btn-light-brand w-100 mb-0" style="cursor:pointer;">
                        <i class="feather-paperclip me-1"></i>Add Files
                        <input type="file"
                               name="files[{{ $rowIdx }}][]"
                               multiple accept="image/*,.pdf,.doc,.docx"
                               class="d-none file-input"
                               data-preview="fp-{{ $rowIdx }}">
                    </label>
                    <div id="fp-{{ $rowIdx }}" class="d-flex flex-wrap gap-1 mt-1"></div>
                </td>
            </tr>
            @php $rowIdx++; @endphp
            @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    @if($rowIdx === 0)
    <div class="alert alert-soft-info mb-0">
        The linked samples have no testing parameters in their categories.
        <a href="{{ route('masters.parameters.index') }}">Manage parameters</a>
    </div>
    @endif
@endif
