{{-- Pass / Fail / N/A rows. Params: $rows (label, result, remarks), $itemLabel (column title).
     Rows without a result are not printed as rows; they are named once underneath as "Not checked". --}}
@php
    $rtChecked = collect($rows)->filter(fn ($r) => $r['result'] !== '')->values();
    $rtOpen = collect($rows)->filter(fn ($r) => $r['result'] === '')->pluck('label')->filter()->values();
    $rtRemarks = $rtChecked->contains(fn ($r) => trim($r['remarks']) !== '');
@endphp
@if($rtChecked->isNotEmpty())
<table class="tbl">
    <thead>
        <tr>
            <th style="width:6%" class="c">#</th>
            <th style="width:{{ $rtRemarks ? '50%' : '76%' }}">{{ $itemLabel ?? 'Checkpoint' }}</th>
            <th style="width:18%" class="c">Result</th>
            @if($rtRemarks)<th style="width:26%">Remarks</th>@endif
        </tr>
    </thead>
    <tbody>
        @foreach($rtChecked as $i => $r)
        @php $rk = \App\Services\Inspection\InspectionReportService::resultKey($r['result']); @endphp
        <tr class="{{ $rk === 'fail' ? 'row-fail' : '' }}">
            <td class="c muted">{{ $i + 1 }}</td>
            <td>{{ $r['label'] }}</td>
            <td class="c"><span class="chip chip-{{ match($rk) { 'pass' => 'pass', 'fail' => 'fail', 'na' => 'na', default => 'pending' } }}">{{ match($rk) { 'pass' => 'PASS', 'fail' => 'FAIL', 'na' => 'N/A', default => $r['result'] } }}</span></td>
            @if($rtRemarks)<td class="small">{{ $r['remarks'] }}</td>@endif
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@if($rtOpen->isNotEmpty())
<div class="not-checked"><strong>Not checked ({{ $rtOpen->count() }}):</strong> {{ $rtOpen->implode('; ') }}</div>
@endif
