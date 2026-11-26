{{-- Label/value grid, two pairs per row. Params: $fields (label => value; empty values are left out). --}}
@php
    $kvFields = collect($fields)->filter(fn ($v) => $v !== null && $v !== '')->all();
    $kvPairs = array_chunk($kvFields, 2, true);
@endphp
@if(!empty($kvFields))
<table class="kv">
    @foreach($kvPairs as $pair)
    <tr>
        @foreach($pair as $label => $value)
        <td class="kv-k">{{ $label }}</td>
        <td class="kv-v">{{ $value }}</td>
        @endforeach
        @if(count($pair) === 1)<td class="kv-k"></td><td class="kv-v"></td>@endif
    </tr>
    @endforeach
</table>
@endif
