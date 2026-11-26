# Design system — TradeSyncERP

The one place for the look of the web app and the PDFs. Stage 1 (2026-11-26): tokens, app shell, two reference screens (dashboard, inspections list), run-edit mobile fixes, and the inspection-run PDF. Stage 2 applies it to the other screens and PDFs.

- **Web:** Bootstrap 5.2 + the bundled Duralux theme (`public/assets/css/bootstrap.min.css`, `theme.min.css`). The brand layer is **`public/assets/css/brand.css`**, loaded last in `resources/views/index.blade.php`.
- **PDF:** dompdf 3. Shared brand partials are in `resources/views/exports/partials/_brand-*.blade.php`.
- No CSS framework was added, no build step, no remote fonts, no CDN assets.

---

## 1. Brand source: the logo

`public/assets/images/logo-pdf.png` (computed with Pillow, opaque pixels):

| Property | Value |
|---|---|
| Size | 4534 × 352 px, RGBA |
| Transparency | Has an alpha channel, but **every pixel is opaque** (white background) |
| Dominant | white `#FFFFFF` 73%, near-black `#171717` 8.4% (the "TRADE SOLUTIONS" lettering) |
| Accent | cyan `#01AEEF` (`#02ADED`/`#01AEF1`, ~4%: "Sourcing & Inspection Co." and the mark) |
| Neutrals | greys `#787778`, `#A7A7A8` (the triangle mark) |

**Other logo files:**
- `logo-trade.png` (1696 × 352, the same artwork): used by the sidebar, the topbar on narrow screens, and the old run-PDF cover.
- `logo-abbr.png` (512², the theme's blue "D" placeholder, **not the brand**): used by the login page and the collapsed sidebar (Q43).
- `logo-full.png` (140 × 30): unused.
- `favicon.ico`.
- Company Settings can upload a logo (`company_settings.logo_path`). The shared PDF partials prefer it over `logo-pdf.png`. Neither database has one uploaded today.

## 2. Tokens (`brand.css` §1; Bootstrap variables mapped in §2)

Contrast ratios are WCAG 2.x, computed (script output in the Stage 1 report). Text needs at least 4.5:1, non-text 3:1.

| Token | Hex | Use | Contrast |
|---|---|---|---|
| `--ts-accent` | `#01AEEF` | logo cyan: rules, active-nav bar, PDF header rule. **Never text.** | 2.53:1 on white (decorative only) |
| `--ts-primary` | `#00699A` | buttons, links, focus ring, PDF accents | 6.02:1 white-on-primary and primary-on-white |
| `--ts-primary-dark` | `#004E73` | hover / active | 8.98:1 with white |
| `--ts-primary-subtle` / `--ts-on-primary-subtle` | `#E1F4FC` / `#00557D` | active nav item, soft badges | 7.15:1 |
| `--ts-ink` | `#171717` | headings (logo black) | 17.93:1 |
| `--ts-body` | `#2B3137` | body text | 13.15:1 on white, 12.14:1 on page |
| `--ts-muted` | `#5B6470` | secondary text, table headers | 6.00:1 white, 5.54:1 page, 5.29:1 surface-2 |
| `--ts-page` / `--ts-surface` / `--ts-surface-2` | `#F4F6F8` / `#FFFFFF` / `#EEF1F4` | backgrounds | — |
| `--ts-border` / `--ts-border-strong` | `#D5DAE0` / `#8A939E` | dividers / form controls | strong 3.11:1 (non-text) |
| success | text `#17643A` on `#E3F4EA`; solid `#1D7A46` | | 6.30:1 / 5.35:1 |
| danger | text `#A32019` on `#FDE8E7`; solid `#B42318` | | 6.43:1 / 6.57:1 |
| warning | text `#7A4A00` on `#FFF1D6`; solid `#9A5B00` | | 6.70:1 / 5.43:1 |
| info | text `#0B5B85` on `#E1F1FA` | | 6.37:1 |

**Verdicts and statuses**, always text plus colour:

| Status | Chip | Contrast |
|---|---|---|
| Pass | green | 6.30:1 |
| Fail | red | 6.43:1 |
| Conditional | amber | 6.70:1 |
| Re-Inspection | violet `#5B2E91` on `#EFE7FA` | 7.79:1 |
| Pending / N/A | grey `#4A525C` on `#ECEFF2` | 6.86:1 |

**Severity**, always text plus colour:

| Severity | Colours | Contrast |
|---|---|---|
| Critical | `#A32019` on `#FDE8E7` | 6.43:1 |
| Major | `#8F3B00` on `#FFEBDC` | 6.50:1 |
| Minor | `#1849A9` on `#E5EEFC` | 7.01:1 |

**Replaced because they failed:**
- theme body text `#6b7885` on `#f3f4f6` (4.10:1);
- `.text-success` `#17c666` and `.text-warning` `#ffa21d` on white;
- white text on the theme's green, amber and cyan buttons;
- the PDF page number in `#00B0ED` (2.49:1, now `#00699A`).

### 2.1 Dark mode (the theme's toggle; `html.app-skin-dark`)

The same tokens get dark values in `brand.css` §2b, matched to the theme's dark surfaces:
- page `#121A2D`, cards `#0F172A`, table headers `#1C2438`;
- text: body `#C9CDD6`, muted `#A3A9B6`, headings white;
- links use `--ts-primary-text` `#5CC8F5`, because the button blue `#00699A` is only 2.96:1 as text on dark;
- chips and status text switch to dark tints with light text.

Every component that reads the tokens follows automatically. Computed contrast: body 11.21:1, muted 7.57:1 (6.56:1 on table headers), links 9.38:1, chips 7.38–8.45:1.

The theme's dark rules re-colour some utilities with higher-specificity `!important` selectors: `.text-*`, `.bg-*`, `.btn-danger` / `-success` / `-warning` / `-info` and `.page-link`. `brand.css` repeats those selectors (with the `html.app-skin-dark` prefix where needed), so the tokens stay in charge.

**Audit (2026-11-26, headless Chrome on `trade_scratch`):** I measured every visible text element against the background behind it on 26 pages (lists, forms, show pages, the run edit page, ledgers, admin), in both skins, at 1,680 px and 375 px. The result: **0 elements below 4.5:1** (3:1 for large text).

**Typography:**
- system stack `system-ui, -apple-system, "Segoe UI", Roboto, …` (`--ts-font-sans`);
- sizes 12 / 13 / 14 (body) / 16 / 20 px, line height 1.5;
- table numbers use `font-variant-numeric: tabular-nums`.

**Other scales:**
- spacing: 4 px steps (`--ts-space-1…6`);
- radii: 4 / 6 / 10 px / pill;
- three shadows;
- z-index layers: sticky 1020, header 1025, sidebar 1030, overlay 1035, dropdown 1060, modal 1065, toast 1090;
- touch target: 44 px.

**`!important`** appears only where the theme's own utility is `!important`:
- `.text-*` and `.bg-soft-*`;
- `.text-muted`;
- two responsive overrides of `gap-4` / `ms-auto`;
- the sticky action bar's shadow.

## 3. Components (`brand.css` §4), defined once

- **Buttons:** primary, outline-primary, secondary (`btn-light-brand` / `btn-outline-secondary`), danger, success, warning and info, all mapped through `--bs-btn-*` variables. 44 px minimum on touch screens.
- **Cards:** 1 px border, 10 px radius, small shadow, ink-coloured titles.
- **Tables:** grey uppercase header, hover tint, light zebra; `.ts-table-sticky` gives a sticky header; `.table-responsive` scrolls horizontally on phones.
- **Forms:** bold label in ink, help text in muted, error in danger text, strong control border; focus is a 3 px primary outline.
- **Status chips:** `<x-status-chip :status="…"/>` (`resources/views/components/status-chip.blade.php`), or `kind="severity"`. Classes `ts-chip-{pass|fail|conditional|reinspection|pending|critical|major|minor|…}`.
- **Alerts, modals, pagination:** token colours.
- **Empty state:** `.ts-empty` with `.ts-empty-title`.
- **Page header:** title, breadcrumb (a single scrollable line on phones), primary action. Wraps under 768 px.
- **Flash and error messages:** `partials/flash-messages` (unchanged markup, token colours).

## 4. App shell

**Sidebar** (`resources/views/partials/sidebar.blade.php`):
- One data array: groups → links → optional children. Each link names the **same permission its route's controller middleware requires** (checked against `route:list`). Administration uses `role:Admin`, as its routes do.
- A link shows only if the user has that permission. A group shows only if it has a visible link.
- Hiding is cosmetic: every server-side check is unchanged.
- Groups:
  - **Overview:** Dashboard, Recent Activities
  - **Operations:** Customer Orders, Samples, Sample Movements
  - **Quality control:** Inspections, Inspection Types, Inspection Sections, Defects, AQL Calculator, QC Image Test
  - **Finance:** Customer Invoices, Customer Payments, Expenses, Purchase Orders, Ledgers, Accounts, Banks, Expense Heads, Currencies
  - **HR & payroll:** Employees, Salary Runs, Allowance Types
  - **Master data:** Customers, Suppliers, Categories, Colors, Sizes
  - **Administration:** User Management (Users, Roles & Permissions), Company Settings
- **Behaviour** (`public/assets/js/sidebar-nav.js`):
  - Group headers are buttons with `aria-expanded`. The collapsed state is remembered in this browser (`localStorage` key `ts.nav.collapsed`), and the group with the current page always opens.
  - The active link has `aria-current="page"` and a cyan bar. A parent with an active child is highlighted.
  - The icon-only mode on wide screens is the theme's own (`#menu-mini-button`). Groups never hide links there.
  - Below 1024 px the menu is off-canvas (the theme's `#mobile-collapse`). Escape closes it and returns focus to the toggle, a tap on the overlay closes it, focus moves into the menu when it opens, and Tab stays inside while it is open.

**Topbar** (`partials/header.blade.php`):
- toggles, full screen, dark mode, the activity bell and the user menu, all with accessible names;
- the company logo appears while the sidebar is off-canvas;
- the bell shows only with `activities.view` (its feed was already gated);
- the "mega menu" toggle was removed, because it had no menu to open.

**Layout** (`index.blade.php`): `brand.css` loaded last, a "Skip to main content" link, `<main id="main-content">`. The IE-only CDN scripts were removed.

**Page pattern:** page header (title, breadcrumb, primary action), then `.main-content` with cards, then flash messages from `partials/flash-messages`.

## 5. PDF

**Partials** (dompdf-safe: tables and block boxes; no flexbox, grid or CSS variables; colours copied from the tokens):

| Partial | What |
|---|---|
| `_brand-pdf-styles` | `@page` (A4, margins 27/15/22/15 mm), body 9 pt DejaVu Sans, running header and footer, chips (verdict, severity, status), `no-break` / `keep-with-next` |
| `_brand-header` | every page: logo (Company Settings logo, else `logo-pdf.png`, by **filesystem path**: the `_pdf-company-header` mechanism), `$docTitle` and `$docNumber`, one line of company details, a 1.5 pt cyan rule |
| `_brand-footer` | every page: company line, `$footerNote`, "Generated …". The right 32 mm stay empty for **PdfPageNumberService** ("Page N of M", opt-in via `<meta name="pdf-page-numbers" content="1">`) |

**Usage:** `@include('exports.partials._brand-pdf-styles')` inside `<style>`, then the header and footer right after `<body>`. Read `CompanySetting::current()` once into `$companySetting` before the includes.

**Rules:**
- Reset with `body, body *`, never `*`. A bare `*` also resets dompdf's page box: the old run PDF's fixed header and footer never printed because of it.
- Light tints with dark text and a border, never white text on dark fills (print and grayscale safe).
- Body text 9 pt minimum; labels and chips 7–7.5 pt.
- Table headers repeat on each page (`thead { display: table-header-group }`); rows and label/value rows don't split (`page-break-inside: avoid`); headings use `page-break-after: avoid`.
- Photos sit in fixed 3-column tables (`table-layout: fixed`), with images capped at 150 px high and 100% of the cell. The caption is the row label plus a number, never the file name.

**Applied in Stage 1:** `exports/inspection-run-pdf.blade.php`:
- cover: title block, a details table, one decision box with the inspector's assessment line (Q15 wording unchanged), and a summary strip (sections complete, defects by severity, AQL result);
- per-severity sampling-basis table;
- defect table with photo thumbnails (each photo is still embedded once);
- restyled section blocks and the final decision card.

**Other templates:** the other 32 PDFs still use `_pdf-head` + `_pdf-company-header` + `_pdf-company-footer` (Stage 2).

## 6. Vendor CSS: the one-time token substitution

`bootstrap.min.css` and `theme.min.css` hard-coded the template's primary blue. They now reference the tokens, each with a literal fallback. Substitutions:

| Old value | Becomes |
|---|---|
| `#3454d1` | `var(--ts-primary,#00699a)` |
| `#2a43a7`, `#2c47b2`, `#273f9d` | `var(--ts-primary-dark,#004e73)` |
| `#1f327d`, `#192864` | `var(--ts-primary-darker,#003a56)` |
| `#d6ddf6`, `#e3e7f9`, `#dae0f7`, `#ebeefa` | `var(--ts-primary-subtle,#e1f4fc)` |
| `#c2ccf1`, `#c6cff2` | `var(--ts-primary-border,#b3e0f5)` |
| `rgba(52,84,209,…` | `rgba(var(--ts-primary-rgb,0,105,154),…` |
| `--bs-primary-rgb:52,84,209` | the primary-rgb token |
| `--bs-btn-focus-shadow-rgb:82,110,216` | the primary-rgb token |
| `font-family:Inter,sans-serif` | `var(--ts-font-sans,…)` |

**Removed:** the runtime `@import url(https://fonts.googleapis.com/…)` in `theme.min.css`.

**Counts:**
- `bootstrap.min.css`: 14 + 5 + 6 + 2 + 1 + 1 + 1 + 1.
- `theme.min.css`: 88 + 5 + 2 + 8 + 3, plus the import.
- A second run changes nothing (idempotent).

The `.map` files are now stale, and `vendors.min.css` (select2, date pickers) still has the old blue (Stage 2).
