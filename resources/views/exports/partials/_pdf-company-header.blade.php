@php
    $companySetting = $companySetting ?? \App\Models\CompanySetting::current();
    $logoPath = $companySetting->logo_absolute_path ?? public_path('assets/images/logo-pdf.png');
@endphp
<div class="pdf-header">
    <table class="ph-logo-row">
        <tr>
            <td class="ph-logo-cell-centered">
                <img src="{{ $logoPath }}" alt="{{ $companySetting->company_name }}">
            </td>
        </tr>
    </table>
</div>
