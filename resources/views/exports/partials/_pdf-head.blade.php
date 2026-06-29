<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
body, body * { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9pt;
    color: #212121;
    line-height: 1.55;
}

@page {
    size: A4 portrait;
    margin: 24mm 15mm 28mm 15mm;
}

.page-break { page-break-after: always; }
.no-break   { page-break-inside: avoid; }

/* ── Fixed running header (repeats every page) ─────────── */
.pdf-header {
    position: fixed;
    top: -20mm;
    left: -15mm; right: -15mm;
    border-bottom: 2px solid #01AFEE;
    padding: 0 15mm;
    background: #ffffff;
}

/* Centered logo row */
.ph-logo-row { width: 100%; border-collapse: collapse; padding: 4px 0; }
.ph-logo-cell-centered { text-align: center; vertical-align: middle; }
.ph-logo-cell-centered img { height: 42px; width: auto; display: inline-block; }

/* ── Fixed footer ──────────────────────────────────────── */
.pdf-footer {
    position: fixed;
    bottom: -24mm;
    left: -15mm; right: -15mm;
    border-top: 2px solid #01AFEE;
    padding: 4px 15mm 0;
    background: #ffffff;
}
.pdf-footer table { width: 100%; border-collapse: collapse; }
.pf-center  { text-align: center; vertical-align: middle; }
.pf-address { font-size: 7pt; color: #616161; }
.pf-contact { font-size: 6.5pt; color: #9e9e9e; }

/* ── Document title banner ─────────────────────────────── */
.doc-banner {
    background: #1a3560;
    color: #ffffff;
    padding: 14px 16px;
    border-radius: 4px;
    margin-bottom: 16px;
}
.doc-banner table { width: 100%; border-collapse: collapse; }
.db-title  { font-size: 14pt; font-weight: bold; }
.db-sub    { font-size: 7.5pt; color: #b0c4d8; margin-top: 2px; }
.db-right  { text-align: right; vertical-align: bottom; }
.db-code   { font-size: 11pt; font-weight: bold; }
.db-date   { font-size: 7.5pt; color: #b0c4d8; margin-top: 2px; }

/* ── Info section ──────────────────────────────────────── */
.info-section { margin-bottom: 14px; }
.info-section h3 {
    font-size: 8pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #546e7a;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 4px;
    margin-bottom: 8px;
}
.info-grid { width: 100%; border-collapse: collapse; }
.info-grid td { font-size: 8.5pt; padding: 3px 0; vertical-align: top; }
.info-label { color: #757575; width: 38%; }
.info-value { font-weight: bold; color: #212121; }

/* ── Two-column layout ─────────────────────────────────── */
.two-col { width: 100%; border-collapse: collapse; }
.two-col td { width: 50%; vertical-align: top; padding-right: 12px; }
.two-col td:last-child { padding-right: 0; padding-left: 12px; }

/* ── Data tables ───────────────────────────────────────── */
.data-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
.data-table thead th {
    background: #01AFEE;
    color: #ffffff;
    font-size: 8pt;
    font-weight: bold;
    padding: 6px 8px;
    text-align: left;
}
.data-table thead th.text-right  { text-align: right; }
.data-table thead th.text-center { text-align: center; }
.data-table tbody tr:nth-child(even) { background: #f8f9fa; }
.data-table tbody td {
    padding: 5px 8px;
    font-size: 8.5pt;
    border-bottom: 1px solid #e9ecef;
    vertical-align: top;
}
.data-table tbody td.text-right  { text-align: right; }
.data-table tbody td.text-center { text-align: center; }
.data-table tfoot td {
    padding: 7px 8px;
    font-size: 9pt;
    font-weight: bold;
    border-top: 2px solid #01AFEE;
    background: #e6f7fd;
}
.data-table tfoot td.text-right { text-align: right; }

/* ── Status badges ─────────────────────────────────────── */
.badge           { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }
.badge-success   { background: #d4edda; color: #155724; }
.badge-warning   { background: #fff3cd; color: #856404; }
.badge-danger    { background: #f8d7da; color: #721c24; }
.badge-info      { background: #d1ecf1; color: #0c5460; }
.badge-primary   { background: #cce5ff; color: #004085; }
.badge-secondary { background: #e2e3e5; color: #383d41; }

/* ── Summary / totals box ──────────────────────────────── */
.summary-box {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 10px 12px;
    margin-top: 12px;
    background: #f8faff;
}
.summary-box table { width: 100%; border-collapse: collapse; }
.summary-box td { font-size: 8.5pt; padding: 3px 0; }
.summary-row-total td { font-size: 11pt; font-weight: bold; color: #1a3560; border-top: 1px solid #c8d6f0; padding-top: 6px; margin-top: 4px; }

/* ── Utilities ─────────────────────────────────────────── */
.text-right  { text-align: right; }
.text-center { text-align: center; }
.text-muted  { color: #757575; }
.fw-bold     { font-weight: bold; }
.fs-italic   { font-style: italic; }
.no-data     { text-align: center; padding: 20px; color: #9e9e9e; font-style: italic; }
.divider     { border: none; border-top: 1px solid #e0e0e0; margin: 12px 0; }
</style>

{{-- PHP canvas script: draws "Page X of Y" on every page via DomPDF's page_text API --}}
<script type="text/php">
if (isset($pdf)) {
    $font  = $fontMetrics->get_font('DejaVu Sans', 'normal');
    $size  = 7;
    $color = [0.00, 0.69, 0.93];
    $w     = $pdf->get_width();
    $h     = $pdf->get_height();
    $pdf->page_text($w - 90, $h - 28, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, $size, $color);
}
</script>
