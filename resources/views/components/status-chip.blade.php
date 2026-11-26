{{-- One status / verdict chip for the whole app (docs/DESIGN.md §3.4): always the text plus a colour.
     Usage: <x-status-chip :status="$inspection->overall_status" />  or  <x-status-chip :status="$row->severity" kind="severity" /> --}}
@props(['status' => null, 'kind' => 'verdict'])
@php
    $value = (string) ($status ?? '');
    $key = strtolower($value);
    $class = $kind === 'severity'
        ? match ($key) { 'critical' => 'critical', 'major' => 'major', 'minor' => 'minor', default => 'neutral' }
        : match ($key) {
            'pass', 'complete', 'completed', 'paid', 'active' => 'pass',
            'fail', 'failed', 'rejected', 'void', 'voided' => 'fail',
            'conditional' => 'conditional',
            're-inspection' => 'reinspection',
            default => 'pending',
        };
    $label = $value === '' ? 'Pending' : ($kind === 'severity' ? ucfirst($value) : $value);
@endphp
<span {{ $attributes->merge(['class' => "ts-chip ts-chip-{$class}"]) }}>{{ $label }}</span>
