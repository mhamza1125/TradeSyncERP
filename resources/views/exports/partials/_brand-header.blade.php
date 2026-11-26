{{-- Brand running header for PDFs (docs/DESIGN.md §5). Params: $docTitle, $docNumber (both optional).
     Logo: the company logo from Company Settings, else public/assets/images/logo-pdf.png, loaded by
     filesystem path — the same mechanism as _pdf-company-header. All values are escaped.
     Row 1: logo | document title + number.  Row 2: company name and details on one line. --}}
@php
    $companySetting = $companySetting ?? \App\Models\CompanySetting::current();
    $brandLogoPath = $companySetting->logo_absolute_path ?? public_path('assets/images/logo-pdf.png');
    $brandDetails = collect([
        $companySetting->company_name,
        collect([$companySetting->address, $companySetting->city, $companySetting->country])->filter()->implode(', '),
        $companySetting->phone,
        $companySetting->email,
        $companySetting->website,
    ])->filter()->implode('  ·  ');
@endphp
<div class="bh">
    <table>
        <tr>
            <td class="bh-logo">
                @if(is_file($brandLogoPath))
                    <img src="{{ $brandLogoPath }}" alt="{{ $companySetting->company_name }}">
                @else
                    <span class="bh-company-name">{{ $companySetting->company_name }}</span>
                @endif
            </td>
            <td class="bh-doc">
                @isset($docTitle)<div class="bh-doc-title">{{ $docTitle }}</div>@endisset
                @isset($docNumber)<div class="bh-doc-number">{{ $docNumber }}</div>@endisset
            </td>
        </tr>
    </table>
    <div class="bh-company-details">{{ $brandDetails }}</div>
    <div class="bh-rule"></div>
</div>
