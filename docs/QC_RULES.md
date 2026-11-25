# QC rules — as implemented

This file is the single place these rules are written down. The code that enforces each rule is named next to it. If you change a rule, change the code and this file in the same commit.

Status of open decisions: see `docs/AUDIT_QUESTIONS.md` (Q12–Q16, Q28–Q37).

---

## 1. AQL sampling plan

Code: `app/Services/Inspection/AqlCalculationService.php` is the only copy of the tables. The browser gets them from `tableForJs()` via `public/assets/js/aql-plan.js`. Sources: `docs/AQL_SOURCES.md` (verify against the purchased standard).

1. **Standard.** ISO 2859-1 / ANSI-ASQ Z1.4 / MIL-STD-105, single sampling, **normal** inspection.
   - Inspection levels: I, II, III, S1–S4.
   - AQL levels: 0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5.
   - Tightened, reduced, double and multiple sampling are not implemented.
2. **AQL keys** are canonical strings (`'0.10'`, `'2.5'`). Input such as `0.1`, `2.50` or `4` is matched in integer thousandths. Float array keys and float equality are never used.
3. **Code letter.** Table I gives it from the lot size and inspection level. A lot below 2 is outside the table, so there is no plan.
4. **Ac/Re per severity.** Table II-A gives them. For an **arrow** cell, use the first plan in the arrow's direction, *including that plan's sample size*. So each severity can have its own sample size and code letter.
5. **100% inspection.** If a plan's sample size is equal to or larger than the lot size, the sample size becomes the lot size. That plan's Ac/Re still apply (Q13).
6. **Units to inspect** (`inspection_run_aql.sample_size`) are the **largest** per-severity sample size, capped at the lot size (Q12). The colour/size distribution uses this number.
7. **"Not Allowed"** for a severity means Ac 0 / Re 1 at the base sample size (any defect fails). It is stored as `-1` in `aql_*` and printed as "Not allowed".
8. **Recorded and printed per run.** For each severity, the code letter and sample size are stored (`code_letter_*`, `sample_size_*`), along with the standard reference (`standard_ref`). The PDF prints a "Sampling basis" line: standard, level, "normal inspection; switching rules not applied", 100% inspection when it applies, and where the found counts came from.
   - The reference reads **"ISO 2859-1:1999 / ANSI/ASQ Z1.4-2003 (R2018), single sampling"** until the tables are checked against ISO 2859-1:2026 (Q26).
   - Tightened and reduced inspection and the switching rules are not implemented.

## 2. AQL verdict

Code: `AqlCalculationService::verdict()`.

**Found counts (one source of truth).** When the run has recorded defect rows, `found_critical/major/minor` are the sums of their quantities per class, and typed counts are ignored. A row's own severity is used if set, otherwise the defect library's. `counts_source = defect_rows`.
- `functional` defects are not one of the three AQL classes and are not counted (Q27).
- Without defect rows, the counts are entered by hand (`counts_source = manual`).
- Saving defects (**Save run**, **Finish**, or the defect section's own **Save** button, which sends the rows by AJAX) refreshes the counts and the AQL verdict.
- **Removing every row.** The counts the rows produced no longer exist: in that save the found counts become 0 and `counts_source` goes back to `manual`. From the next save on, the typed counts apply again. How rows are saved: §8.

Code: `InspectionDefectCountService`, `InspectionRunController::refreshAqlFromDefects()`.

These are checked in order:

1. If any severity has found > Ac, the verdict is **Fail**.
2. If defects were found for a severity that has **no plan** (Ac is null), the verdict is **Pending**. It never passes.
3. If no defects were found:
   - while the AQL check is open, the verdict is **Pending**;
   - once it is **complete**, the verdict is **Pass**. Complete means the run's AQL section is marked complete, or the run is being finished.
4. Otherwise, the verdict is **Pass**.

In the PDF, a severity row with no Ac prints "—", never PASS.

The AQL Calculator tool has no "complete" step, so its zero-defect result stays Pending (Q14).

## 3. Run decision and the inspector's assessment (Q15 = a)

Code: `InspectionRunController::verdictParts()`, `resolveRunVerdict()` and `assessmentFinishProblem()`; `InspectionVerdictService::decide()`, `contradicts()` and `fromAssessment()`.

**The decision** (`inspection_runs.verdict`, set when a run is finished) is the **computed verdict**:

1. the **AQL verdict**, if it is not Pending;
2. otherwise, from the **defect rows** of the defect section: Critical/Major → Fail; Minor/Functional only → Conditional; none → Pass;
3. otherwise, Pending.

**The inspector's assessment** is the **Overall Article Result** choice (`overall_result`, labelled "Inspector's assessment" on the edit page). Mapping: Pass → Pass, Fail → Fail, Conditional Pass → Conditional, Re-Inspection Required → Re-Inspection. "Pending" or no choice means no assessment.
- It is recorded and printed. It **never changes** a computed decision.
- **Only when nothing computes** (step 3: no AQL verdict and no defect section) does the assessment become the decision. No contradiction can arise then (assumption A1).
- **A contradicting assessment needs a reason to finish.** It contradicts when it exists, the decision is not Pending, and they differ.
  - Without a reason, the finish is refused (the data is saved), and the message names the "Reason the assessment differs" field.
  - The reason (at most 2,000 characters) is stored in the same section's data, as `assessment_reason` (no new column).
- **Every finish is logged** (event `finished`) with the decision, the computed verdict, the assessment and the reason.
- The old **Final Review `overall_verdict`** field is no longer read. A read-only count on the `trade_prod` copy found it filled on 0 of 8 runs (A2).

**Where it shows.**
- **Run edit page.** The calculated decision so far, next to the assessment, with the hint that the report's decision is calculated from the AQL result and the defects. For a finished run, the header shows "Decision".
- **Inspection page and list.** The decision and the roll-up (unchanged).
- **PDF.**
  - One "Final Inspection Decision". Under it, per run, "Inspector's assessment — Run #N", which either agrees, or "differs from the decision" with the reason.
  - The section body prints "Inspector's assessment", plus the reason when they differ.
  - The summary row shows the assessment as **RECORDED**, never PASS or FAIL.
  - A finished run that contradicts and has no stored reason (stored before this rule) prints "no reason recorded". Stored data is not rewritten (A3).

Run verdict values: Pending, Pass, Fail, Conditional, Re-Inspection.

## 4. Inspection overall status (roll-up)

Code: `InspectionVerdictService::rollUp()`, applied by `Inspection::syncOverallStatus()` when a run is finished or reopened.

- **Precedence:** Fail > Re-Inspection > Conditional > Pending > Pass.
- An **unfinished** run counts as Pending.
- No runs means Pending.
- **Conditional and Re-Inspection never roll up to Pass.**
- `overall_status` is **derived**. It can't be posted from the inspection form (validation: `prohibited`).
- `N/A` exists in the column, but nothing sets it. Which inspection types have no verdict is Q16.
- The PDF cover, header badge, final decision card and each style group's "x of y colors passed • STATUS" line all use this same roll-up and the display map in `InspectionVerdictService::display()`.

Stored statuses are recalculated only when a run is finished or reopened. Historical rows are not rewritten.

## 5. Finished runs are read-only

Code: `InspectionRunLockService`, the `GuardsFinishedRun` model trait, and checks in `InspectionRunController`.

**Scope.** A finished run's own data is read-only (below). The inspection header and two masters that the report prints are protected by the rules further down. Other things the PDF prints are still read live and can change an old report: names of samples, customers, colours and employees, defect names (a rename, not a delete), section names, and company settings. Issued report snapshots are not implemented (proposal d1).

- **What a finished run is.** A run with `completed_at` set is read-only. The following are refused:
  - full-form save;
  - per-section save;
  - photo upload, delete, reorder and crop;
  - run delete.
- **Model-level guard.** It also refuses any create, update or delete of the run's sections, AQL record, AQL size rows and defect rows.
- **Reopen.** `POST /inspections/{i}/runs/{r}/reopen`.
  - It needs permission **`inspections.reopen`** (Admin only by default) and a written **reason** (5–2000 characters).
  - It clears `completed_at`, sets the verdict to Pending, writes an activity-log entry (event `reopened`, with the reason, the previous verdict and the previous completion time), and recalculates the inspection status.
- **Inspection delete.** An inspection with **any finished run** can't be deleted.
- **Inspection header (owner decision D2, Q29).** While any run of an inspection is finished, its header can't be changed: type, date, remarks, inspectors and customer orders. `InspectionController@update` refuses with "Inspection details are locked because run #N is finished. Reopen the run first…", and the edit page shows the lock with the form disabled. Reopening that run (above) unlocks it. Reads are unaffected, and a new run can still be added to the inspection. Code: `InspectionRunLockService::headerLockedBy()`.
- **Master deletes (owner decision D3, Q30).** Deactivating stays allowed in every case.
  - A **defect** recorded on any finished run's defect rows can't be deleted. A defect used only by unfinished runs, or not used, can be (`Masters\DefectController@destroy`).
  - An **inspection type** used by an inspection that has a finished run can't be deleted. Other types can be (`Masters\InspectionTypeController@destroy`).
  - A **library section** can't be deleted while any inspection type template or any run uses it (`InspectionSectionController@destroy`).
  - The refusal says why and says to deactivate instead (JSON requests: 422 with `success: false`). Finished reports still print a deactivated defect's and type's names.
- **Activity log.** Runs, run sections, run AQL and run defects are logged with key fields only:
  - logged: status, notes, verdict, plan and counts;
  - not logged: section `data` JSON, variations, photos.
- **Bypass.** `InspectionRunLockService::bypass()` exists only for building fixture data (demo seeder, tests).
- **Photo edits keep the original.** Cropping or rotating a photo stores the edit as the shown file and keeps the **first original file** (`attachments.original_file_path`). Later edits replace only the previous edit. Deleting the photo deletes both files (`Attachment::deleteFiles()`).
- **Numbering.** Report numbers and run numbers are never reused; see `docs/FINANCE_RULES.md` §6.

## 6. Section skip policy and completeness

Code: `InspectionSectionPolicyService`, enforced in `InspectionRunController::update()` and `saveSection()`.

| `skip_policy` | Can be skipped (`na`)? | Needed to finish the run |
|---|---|---|
| mandatory | no | `complete` |
| optional | no | nothing |
| skippable | yes | `complete` or `na` |

- A skip request for a non-skippable section is rejected with a validation error.
- Finishing with a required section open saves the data, does **not** finish, and lists the sections still to do.
- **AQL plan needed to finish (owner decision D1, Q28).** A run with an AQL section (`section_type = aql`) that is not skipped is finished only when its AQL record has:
  - a lot size of at least 2;
  - a computed plan (a code letter and a sample size);
  - an AQL verdict other than Pending. Pending at finish means defects were found in a class that has no AQL value.

  Otherwise the data is saved and the run is not finished. The message names the AQL section and the reason, after any sections still to do. The AQL verdict is then re-evaluated as "not finishing" (§2, rule 3). Types without an AQL section are unaffected. Marking the AQL section complete with its own button is still allowed; the check is at finish. Runs finished before this rule are not changed. Code: `InspectionRunController::aqlFinishProblem()`.
- **Save run** (next to Finish) saves every section, the AQL plan and the defect rows, and never finishes. Save run and Finish post the form once: their buttons are disabled while it is sent. If the session has expired (HTTP 419) or the server fails, the page stays as typed and says that nothing was saved.
- **Unsaved changes.** The run edit page warns before leaving (links on the page and the browser's own leave prompt) when a field differs from what was loaded or last saved. A section's own **Save** marks that section saved. That button saves status, remarks, section data and, for a defect section, its rows; it does **not** save the AQL plan (the page says to use Save run).

## 7. Run save validation

Code: `InspectionRunController::update()` and `saveSection()`. The limits follow the column types, so too large a number gets a 422 with a field message, never HTTP 500.

| Field | Accepted | Column |
|---|---|---|
| `aql.lot_size` | 1 to 4,294,967,295 | `INT UNSIGNED` |
| `aql.found_critical/major/minor` | 0 to 65,535 | `SMALLINT UNSIGNED` |
| `aql.aql_critical/major/minor` | one of the 11 AQL levels, or `not_allowed`. Numerically equal spellings (`2.50`, `0.1`) are the same level and are accepted. Anything else (`3.0`, `abc`) is rejected. | `DECIMAL(5,3)` |
| `aql.variations.*.order_qty` | 0 to 4,294,967,295, and their total at most 4,294,967,295 (it becomes the lot size) | JSON / `lot_size` |
| `aql.variations.*.inspect_qty` | ignored: computed (below) | JSON |
| `aql.size_breakdown.*.order_qty/checked_qty/error_qty` | 0 to 4,294,967,295 | `INT UNSIGNED` |
| `aql.sample_size` | ignored: computed. 0 to 65,535 accepted, because a new run's AQL placeholder posts 0. | `SMALLINT UNSIGNED` |
| defect row `qty` (form and AJAX) | 1 to 65,535 | `INT UNSIGNED`, but rows add up into `found_*` |
| defect rows per AQL class, all sections | total at most 65,535, else 422 and nothing is saved | `found_*` |

- **Lot size from variations.** When variations have ordered quantities, the lot size is their total. If the typed lot size differs, the save message says "Lot size set to N, the total ordered quantity of the variations (you entered M)."
- **Units to inspect per variation** (`inspect_qty`) are computed on the server: the plan's units to inspect (§1, rule 6) spread over the ordered quantities by largest remainder, the same method as the page. Posted values are ignored.
- Values stored before these rules (for example an `aql_major` of `3.000`) are not changed.

## 8. Defect rows: how a save applies them

Code: `InspectionRunController::syncSectionDefects()`.

- The defect section always posts `sections[ID][defects]`: a hidden, empty input placed **before** the rows. PHP keeps the last value of a repeated key, so rows posted after it replace it.
  - **Key present:** the posted rows are the section's full list. A row posted with its id is updated. A saved row that is not posted is deleted, with its photos. Key present and empty: every row of the section is deleted.
  - **Key absent:** the rows are not touched.
- **Repeated posts** (double click, stale tab, back button). A row posted **without** an id reuses a saved row of the same section when every field is identical (defect, severity, size, qty, carton, status, disposition, notes) and no posted id claims that saved row. Otherwise it is a new row. So posting the same form twice keeps one row with the same id and its photos (photos are keyed by row id, `defect_row_{id}`).
- Saves of one run are serialised: the run row is locked for the save, so two posts run one after the other. The lock check is repeated under that lock.
- A section's AJAX **Save** sends the rows and returns `defect_ids` (posted row index → saved id). The page writes those ids back into the form, so the next save updates the rows instead of creating them again.
- A finished run's rows can't change (§5).
