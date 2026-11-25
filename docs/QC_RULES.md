# QC rules — as implemented

This file is the single place these rules are written down. The code that enforces each rule is named next to it. If you change a rule, change the code and this file in the same commit.

Status of open decisions: see `docs/AUDIT_QUESTIONS.md` (Q12–Q16).

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
- Saving defects (form or AJAX) refreshes the counts and the AQL verdict.

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

## 3. Run verdict (set when a run is finished)

Code: `InspectionRunController::resolveRunVerdict()`, with the mapping in `InspectionVerdictService::fromFinalReview()`.

Precedence:

1. **Inspector's explicit verdict**, today read from the Final Review section's `overall_verdict`:
   - Pass → Pass
   - Fail → Fail
   - Conditional Pass → **Conditional**
   - Re-Inspection Required → **Re-Inspection**
2. Otherwise, the **AQL verdict** if it is not Pending.
3. Otherwise, from the **defect rows**: Critical/Major → Fail; Minor/Functional only → Conditional; none → Pass.
4. Otherwise, **Pending**.

Run verdict values: Pending, Pass, Fail, Conditional, Re-Inspection.

> **Known gap (Q15, blocks F-05).** The run edit page captures the inspector's verdict in **Overall Article Result** (`overall_result`), which step 1 does not read. An override reason is **not** required yet: that waits for Q15.

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
