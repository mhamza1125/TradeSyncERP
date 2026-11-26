{{-- Brand running footer for PDFs (docs/DESIGN.md §5). Params: $footerNote (optional), $generatedAt (optional,
     defaults to now). "Page N of M" is drawn by App\Services\Pdf\PdfPageNumberService into the empty
     right-hand cell (the template must carry <meta name="pdf-page-numbers" content="1">). --}}
@php
    $companySetting = $companySetting ?? \App\Models\CompanySetting::current();
    $footerCompany = collect([
        $companySetting->company_name,
        collect([$companySetting->address, $companySetting->city, $companySetting->country])->filter()->implode(', '),
    ])->filter()->implode('  ·  ');
@endphp
<div class="bf">
    <table>
        <tr>
            <td>
                <div class="bf-company">{{ $footerCompany }}</div>
                <div>
                    @isset($footerNote){{ $footerNote }} &nbsp;·&nbsp; @endisset
                    Generated {{ ($generatedAt ?? now())->format('d F Y, H:i') }}
                </div>
            </td>
            <td class="bf-pages"></td>
        </tr>
    </table>
</div>
