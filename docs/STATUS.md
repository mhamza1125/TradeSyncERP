# Status — 2026-11-25

Stabilisation work after the audit (`docs/AUDIT_2026-11-25.md`). Nothing is committed yet.

## Tests

| | Database | Result |
|---|---|---|
| Before (audit) | sqlite `:memory:` | 38 tests: 14 pass, 23 errors (MySQL-only migrations), 1 failure |
| After stabilisation | MySQL `trade_test` | 309 tests, 2,713 assertions: all pass |
| After stage 2 (recommendations) | MySQL `trade_test` | 342 tests, 2,853 assertions: all pass, 0 deprecations |
| Inspection run review (start of stage 3) | MySQL `trade_test` | 344 tests, 2,861 assertions: all pass |
| After stage 3 (inspection run fixes) | MySQL `trade_test` | 396 tests, 3,119 assertions: all pass |
| After Q15 = (a) | **MySQL `trade_test`** | **412 tests, 3,177 assertions: all pass** |

How to run: `composer run test`. `tests/TestCase.php` refuses to run against anything other than `mysql:trade_test`, so tests can never wipe `trade_prod`.

## Done

| Audit item | Status |
|---|---|
| F-01 dompdf code execution | Fixed. `isPhpEnabled=false`. PDF data is escaped. "Page X of Y" is drawn by `PdfPageNumberService`. |
| F-02 open registration / unguarded endpoints | Fixed. No `/register`. Permission checks on the activity log, allowance types and attachments. Dashboard finance and activity widgets are permission-gated. |
| F-06 dangerous uploads | New uploads fixed (type allowlist). **Already-stored files: waiting on Q11.** |
| F-07 suspended users | Fixed. Login, mid-session logout, and session rows deleted on deactivation. |
| F-11 nested run URLs | Fixed. Scoped bindings. |
| F-03 / F-14 AQL tables and verdict | Fixed and verified against two sources (`docs/AQL_SOURCES.md`). Read-only checker: `php artisan qc:aql-reevaluate`. |
| F-04 roll-up | Fixed. Conditional / Re-Inspection statuses; one roll-up for the PDF cover and colour lines. |
| F-05 override reason | Done with Q15 = (a): a contradicting inspector's assessment needs a reason (QC_RULES §3). |
| F-08 immutability | Fixed. Finished runs are read-only; reopen needs `inspections.reopen` plus a reason, and is logged. Run models are logged. Inspections with finished runs can't be deleted. Soft deletes: not done (proposal pending). |
| F-12 / F-18 | Fixed: status is derived, and skip/completeness are enforced on the server. **N/A rule waiting on Q16.** |

All rules as implemented: `docs/QC_RULES.md` (QC) and `docs/FINANCE_RULES.md` (finance).

### Stage 2 (recommendations, 2026-11-25)

| Item | Status |
|---|---|
| F-21 + F-09 + FN-1: single `LedgerService`; correct openings; transfer check on the ledger balance; pages show the opening balance | Done |
| F-10: voids instead of deletes (expenses, payments, and their transactions) | Done |
| F-16: per-currency dashboard totals; derived overdue; new movements counted | Done |
| FN-3: payments linked to invoices by id; reference validated against the customer's invoices | Done. Settlement with WHT/deductions waits on **Q22**. |
| QC i2: crop keeps the original photo | Done |
| QC a1: per-severity plan stored; "Sampling basis" printed; "Not allowed" printed as words | Done |
| QC b1: AQL found counts from recorded defect rows | Done (functional severity excluded, **Q27**) |
| F-19 / d2: locked numbering; report numbers never reused | Done |
| F-05 override reason | Done (Q15 = a) |
| Sign-off / issued report revisions, KPIs | Not started (next) |

### Stage 3 (inspection run fixes, 2026-11-25, from `docs/INSPECTION_RUN_REVIEW.md`)

Rules as implemented: `docs/QC_RULES.md` §2 and §5–§8. No migration, no data change, no new package.

| Item | Status |
|---|---|
| RR-01 / D1 (Q28): finish needs a valid AQL plan | Done. Existing finished runs are not changed. |
| RR-04: removing the last defect row | Done (empty `defects` sentinel posted before the rows). |
| RR-18: double submit recreated a row | Done. Identical row re-posts reuse the saved row; saves of one run are serialised; submit buttons are disabled while posting. |
| RR-09: AQL values validated | Done |
| RR-10: `inspect_qty` computed on the server; lot-size replacement is reported | Done |
| RR-16: upper bounds give 422, not 500 | Done (limits: QC_RULES §7) |
| RR-03 (part): Save run button, unsaved-changes guard, defect rows by AJAX with ids returned | Done. The AJAX section save does not save the AQL plan; Save run does. |
| RR-19: real error messages; session expiry keeps the page | Done. `APP_DEBUG` not changed (owner action below). |
| RR-05 / D2 (Q29): header locked while a run is finished | Done |
| RR-06 / D3 (Q30): defects and types used by finished runs can't be deleted | Done |
| Found here: a new run's AQL placeholder (`sample_size` 0) made every full-form save fail until a lot size was entered | Done (the computed `sample_size` accepts 0) |
| RR-02 / Q15, RR-07 / Q33, RR-08 / Q31, RR-12 / Q32, RR-17 / Q34 | Not in this stage |

### Q15 = (a) (2026-11-25)

| Item | Status |
|---|---|
| RR-02 / F-05: one decision (computed verdict). Overall Article Result is the inspector's assessment; a contradicting assessment needs a stored, logged and printed reason | Done (`docs/QC_RULES.md` §3). No migration; stored data not rewritten. Old contradicting runs print "no reason recorded". |
| Found while testing: the hidden "Finish Inspection" section blocks finishing (Q38); checkpoint status reset on save (Q39); PDF summary "PASS" on a defect section (Q40) | Not changed: questions added |

### Owner actions (stage 3)

1. **RR-17 / Q34:** before deploying, run `php artisan migrate:status` and `git log -1` on production. Deploy the code only together with the rollout steps below.
2. **`APP_DEBUG=false`** in the production `.env`. With `true`, a server error's JSON includes exception details. The page no longer shows them, but the response still carries them.
3. **D2:** rotate the seeded `admin@erp.test` password or remove the account (accepted risk, below).
4. **RR-08 / Q31:** run photos are still public `/storage/...` URLs and keep their EXIF data (next stage).
5. **Q33:** decide what happens to legacy runs: finished runs with pending sections, and AQL records with lot size 0. Nothing is changed automatically. A legacy run that is reopened can only be finished again once it has an AQL plan.
6. Answer Q35–Q37 (`docs/AUDIT_QUESTIONS.md`, batch 5).

## Rollout steps for production (owner)

1. Back up the database and `storage/app/public` (D4: owner).
2. `php artisan migrate`. Adds 7 additive migrations (`2026_11_25_000001` … `000007`): enum widening, void columns, `customer_payments.customer_invoice_id`, `attachments.original_file_path`, per-severity AQL plan columns, `inspection_run_aql.counts_source`, and the `number_sequences` table. No existing row is changed: new columns are NULL, or default `manual` for `counts_source`. All 7 were rehearsed on `trade_scratch`.
3. `php artisan db:seed --class=RolesAndPermissionsSeeder --force`. Create-only: adds `activities.view` and `inspections.reopen` and gives them to Admin. **Until this runs, even Admin gets 403 on the activity log and can't reopen runs.**
4. `php artisan optimize:clear`. This empties the cache table only; sessions are not touched.

**Verified on 2026-11-25 on `trade_rehearsal`.** That is a fresh copy of `trade_prod`, run with `APP_ENV=production`, using steps 2–4 plus a full `php artisan db:seed --force` as the worst case. Every row of all 62 pre-existing tables (1,855 rows) was fingerprinted before and after, over its original columns.
- **0 rows deleted or changed** in any business table.
- Added only: 7 `migrations` rows, 2 `permissions` and 2 `role_has_permissions` rows, the new columns, and the empty `number_sequences` table.
- The only removed rows were the 5 `cache` entries, flushed by `optimize:clear`.

The seeders `InspectionSectionSeeder` and `InspectionTypeSectionDefaultSeeder` used to overwrite admin-edited sections and mappings (`updateOrCreate`). They now use `firstOrCreate` (test: `SeedersPreserveDataTest`). Uploaded files in `storage/app/public` are git-ignored and are not affected by a code deploy.
5. Optionally, `php artisan qc:aql-reevaluate --summary` (read-only).

## Accepted risks

- **D2 — seeded test account `admin@erp.test`** keeps its well-known seeded password. **Accepted for now by the owner. It must be rotated (or the account removed) before the app is exposed to real users or the internet** (assumption A1 treats it as internet-reachable). Not changed by this work.
- `DB::prohibitDestructiveCommands()` only protects `APP_ENV=production`. With `APP_ENV=local` and `.env` pointing at `trade_prod`, `migrate:fresh` / `db:wipe` would run against it. Recommendation (not done): also prohibit when the database name is `trade_prod`, or point local `.env` at a copy.

## Known issues found during this work (not fixed: outside the brief)

- `InspectionExportTestSeeder` (demo, local/testing only) fails: it writes `inspection_run_aql.notes`, a column dropped by migration `2026_07_07_073658`.
- The inspection PDF's own footer uses CSS `counter(pages)`, which dompdf 3.1.5 renders as "of 0" (verified with a probe).
- The AQL verdict of an **unfinished** run with no plan (lot below 2), zero defects and its AQL section marked complete is stored as "Pass" (`AqlCalculationService::verdict()`). Finishing is refused in that state, so it never becomes a run verdict (Q36).
- A deleted customer order or employee still removes its rows from the inspection's order and inspector lists (foreign key cascade). The header lock (D2) does not cover master deletes of orders or employees.
- Stored statuses in the `trade_prod` copy that differ from the new roll-up: 1 inspection stored Pass with a finished Conditional run, and 1 stored Pass while its runs are not all finished. They are recalculated only when a run in that inspection is finished or reopened. No data was changed.

## Open questions

Q11 (stored files), Q12/Q13/Q14 (AQL defaults implemented, confirm), Q16 (N/A), Q17, Q31–Q34, Q35–Q37 and Q38–Q40. Q15 is answered (a). See `docs/AUDIT_QUESTIONS.md`.
