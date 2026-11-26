{{-- One salary-line value: an input while the run is a Draft, plain text (plus the hidden value) once paid.
     Params: $i (line index), $field, $value, $paid, $label (for screen readers), $int (whole numbers, optional),
     $align ('end' | 'center', optional), $attrs (extra attributes, optional), $extraClass (optional). --}}
@php
    $int   = $int ?? false;
    $align = $align ?? 'end';
@endphp
@if($paid)
    {{ $int ? (int) $value : number_format((float) $value, 2) }}
    <input type="hidden" name="lines[{{ $i }}][{{ $field }}]" value="{{ $value }}">
@else
    <input type="number" @if(!$int) step="0.01" @endif min="0"
           name="lines[{{ $i }}][{{ $field }}]"
           class="form-control line-field text-{{ $align }} {{ $extraClass ?? '' }}"
           value="{{ $value }}" data-field="{{ $field }}" aria-label="{{ $label }}"
           @foreach(($attrs ?? []) as $attr => $attrValue) {{ $attr }}="{{ $attrValue }}" @endforeach>
@endif
