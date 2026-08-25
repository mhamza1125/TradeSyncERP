@php
    $companySetting = $companySetting ?? \App\Models\CompanySetting::current();

    $addressLine = trim(collect([
        $companySetting->address,
        $companySetting->city,
        $companySetting->country,
    ])->filter()->implode(', '));

    $contactLine = collect([
        $companySetting->phone,
        $companySetting->email,
        $companySetting->website,
    ])->filter()->map(fn ($v) => e($v))->implode(' &nbsp;|&nbsp; ');
@endphp
<div class="pdf-footer">
    <table>
        @if($addressLine)
        <tr>
            <td class="pf-center pf-address">{{ $addressLine }}</td>
        </tr>
        @endif
        @if($contactLine)
        <tr>
            <td class="pf-center pf-contact">{!! $contactLine !!}</td>
        </tr>
        @endif
    </table>
</div>
