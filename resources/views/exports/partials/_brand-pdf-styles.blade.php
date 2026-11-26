{{-- Brand PDF base styles (docs/DESIGN.md §5). dompdf-safe: tables, floats and block boxes only;
     no flexbox, grid or CSS variables. Colours mirror the web tokens in public/assets/css/brand.css.
     Include inside <style> … </style>, then @include the _brand-header and _brand-footer partials
     right after <body>, and opt in to page numbers with <meta name="pdf-page-numbers" content="1">. --}}
@page { size: A4 portrait; margin: 27mm 15mm 22mm 15mm; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9pt;
    line-height: 1.45;
    color: #212121;
}

/* Running header (every page): logo row, one details line, brand rule. Height ≈ 15mm,
   inside the 27mm top margin. */
.bh {
    position: fixed;
    top: -21mm;
    left: 0; right: 0;
}
.bh table { width: 100%; border-collapse: collapse; }
.bh td { vertical-align: bottom; }
.bh-logo img { height: 7mm; width: auto; }
.bh-company-name { font-size: 10pt; font-weight: bold; color: #171717; }
.bh-doc { text-align: right; width: 60mm; }
.bh-doc-title { font-size: 7.5pt; font-weight: bold; color: #00699A; text-transform: uppercase; letter-spacing: 0.4px; }
.bh-doc-number { font-size: 9.5pt; font-weight: bold; color: #171717; }
.bh-company-details { font-size: 7pt; color: #5B6470; margin-top: 1.5mm; white-space: nowrap; overflow: hidden; }
.bh-rule { border-bottom: 1.5pt solid #01AEEF; margin-top: 1.5mm; }

/* Running footer (every page). The right-hand 32mm stays empty: PdfPageNumberService draws
   "Page N of M" there (28pt above the bottom edge). */
.bf {
    position: fixed;
    bottom: -16mm;
    left: 0; right: 0;
    height: 12mm;
    border-top: 0.75pt solid #D5DAE0;
    padding-top: 1.5mm;
}
.bf table { width: 100%; border-collapse: collapse; }
.bf td { vertical-align: top; font-size: 7pt; color: #5B6470; line-height: 1.35; }
.bf-company { font-weight: bold; color: #2B3137; }
.bf-pages { width: 32mm; }

/* Keep blocks and headings together */
.no-break { page-break-inside: avoid; }
.keep-with-next { page-break-after: avoid; }
.page-break { page-break-after: always; }

/* Chips: text + colour, readable in grayscale (dark text on a light tint, with a border) */
.chip {
    display: inline-block;
    padding: 1.5pt 6pt;
    border-radius: 7pt;
    border: 0.75pt solid #D5DAE0;
    font-size: 7.5pt;
    font-weight: bold;
    line-height: 1.3;
    white-space: nowrap;
}
.chip-pass, .chip-complete { background: #E3F4EA; color: #17643A; border-color: #8CCBA4; }
.chip-fail              { background: #FDE8E7; color: #A32019; border-color: #EE9C95; }
.chip-conditional       { background: #FFF1D6; color: #7A4A00; border-color: #E2B866; }
.chip-reinspection      { background: #EFE7FA; color: #5B2E91; border-color: #BBA2E0; }
.chip-pending, .chip-na { background: #ECEFF2; color: #4A525C; border-color: #C3CAD2; }
.chip-critical          { background: #FDE8E7; color: #A32019; border-color: #EE9C95; }
.chip-major             { background: #FFEBDC; color: #8F3B00; border-color: #F0B17F; }
.chip-minor             { background: #E5EEFC; color: #1849A9; border-color: #9DB8EE; }
.chip-functional        { background: #ECEFF2; color: #4A525C; border-color: #C3CAD2; }
