{{--
    Shared running header for all PDF exports.
    Variables:
      $reportTitle    — e.g. "Customer Directory"
      $reportSubtitle — e.g. "All active customers" (optional)
      $reportRef      — e.g. invoice number (optional, shown on right)
--}}
<div class="pdf-header">
    {{-- Row 1: Company branding + logo --}}
    <table class="ph-logo-row">
        <tr>
            <td class="ph-logo-cell">
                <img src="{{ public_path('assets/images/logo-trade.png') }}" alt="TradeSyncERP">
            </td>
            <td class="ph-brand-cell">
                <div class="ph-brand-name">TradeSyncERP</div>
                <div class="ph-brand-tag">Quality Control &amp; ERP System</div>
            </td>
            <td class="ph-right">{{ now()->format('d M Y, H:i') }}</td>
        </tr>
    </table>
    {{-- Row 2: Report-specific title --}}
    <table class="ph-info-row">
        <tr>
            <td class="ph-left">
                {{ $reportTitle ?? '' }}
                @isset($reportSubtitle)
                    <span class="ph-sub">| {{ $reportSubtitle }}</span>
                @endisset
            </td>
            <td class="ph-center">Confidential</td>
            <td class="ph-right">
                @isset($reportRef){{ $reportRef }}@endisset
            </td>
        </tr>
    </table>
</div>
