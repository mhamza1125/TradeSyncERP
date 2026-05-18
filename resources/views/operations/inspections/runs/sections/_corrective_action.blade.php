{{-- Corrective Action Plan (CAP) --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $items = $data['items'] ?? [];
    $secId = $runSection->id;
@endphp

<p class="text-muted fs-13 mb-3">
    Document corrective actions for each non-conformance found during inspection.
</p>

<div class="table-responsive mb-3">
    <table class="table table-sm table-bordered align-middle mb-0" id="cap-table-{{ $secId }}">
        <thead class="table-light">
            <tr>
                <th class="ps-3" style="width:30px">#</th>
                <th>Defect / Non-Conformance</th>
                <th>Root Cause</th>
                <th>Corrective Action</th>
                <th style="width:130px">Responsible</th>
                <th style="width:110px">Target Date</th>
                <th style="width:100px">Status</th>
            </tr>
        </thead>
        <tbody id="cap-body-{{ $secId }}">
        @foreach($items as $idx => $item)
        <tr>
            <td class="ps-3 text-muted fs-12">{{ $idx + 1 }}</td>
            <td>
                <input type="text"
                       name="sections[{{ $secId }}][data][items][{{ $idx }}][defect_description]"
                       class="form-control form-control-sm"
                       value="{{ old("sections.{$secId}.data.items.{$idx}.defect_description", $item['defect_description'] ?? '') }}"
                       placeholder="Describe the defect…">
            </td>
            <td>
                <input type="text"
                       name="sections[{{ $secId }}][data][items][{{ $idx }}][root_cause]"
                       class="form-control form-control-sm"
                       value="{{ old("sections.{$secId}.data.items.{$idx}.root_cause", $item['root_cause'] ?? '') }}"
                       placeholder="Root cause…">
            </td>
            <td>
                <input type="text"
                       name="sections[{{ $secId }}][data][items][{{ $idx }}][corrective_action]"
                       class="form-control form-control-sm"
                       value="{{ old("sections.{$secId}.data.items.{$idx}.corrective_action", $item['corrective_action'] ?? '') }}"
                       placeholder="Action to take…">
            </td>
            <td>
                <input type="text"
                       name="sections[{{ $secId }}][data][items][{{ $idx }}][responsible_party]"
                       class="form-control form-control-sm"
                       value="{{ old("sections.{$secId}.data.items.{$idx}.responsible_party", $item['responsible_party'] ?? '') }}"
                       placeholder="Name / dept…">
            </td>
            <td>
                <input type="date"
                       name="sections[{{ $secId }}][data][items][{{ $idx }}][target_date]"
                       class="form-control form-control-sm"
                       value="{{ old("sections.{$secId}.data.items.{$idx}.target_date", $item['target_date'] ?? '') }}">
            </td>
            <td>
                <select name="sections[{{ $secId }}][data][items][{{ $idx }}][status]"
                        class="form-select form-select-sm">
                    @foreach(['Open','In Progress','Closed','Rejected'] as $st)
                        <option value="{{ $st }}" @selected(($item['status'] ?? 'Open') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

<button type="button"
        class="btn btn-xs btn-light-brand"
        onclick="addCapRow('{{ $secId }}', {{ count($items) }})">
    <i class="feather-plus me-1"></i>Add Row
</button>

<script>
(function() {
    let capCount_{{ $secId }} = {{ count($items) }};

    window.addCapRow = function(secId, startIdx) {
        if (secId !== '{{ $secId }}') return;
        const idx  = capCount_{{ $secId }}++;
        const body = document.getElementById('cap-body-' + secId);
        const row  = document.createElement('tr');
        row.innerHTML = `
            <td class="ps-3 text-muted fs-12">${idx + 1}</td>
            <td><input type="text" name="sections[${secId}][data][items][${idx}][defect_description]" class="form-control form-control-sm" placeholder="Describe the defect…"></td>
            <td><input type="text" name="sections[${secId}][data][items][${idx}][root_cause]" class="form-control form-control-sm" placeholder="Root cause…"></td>
            <td><input type="text" name="sections[${secId}][data][items][${idx}][corrective_action]" class="form-control form-control-sm" placeholder="Action to take…"></td>
            <td><input type="text" name="sections[${secId}][data][items][${idx}][responsible_party]" class="form-control form-control-sm" placeholder="Name / dept…"></td>
            <td><input type="date"  name="sections[${secId}][data][items][${idx}][target_date]" class="form-control form-control-sm"></td>
            <td>
                <select name="sections[${secId}][data][items][${idx}][status]" class="form-select form-select-sm">
                    <option value="Open" selected>Open</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Closed">Closed</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </td>`;
        body.appendChild(row);
    };
})();
</script>
