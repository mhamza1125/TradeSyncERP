CONFIDENTIAL - do not commit or deploy

# QC enhancement proposal: TradeSyncERP

Advisory, stage 1. Nothing here is built. It extends what exists, with no redesign. References: `docs/QC_STANDARDS_REFERENCE.md` (sources S1–S34), `docs/QC_RULES.md`, `docs/AUDIT_2026-11-25.md`, `docs/STATUS.md`.

**Stabilisation status (VERIFIED).** The fixes from 2026-11-25 are present in the working tree but **not committed**. On 2026-11-25 the full suite on `trade_test` passed 344 tests at the inspection run review and 396 after its fixes (`docs/STATUS.md`). Q11–Q16 are open, and F-05 (override reason) is blocked on Q15.

**Labels.**
- **VERIFIED** means the code was read or run, or the database was queried.
- **READ-ONLY** means it was inferred from code.
- **SOURCE** means it comes from a cited document.
- **HYPOTHESIS** means it is an inference.

All database figures are aggregate counts from `trade_prod` (a copy of production), read with SELECT only on 2026-11-25.

---

## 1. Business profile (Part 1)

### 1.1 Volumes (VERIFIED, database: trade_prod)

| Item | Count | Note |
|---|---|---|
| Customers | 4 (all active) | invoiced in EUR (3 invoices), GBP (1) and USD (1) |
| Suppliers | 33 | |
| Samples (styles) | 4 | |
| Inspections | 6 | created 2026-08-10 to 2026-09-10 |
| Runs | 7 (3 finished) | 5 inspections with 1 run, 1 with 2 runs |
| Run AQL records | 3 | all placeholders (lot size 0) |
| AQL calculator records | 1 | level II, AQL 2.5 / 2.5 / 2.5, lot 500 |
| Defect library | 192 (all active) | critical 7, major 118, minor 67 |
| Recorded run defects | 4 rows | major 2 rows (qty 6), minor 2 rows (qty 4) |
| Section library | 40 | 34 mandatory, 6 skippable, 0 optional |
| Run sections | 73 | 50 pending, 22 complete, 1 skipped |
| Run evidence photos/files | 72 | |
| Employees | 11 | Quality Control: 5 (QC Associate 2, QC Auditor 1, Senior QC Auditor 2) |
| Login users | 4 | |
| Inspection–inspector links | 13 (all 6 inspections have inspectors) | |
| Customer orders | 2 | linked to 2 inspections |
| Activity log entries | 563 | |

**Conclusion (HYPOTHESIS).** The data is a **pilot or early-use dataset** (one month, 6 inspections). Typical lot sizes, AQL levels in use, override rates and re-inspection rates **cannot be estimated** from it: the answer is "unknown", not zero.

### 1.2 Sector, inspection types and usage (VERIFIED)
- **Sector: motorcycle protective apparel and accessories.** Product names are a touring jacket, touring boots, racing gloves and a helmet buckle set. Categories are Garments (jackets/pants), Boots, Gloves and Accessories.
- **Inspection types defined:** 10. Those used:
  - Inline (DUPRO): 3
  - Final (AQL): 2
  - Final (100%): 1

  Not used yet: PPS, SMS, CLI, Packing Evaluation, Re-Inspection, Final (% based), Final (AQL / %).
- **AQL and inspection levels actually used:** only the default seeds (critical 0.065, major 2.5, minor 4.0, level II) on runs. The calculator used II and 2.5 / 2.5 / 2.5.
- **Severity usage:** 4 recorded defect rows, all major or minor; no critical defects recorded.
- **Verdict override:**
  - Finished runs where the AQL verdict differs from the run verdict: 0 of 3.
  - Finished runs where **Overall Article Result** (the inspector's choice) differs from the stored run verdict: **1 of 3**. This is the Q15 issue: the inspector's choice is not used.
- **Re-done runs:** 1 inspection has 2 runs on the same sample and colour (a repeat run). There is no "re-inspection of" link.
- **Inspectors:** `Employee` records in the QC department, attached to inspections (not to runs), and **not linked to login users** (READ-ONLY: `employee_inspection` pivot; no `user_id` on employees).
- **Reports to external customers:** yes, inferred.
  - The PDF is company-branded (logo, company footer).
  - Customer orders are printed on it.
  - Invoice lines bill customers per **supplier + inspection type + PO/invoice number** (VERIFIED: `customer_invoice_items` columns).

### 1.3 Third-party inspection body or in-house QC? (HYPOTHESIS, stated as assumption A-1)
The evidence points to a **third-party inspection / QC service provider working for buyers (brands), inspecting at suppliers' factories**:
- **Billing.** Customers are invoiced for inspections performed at **suppliers**, in the customers' currencies (VERIFIED).
- **Service catalogue.** The inspection types are the standard third-party menu: PPS, DUPRO, FRI with AQL, CLI (VERIFIED).
- **Many suppliers.** There are 33 suppliers against 4 customers (VERIFIED).
- **Buyer-approved reference samples.** Samples carry seals: Gold, Red and Silver Seal (VERIFIED).
- **External report.** The run PDF is a customer-facing report (VERIFIED).

**If wrong** (that is, the business is an in-house QC function of a manufacturer or importer):
- ISO/IEC 17020 drops out.
- Customer-facing report governance (§d) becomes lower priority.
- Supplier scorecards, NCR/CAPA (§e) and switching rules per supplier (§a) gain value.

See Q17.

---

## 2. Gap map: requirement or practice versus current code

| # | Requirement / practice (source) | State | Evidence |
|---|---|---|---|
| G1 | Correct AQL normal single plans, arrows, 100% cap (S3/S7/legend) | **PRESENT** | `AqlCalculationService`, 195 unit tests, `docs/AQL_SOURCES.md` (VERIFIED) |
| G2 | Tables aligned with ISO 2859-1:**2026** (S1, S3) | **ABSENT / unknown** | Tables are 1999-era; S4 and S5 disagree on whether the 2026 tables changed |
| G3 | Inspection level and code letter recorded and printed | PARTIAL | Stored on `inspection_run_aql` (level, code letter, n, Ac/Re). **Per-severity n is not stored** (only the overall n). The PDF prints the stored values (READ-ONLY). |
| G4 | Tightened/reduced plans and switching per supplier+product (S3 intro, clause 9) | **ABSENT** | No severity state, no lot history (VERIFIED) |
| G5 | Classification of nonconformities (S3 4.2; practice S25) | PARTIAL | Severity per defect in the library and per row (`inspection_run_defects.severity`). **No written definitions per category.** |
| G6 | One source of truth for defect counts | **ABSENT** | AQL `found_*` counts are typed in separately from defect rows (audit F-05) |
| G7 | Report traceable to the inspector (17020 7.3.2) | PARTIAL | Inspectors are on the inspection, not the run; inspectors are not users |
| G8 | Approval/signature by authorized personnel on the report (17020 7.4.2 e; 9001 8.6) | **ABSENT** | No approver, no sign-off record |
| G9 | Unique report ID and issue date (17020 7.4.2 b) | PARTIAL | `report_number` is unique but generated as max(id)+1 (F-19); **no issue date** |
| G10 | Amended report identifies the replaced one (17020 7.4.5) | **ABSENT** | No revisions; the PDF is regenerated live from current data |
| G11 | Records amendments trackable, original kept (17020:2026, S14) | PARTIAL | Activity log on run models (key fields, since 2026-11-25); **section data JSON not versioned; photo crop replaces the original file** (READ-ONLY: `updateAttachmentImage` deletes the old path) |
| G12 | Immutable finished records (A4) | PRESENT | Run lock + reopen (VERIFIED by tests) |
| G13 | No hard deletes of inspection records | PARTIAL | Delete is blocked when finished runs exist; otherwise hard delete; no soft deletes |
| G14 | Nonconforming output control and corrective action (9001 8.7, 10.2; 2859 7.2) | **ABSENT** | Only per-defect status and disposition codes (MACDF/MACSO/MACDE) |
| G15 | Item identification: lot, cartons drawn (17020 7.2.1; practice S26) | PARTIAL | Carton number per defect row; no lot or carton-range record |
| G16 | Measurements with specification and tolerance | **ABSENT** | `_measurement_check` stores only label + result (VERIFIED) |
| G17 | Equipment and calibration (17020 6.2; 9001 7.1.5) | **ABSENT** | None (only relevant if Q19 says so) |
| G18 | Data-system validation and change control (17020:2026, S14) | PARTIAL | Test suite, AQL source verification; no release or change log |
| G19 | Inspector competence and authorization records (17020 6.1.5/6.1.10) | **ABSENT** | |
| G20 | Complaints and appeals (17020 7.5) | **ABSENT** | |
| G21 | KPIs with definitions (9001 9.1) | **ABSENT** | No QC KPIs; dashboard is operational |
| G22 | Photo privacy (EXIF/GPS) | **ABSENT** | Originals kept with EXIF (audit F-26) |

---

## 3. Proposals by area

Format for each area:
- **What:** the change.
- **Why:** the reason.
- **Alt:** the alternatives.
- **Trade-off.**
- **Not when:** when it would not apply.
- **Effort / Value.**
- **Deps:** dependencies.
- **Data risk:** risk to existing data.
- **Tests:** the tests required.

Class is **MUST** (needed to support the standard's method correctly), **SHOULD** or **OPTIONAL**.

### a. AQL engine completeness

**a1. Record the per-severity plan on every run: MUST.**
- **What:** store n and code letter per severity. Arrows can give different sample sizes per severity, and today only the overall n is stored.
- **What (report):** print a "Sampling basis" block on the report:
  - standard and edition;
  - inspection level;
  - severity state (normal, tightened or reduced);
  - per severity: AQL, code letter, n, Ac and Re.
- **Why:** the report must show the plan actually applied (S3 clause 10; 17020 7.4.2 g).
- **Alt:** keep the plan in the variations JSON. **Trade-off:** JSON can't be queried for KPIs.
- **Effort / Value:** S / high.
- **Deps:** none.
- **Data risk:** none (additive, nullable).
- **Tests:** a saved run stores the 3 per-severity n values; the PDF shows the block.
- **SQL idea:** `ALTER TABLE inspection_run_aql ADD sample_size_critical SMALLINT UNSIGNED NULL, ADD sample_size_major …, ADD sample_size_minor …, ADD code_letter_critical VARCHAR(2) NULL …, ADD standard_ref VARCHAR(40) NULL, ADD severity_state ENUM('normal','tightened','reduced') NOT NULL DEFAULT 'normal';`

**a2. Check the tables against ISO 2859-1:2026: MUST before claiming 2026 alignment.**
- **What:** buy the 2026 text, compare clauses 15–16 cell by cell, and update `docs/AQL_SOURCES.md`. If they are unchanged, change the label to "ISO 2859-1:2026".
- **Effort:** S (needs the purchased PDF).
- **Not when:** the customers contract on Z1.4-2003 (R2018).

**a3. Tightened and reduced tables plus switching-rule tracking: SHOULD, only if Q17 says the same buyer inspects the same supplier and product repeatedly.**
- **What (history):** add an `aql_switching_states` table, one row per (customer, supplier, product key, AQL set). It holds the current state, consecutive-accepted count, recent-results window, switching score, and `discontinued_at`.
- **What (log):** add an append-only `aql_switching_events` table (from, to, reason, run id, user, time).
- **What (on finish):** finishing a run with an AQL record appends its lot result and evaluates clause 9.3 rules from the **purchased text**.
- **What (overrides):** a manual override of the state needs a new permission and a reason.
- **Why:** S3: the scheme is "intended for application to a continuing series of lots … applied to each [producer/process] separately". Without switching, only the normal plan is used, which the standard does not intend for continuing series.
- **Alt:** normal-only, with a printed disclaimer ("switching rules not applied"). This is cheap and honest.
- **Trade-off:** needs a reliable product key (style + supplier) and disciplined data. The thresholds must come from the licensed text, because public sources are low-confidence (reference §1.2).
- **Not when:** inspections are one-off (isolated lots, ISO 2859-2) or the buyer specifies normal only.
- **Effort / Value:** L / medium-high for repeat programmes.
- **Deps:** a1, a2, f1 (lot identity).
- **Data risk:** none (new tables); history starts empty.
- **Tests:** a state machine per transition, with fixtures for each 9.3.x rule; discontinuation blocks new runs for that key; override needs the permission and a reason; tables II-B and II-C cell tests from two sources.

**a4. Special levels S-1..S-4: PRESENT.** Already supported; nothing to do.

### b. Defect governance

**b1. Derive AQL found counts from recorded defect rows: MUST.**
- **What:** `found_critical/major/minor` = sum of `qty` of defect rows by severity, for the run's defect sections.
- **What (manual entry):** keep manual entry only when there are no defect rows, and show a visible "manual counts" flag.
- **What (disagreement):** if both exist and disagree, block finishing.
- **Why:** one source of truth (audit F-05); accuracy (ALCOA).
- **Alt:** a validation-only check (warn on mismatch).
- **Effort / Value:** M / high.
- **Deps:** Q15 (verdict source).
- **Data risk:** historic AQL records are unchanged (only new saves derive).
- **Tests:** rows → counts; mismatch blocks finish; the manual flag is shown on the PDF.

**b2. Severity definitions per product category: SHOULD.**
- **What:** add a `defect_severity_definitions` table (category_id nullable, severity, definition text, examples), printed as an appendix on the report.
- **Why:** ISO 2859-1 4.2 classification (S3); industry practice (S25).
- **Effort / Value:** S / medium.
- **Data risk:** none.

**b3. Evidence photos per defect: PRESENT** (photos keyed by `task_key` per defect row, READ-ONLY). Possible addition: require at least one photo for critical and major rows at finish, as an **OPTIONAL** setting.

**b4. Defect library hygiene: OPTIONAL.**
- **What:** 192 entries (VERIFIED). Add a category scope and an "in use" count, and block deleting defects that are in use (it may be blocked already; check).

### c. Verdict, override and release

**c1. Single documented rule set: PRESENT** (`docs/QC_RULES.md`). **Blocked:** the Q15 field question.

**c2. Override with a reason (A2): MUST.**
- **What:** this is slice 8, waiting on Q15. Mandatory reason; logged; printed.
- **Effort:** S once Q15 is answered.

**c3. Second-person approval and release sign-off: SHOULD (MUST if 17020 applies).**
- **What (permissions):** add `inspections.approve` and `inspections.release`.
- **What (statuses):** a finished run moves through **Finished → Reviewed → Released**. Only Released reports carry a report issue number and date.
- **What (who can sign):** the approver can't be the run's inspector or the person who overrode the verdict (separation of duties).
- **What (sign-off record):** add an `inspection_signoffs` table (inspection_id, run_id nullable, role ENUM('reviewer','approver'), user_id, signed_at, statement_version, ip). It is append-only.
- **What (PDF wording):** "Approved by <name> on <date>". The PDF **must not** call this a legal electronic signature (that depends on jurisdiction).
- **Why:** 17020 7.4.2(e) "signature or other indication of approval by authorized personnel"; ISO 9001 8.6 release records (S13, S20).
- **Alt:** a single "approved by" field on the inspection. It is simpler but gives no history.
- **Trade-off:** adds a step to the workflow, and small teams may find it slow.
- **Not when:** in-house QC with no external report.
- **Effort / Value:** M / high.
- **Deps:** j1 (inspectors are users), d1.
- **Data risk:** none; existing finished runs stay unreleased until signed.
- **Tests:** self-approval is refused; the approver needs the permission; sign-off is immutable; the PDF shows "DRAFT" until released.

### d. Report issuance and revision

**d1. Issued report snapshot and revisions: MUST if reports go to customers (A-1).**
- **What (issuing):** add `inspection_reports` (id, inspection_id, report_no UNIQUE, revision SMALLINT, issued_at, issued_by, approved_by, scope_text, sampling_basis_text, limitations_text, pdf_path, sha256, supersedes_id FK nullable, status ENUM('issued','superseded','withdrawn')). Issuing **renders the PDF once, stores it (private disk), and hashes it**.
- **What (reprints):** reprints serve the stored file. "Amend" creates revision n+1, which marks the previous one superseded. The new PDF prints "Supersedes <report_no> rev n-1" (17020 7.4.5).
- **What (header):** issuing body, unique ID, issue date, inspection date(s), location, items, approval, conformity statement, results, inspector(s), sampling basis, limitations statement.
- **Why:** today the PDF is regenerated live, so a reprint can differ from what the customer received (audit; G10).
- **Alt:** store only a hash of a regenerated PDF (fragile, because the template changes).
- **Trade-off:** storage of about 1–5 MB per report (HYPOTHESIS), and template changes no longer alter old reports, which is intended.
- **Effort / Value:** M / high.
- **Deps:** c3, d2.
- **Data risk:** none (new table). Old reports get "rev 0" only when they are first issued.
- **Tests:** an issued PDF is byte-identical on reprint; an amendment creates a chain; a superseded report is watermarked; the report number is unique under concurrency.

**d2. Atomic numbering: SHOULD.**
- **What:** add a `number_sequences` table (name, year, next_value) and use `SELECT … FOR UPDATE` in a transaction. This also fixes F-19 for inspections and runs.
- **Effort:** S.
- **Data risk:** seed `next_value` from the current max.
- **Tests:** a parallel-insert test.

**d3. Soft deletes for inspections and runs: SHOULD.**
- **What:** add a `deleted_at` column, plus restore with a permission.
- **Data risk:** none (additive).
- **Tests:** deleted runs are hidden but kept; activity is logged; cascade rules are reviewed.

### e. Non-conformance and corrective action (lightweight): SHOULD

- **What (NCR table):** `ncrs`: id, ncr_no, inspection_run_id, supplier_id, customer_id, opened_from (verdict), disposition ENUM('rework','sort_100','accept_concession','reject_return','scrap','other'), description, root_cause, action, owner_user_id, due_date, verified_by, verified_at, closed_at, status ENUM('open','action','verification','closed','cancelled').
- **What (actions):** an `ncr_actions` table.
- **What (trigger):** an NCR is created from a run whose verdict is Fail, Conditional or Re-Inspection, prefilled with its defects.
- **What (re-inspection):** "Re-inspection" creates a new run linked to the NCR (see f3).
- **What (ageing):** shown on the dashboard.
- **Why:** ISO 9001 8.7/10.2 (S20); ISO 2859-1 7.2 disposition of non-acceptable lots (S3).
- **Alt:** use defect-row statuses only (today); no root cause or ownership.
- **Not when:** the buyer handles NCRs in their own system.
- **Effort / Value:** M / medium-high.
- **Deps:** k definitions.
- **Data risk:** none.
- **Tests:** an NCR is created only from a non-pass run; it can't close without verification; ageing buckets are correct.

### f. Traceability

**f1. Lot record per run: SHOULD.**
- **What:** add `inspection_runs` columns `lot_reference` VARCHAR, `total_cartons` INT, `cartons_opened_json` (list of carton numbers drawn), `shipment_ref`, and `container_no` VARCHAR NULL.
- **What (helper):** "suggest cartons", a √N random draw (S26), stored together with the seed.
- **Why:** 17020 7.2.1 unique identification; practice S26.
- **Effort / Value:** S–M / medium.
- **Data risk:** none.
- **Tests:** the draw is reproducible from the stored seed; it is printed on the report.

**f2. PO, style, colour and size linkage: PRESENT** (customer orders ↔ inspection; sample + colour per run; size breakdown).

**f3. Re-inspection chain: SHOULD.**
- **What:** add `inspection_runs.reinspection_of_run_id` FK NULL, printed as "Re-inspection of <report/run>". The KPI re-inspection rate uses it.
- **Effort:** S.
- **Data risk:** none.

### g. Measurements: SHOULD (sector: garment dimensions)

- **What (spec):** add `measurement_specs` (sample_id or style, size, point_code, description, nominal DECIMAL(10,3), tol_minus, tol_plus, unit ENUM('cm','mm','in','g','kg')). Graded size charts are imported by CSV.
- **What (inspection):** the run section records the measured value per point and size, and auto-flags out-of-tolerance values; the count feeds defect rows (configurable severity).
- **What (variables sampling):** ISO 3951 variables sampling is **OPTIONAL** and is not recommended now (reference §1.3).
- **Why:** G16; ISO 9001 7.1.5 if measurements decide conformity.
- **Effort / Value:** M / medium.
- **Deps:** none; if instruments matter, see h.
- **Data risk:** none (the existing JSON stays readable).
- **Tests:** tolerance edges (=, just outside); unit conversion off; printed table.

### h. Instruments and calibration: OPTIONAL (only if Q19 = yes)

- **What:** an `instruments` table (id, type, serial, range, calibration_due, certificate attachment). Each run section records the instrument used, and finishing is blocked if a used instrument is past due.
- **Why:** 17020 6.2.6–6.2.10 and 2026 equipment recording (S13, S14); 9001 7.1.5.
- **Effort:** S–M.

### i. Records, audit trail and retention

**i1. Version section data on change: SHOULD.**
- **What:** the run-section activity log holds status and notes only, so data changes are invisible. Store a **hash plus diff of changed keys** (not large JSON), or a `run_section_versions` table (run_section_id, version, data_json, changed_by, changed_at) **only on save after first completion**.
- **Why:** 17020:2026 tracked amendments (S14); ALCOA original (S24).
- **Effort / Value:** M / high if 17020 applies.

**i2. Keep the original photo when cropping: MUST for evidence integrity.**
- **What:** crop creates a new attachment version (`replaces_attachment_id`) and the original is kept, not deleted. This is a **current behaviour change** (READ-ONLY: `updateAttachmentImage` deletes the old file).
- **Effort:** S.
- **Data risk:** none for the future; past crops are already lost.

**i3. EXIF/GPS: SHOULD.**
- **What:** strip GPS on upload (Intervention is installed). Keep the capture timestamp in a column if useful, and keep the original only if policy requires (Q20).
- **Effort:** S.

**i4. Retention setting and export: SHOULD.**
- **What (setting):** add `company_settings.record_retention_years`.
- **What (export):** an inspection export bundle (issued PDFs + CSV of runs/defects + JSON) per inspection or per period.
- **What (purge):** no automatic deletion; a purge report only (data safety rule).
- **Effort:** M.

### j. Inspector competence: SHOULD if 17020 applies, otherwise OPTIONAL

- **What:** add `employees.user_id` FK NULL (UNIQUE), so inspectors log in as themselves.
- **What (authorization):** an `inspector_authorizations` table (employee_id, inspection_type_id, category_id NULL, authorized_by, from, to).
- **What (training):** an `inspector_trainings` table.
- **What (runs):** each run records the performing inspector (`inspection_runs.inspector_employee_id`).
- **Why:** 17020 6.1.5, 6.1.10, 7.3.2 (S13).
- **Effort / Value:** M / high for an inspection body.
- **Data risk:** none.
- **Tests:** an unauthorized inspector can't be assigned; the report shows the inspector.

### k. QC reporting and KPIs: SHOULD

Write the definitions **once**, in a new section of `docs/QC_RULES.md` (§7). Then build one `QcKpiService` and one page with a CSV export. CSV cells starting with `= + - @` must be escaped.

| KPI | Definition (proposed) | Needs |
|---|---|---|
| Pass rate | released runs with verdict Pass ÷ released runs, by month/customer/supplier/type | c3 (or finished runs until then) |
| First-pass yield | runs that are not re-inspections and Pass ÷ runs that are not re-inspections | f3 |
| Re-inspection rate | runs with `reinspection_of_run_id` ÷ all runs | f3 |
| Defect Pareto | Σ qty by defect code and severity | b1 |
| DHU | Σ defect qty ÷ Σ units inspected × 100 (S30) | b1, a1 (units inspected) |
| Supplier scorecard | pass rate, DHU, critical count, open NCRs per supplier | e |
| Override rate | runs with an override reason ÷ finished runs | c2 |
| Turnaround | report issued_at − inspection date (and − run completed_at) | d1 |
| Open NCR ageing | open NCRs by age bucket (0–7, 8–30, >30 days) | e |
| Inspector workload | runs per inspector per period | j |

**Effort:** M. **Data risk:** none (read-only).

### l. Usability: OPTIONAL

| Item | Cost | Note |
|---|---|---|
| Double-submit protection on all forms (only 5 views have it) | S | Small global JS in the layout |
| Tablet layout review of the run edit page | S–M | Not checked yet (NOT CHECKED in the audit) |
| Offline tolerance (save queue in the browser, sync later) | **L** | Needs service worker and conflict rules; no new server infrastructure, but high complexity. Not recommended now. |
| Photo compression before upload | S | Reduces mobile data use |

---

## 4. Roadmap: thin end-to-end slices in dependency order

DM = changes the data model (additive migration only, never applied without approval).

| # | Slice | Class | DM | Acceptance tests |
|---|---|---|---|---|
| 1 | **Answer Q15**, then finish F-05: override reason stored, logged and printed | MUST | no (section JSON) | a contradicting verdict without a reason is refused; the reason is on the PDF and in the log |
| 2 | Keep original photo on crop (i2) | MUST | yes: `attachments.replaces_attachment_id BIGINT NULL` | crop keeps the original; the PDF uses the latest; the original is retrievable |
| 3 | Per-severity plan recorded and printed (a1) | MUST | yes (see a1) | 3 n values stored; the "Sampling basis" block is printed |
| 4 | Derive found counts from defect rows (b1) | MUST | yes: `inspection_run_aql.counts_source ENUM('defect_rows','manual') DEFAULT 'manual'` | counts equal row sums; mismatch blocks finish |
| 5 | Atomic numbering (d2) | SHOULD | yes: `CREATE TABLE number_sequences(name VARCHAR(40) PRIMARY KEY, next_value BIGINT UNSIGNED NOT NULL)` | parallel creates give unique numbers |
| 6 | Inspectors as users + inspector per run (j, first part) | SHOULD | yes: `employees.user_id`, `inspection_runs.inspector_employee_id` | the run shows the inspector; the report is traceable |
| 7 | Review/approval sign-off (c3) | SHOULD (MUST if 17020) | yes: `inspection_signoffs` | no self-approval; immutable; DRAFT watermark until released |
| 8 | Issued reports with revisions (d1) | MUST if external reports | yes: `inspection_reports` | a reprint is byte-identical; the supersedes chain is printed |
| 9 | Soft deletes (d3) | SHOULD | yes: `deleted_at` ×2 | delete hides; restore works; activity logged |
| 10 | Lot, cartons and re-inspection link (f1, f3) | SHOULD | yes (columns in f1, f3) | √N draw reproducible; link printed |
| 11 | Lightweight NCR (e) | SHOULD | yes: `ncrs`, `ncr_actions` | created from non-pass only; closure needs verification |
| 12 | KPI definitions + page + CSV (k) | SHOULD | no | numbers match hand-computed fixtures; CSV injection escaped |
| 13 | Measurements with tolerance (g) | SHOULD | yes: `measurement_specs`, values JSON | tolerance flags at the edges |
| 14 | 2026 table check (a2) | MUST (before claiming 2026) | no | cell tests against the purchased standard |
| 15 | Switching rules (a3) | SHOULD if Q17 = repeat lots | yes: 2 tables | each transition per the licensed text |
| 16 | EXIF, retention, export (i3, i4) | SHOULD | yes: setting column | GPS absent from stored copies; export complete |
| 17 | Section data versioning (i1), competence records (j, second part), complaints register, instruments (h) | only if 17020 or Q19 apply | yes | per item |

**Order.**
- Slices 1–4 are correctness: small and high value.
- Slices 5–8 make reports defensible to customers.
- Slices 9–13 add traceability and management information.
- Slices 14–17 depend on decisions (Q17–Q20) or on buying the standard.

**Out of scope here:** Finance (see `docs/FINANCE_REVIEW.md`); any claim of compliance or certification.
