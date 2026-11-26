CONFIDENTIAL - do not commit or deploy

# Inspection run review: TradeSyncERP (2026-11-25)

Advisory only. No code, config, data or dependency was changed. Reviewed tree: commit `c5342d6` with staged docs (see §5.1).

**Labels.**
- **VERIFIED** means I ran it, loaded it or queried it.
- **READ-ONLY** means I inferred it from code.
- **HYPOTHESIS** means it is an inference.

Writes happened only on `trade_scratch`, through a second app server on :8001. `trade_prod` received SELECTs only, inside `READ ONLY` transactions.

---

## 1. Summary

**What the run does today.** An inspection holds runs (one sample and colour each). A run copies its sections from the inspection-type template.
- The inspector fills sections, defect rows, photos and an AQL plan, then presses **Finish Inspection**.
- Finishing checks that mandatory sections are complete. It resolves a verdict (Final Review field, then AQL verdict, then defect rows, then Pending) and locks the run. Only an Admin can reopen it, with a reason.
- The PDF is regenerated from live data on every download.

**Verified state.**
- The AQL engine is correct: 238 of 238 cases matched an independent calculation.
- The run lock blocks every write path, and reopen is permissioned and logged.
- Numbering is collision-free under concurrency (1,000 of 1,000 unique).
- Uploads are allowlisted, output is escaped, and authorization behaves as configured.
- 344 tests pass.

**Top 5 defects.**
1. **RR-01:** a run can be finished as **PASS with no AQL plan**.
2. **RR-02 (Q15):** the customer PDF prints the inspector's "Pass" **and** "Final Inspection Decision FAIL". This is true of the real report RPT-2026-00004.
3. **RR-03:** defect rows and AQL can be saved only by pressing **Finish**. There is no unsaved-changes warning, and an expired session loses everything.
4. **RR-04:** the **last defect row can't be deleted**.
5. **RR-05/06:** issued reports change silently. The header is editable after finish, and masters can be renamed or deleted.

The deployment state matters too (RR-17): `trade_prod` lacks the 7 committed migrations, so the committed code fails against it.

**Top 5 improvements.**
1. Block finishing an AQL-type run without a plan.
2. Settle Q15: one verdict, and a reason when overriding.
3. Add a "Save" button for the whole form plus an unsaved-changes guard.
4. Fix the defect-row sentinel.
5. Lock the header after finish, guard masters in use, then issued report snapshots.

**What you must decide:** Q15, plus Q28–Q34 (in §8 and `docs/AUDIT_QUESTIONS.md`).

---

## 2. Assumptions and what was NOT checked

- **Assumption 1:** `trade_scratch` behaves like production. It is a fresh mysqldump of `trade_prod` (2026-11-25) plus the 7 committed migrations and the create-only permission seeder. *If wrong:* RR-17 shows production may be at a different migration state. Confirm it (Q34).
- **Assumption 2:** the business is a third-party inspection service. This is still the unconfirmed hypothesis A-1 / Q17. Severity ratings for report findings (RR-02, RR-05) assume reports go to external customers. *If wrong* (in-house QC): lower RR-05 to Medium.

**Not loaded:** pages on **:8000**. `SESSION_DRIVER=database`, so every web request, even GET /login, writes a row to `trade_prod.sessions`. That breaks the SELECT-only rule. Run 8/9 was walked through on an identical scratch clone instead (§5.2). Only `/up`, which has no session, was requested on :8000.

**Concurrency over HTTP:** not checked. PHP's built-in server on Windows serves one request at a time, so concurrency was tested with 4 parallel CLI workers against the real service (§5.6c).

**Photos on production disk:** not checkable. 51 of the 73 attachment paths in `trade_prod` don't exist on this machine, because the DB copy came without the files.

Also **not checked**:
- Real phone photo sizes: the test photos were synthetic and about 190 KB.
- Keyboard-only navigation and screen-reader labels, beyond a static look.
- Slow or offline network behaviour, beyond session expiry.
- Section deactivation: my PUT lacked required fields.
- ISO 2859-1:2026 tables: not purchased. **No alignment with the 2026 edition is claimed.**

Screenshots and scripts are kept outside git, in the session scratchpad.

---

## 3. Map and lifecycle

### 3.1 Code that the run uses (VERIFIED: route list; READ-ONLY: code)

| Step | Route (method) | Code |
|---|---|---|
| List / show / header create, edit, delete | `inspections.*` (resource) | `Operations\InspectionController`, `Store/UpdateInspectionRequest`, views `operations/inspections/{index,show,edit,_form}` |
| Create run | GET `inspections/{i}/runs/create`, POST `inspections/{i}/runs` | `InspectionRunController@create/store` (:55, :89). Sections are copied from `inspection_type_section_defaults` in `resolveRunSections` (:826). The `final_review` section is always added. An AQL record is created when the type has `aql_sampling`. |
| Edit page | GET `…/runs/{run}/edit` | `@edit` (:141), reads only. View `runs/edit.blade.php` plus 33 section partials in `runs/sections/`. `public/assets/js/aql-plan.js`. |
| Per-section save / complete / skip (AJAX) | POST `…/sections/{rs}/save` | `@saveSection` (:592). Sends `status`, `notes`, `data` only (`collectSectionPayload`, `edit.blade.php:1140`). |
| Full form: AQL, defect rows, section data, finish | PUT `…/runs/{run}` | `@update` (:177). Submitted **only** by the Finish button (`edit.blade.php`, `runForm.submit()`). |
| Photos | POST `…/sections/{rs}/upload`, POST `…/attachments/{a}/edit` (crop), POST `…/sections/{rs}/attachments/reorder`, DELETE `…/attachments/{a}` | `@uploadAttachment` :437, `@updateAttachmentImage` :534, `@reorderAttachments` :509, `@deleteAttachment` :489 |
| Reopen | POST `…/runs/{run}/reopen` | `@reopen` :812, `ReopenInspectionRunRequest`, `InspectionRunLockService` |
| Delete run | DELETE `…/runs/{run}` | `@destroy` :412 |
| PDF (single / bulk) | GET `…/runs/{run}/export-pdf`, GET `inspections/{i}/bulk-export-pdf` | `InspectionExportController`, `exports/inspection-run-pdf.blade.php`, `PdfPageNumberService` |
| AQL preview | POST `inspections/aql-calculate` | `@aqlCalculate` :646 |
| Generic attachments | POST `attachments/{type}/{id}`, DELETE `attachments/{a}` | `AttachmentController`. It refuses run-section evidence. |

**Services:**
- `AqlCalculationService` (tables);
- `InspectionVerdictService` (roll-up, display);
- `InspectionSectionPolicyService` (skip and finish rules);
- `InspectionDefectCountService` (derived counts);
- `InspectionRunLockService` (lock and reopen);
- `NumberSequenceService` (report numbers).

**Middleware and permissions:**
- `auth`, `verified`, `EnsureUserIsActive`;
- `permission:inspections.edit` for the whole run controller and the export controller;
- `inspections.reopen` for reopen;
- `inspections.index/create/edit/delete` for the header.

**Model guard:** `GuardsFinishedRun` on run sections, AQL, AQL size rows and defects.

**Masters used:**
- inspection types and their section templates;
- the section library;
- the defect library;
- customers (via sample);
- customer orders (via the header pivot);
- samples, sample variations, colours and sizes;
- employees (inspectors);
- company settings (PDF header and footer);
- users (activity log);
- the AQL calculator tool (separate records, same service).

### 3.2 Data model (VERIFIED: `SHOW CREATE TABLE` on trade_scratch)

`inspections` (report_number UNIQUE; `inspection_type_id` → types **ON DELETE SET NULL**)
- **1→n** `inspection_runs` (CASCADE). `sample_id`, `sample_color_id` → SET NULL. **No unique (inspection_id, run_number).**
  - **1→n** `inspection_run_sections` (CASCADE). `inspection_section_id` → library **ON DELETE CASCADE**. UNIQUE (run, section).
    - **1→n** `inspection_run_defects` (CASCADE). `defect_id` → defects **SET NULL**.
    - Polymorphic `attachments`, with **no FK**. Index (type, id, sort_order).
  - **1→1** `inspection_run_aql` (CASCADE) → `inspection_run_aql_size_breakdowns`.

Inspectors are linked through `employee_inspection` (per **inspection**). Orders are linked through `inspection_customer_orders`.

### 3.3 Lifecycle (VERIFIED by running it, §5.2)

```
Run:  created (verdict Pending, completed_at NULL)
        │  per-section Save / Mark complete / Skip (AJAX: status, notes, data only)
        │  "Finish Inspection" → PUT with finish_run=1 → also the ONLY way AQL + defect rows are saved
        ├─ mandatory section open → data saved, NOT finished, list of missing sections shown
        └─ all done → verdict = FinalReview.overall_verdict ▸ AQL verdict (if ≠ Pending) ▸ defect rows ▸ Pending
                      completed_at = now; inspection.overall_status = roll-up
Finished (read-only: every run write path → 422/redirect; DB guard on child rows)
        │  POST reopen — permission inspections.reopen (Admin only by default) + reason → logged
        └─ back to unfinished, verdict Pending, roll-up recalculated
Delete run: only when unfinished.   Delete inspection: refused if any run is finished.
Inspection header (type, date, remarks, inspectors, orders): editable at ANY time (RR-05).
```

### 3.4 Masters: snapshot or live? (VERIFIED §5.9 unless marked)

| Master | Run keeps a copy? | Effect of edit | Effect of deactivate / delete |
|---|---|---|---|
| Section template `default_data` | **copied** at run creation (READ-ONLY, :826) | old runs unaffected | delete **refused** when in use. A DB cascade would erase run data if the guard were bypassed. |
| Section name, type, skip policy | live (join) | old reports relabelled (READ-ONLY) | — |
| Defect name | live | **old report shows the new name** | delete **allowed**: row keeps its severity, **loses its name** |
| Defect severity | **copied** onto the row (when sent) | old rows keep their severity | — |
| Inspection type | live | **old report shows the new name** | delete **allowed** while in use: 7 inspections went to type NULL, PDF shows "Inspection Type --", and the template cascades away |
| AQL plan (n, Ac, Re, code, standard) | **stored** on `inspection_run_aql` | — | — |
| Sample / customer / colour | live (FK SET NULL) | relabelled | `trade_prod`: **5 of 8 runs reference samples that no longer exist** |
| Inspectors (employees) | live, per inspection | relabelled | — |
| Company settings (logo, footer) | live | old reports change | — |

---

## 4. Business rules as implemented (file:line in `InspectionRunController.php` unless noted)

| Rule | Implementation | VERIFIED? |
|---|---|---|
| Code letter, per-severity n, Ac, Re, 100% cap, overall n = largest | `AqlCalculationService::calculate`, called at :274. Per-severity n and code stored (:300–330). | VERIFIED: 238 cases |
| "Not allowed" | Ac 0 / Re 1 at base plan | VERIFIED |
| Found counts | Defect rows win when any exist (`refreshAqlFromDefects` :723). Otherwise typed. Functional rows not counted. | VERIFIED (§5.4 D–H) |
| AQL verdict | `verdict()`: Fail > Pending (no plan) > zero ⇒ Pending until AQL section complete ⇒ Pass | VERIFIED |
| Section skip / finish | `InspectionSectionPolicyService`, used at :229 and :240 | VERIFIED |
| Run verdict | `resolveRunVerdict` :774: **Final Review `overall_verdict`** (:780) ▸ AQL ▸ defect rows ▸ Pending | VERIFIED |
| Overall Article Result (`overall_result`) | **Ignored by the verdict.** Printed in the PDF as its own row. | VERIFIED (§5.3) |
| Roll-up | `InspectionVerdictService::rollUp` via `Inspection::syncOverallStatus` | VERIFIED |
| Lock / reopen | `InspectionRunLockService`, checks at :179, :414, :440, :497, :512, :542, :595 | VERIFIED |

### 4.1 Disagreements with the documentation

1. `docs/QC_RULES.md` §2 says "Saving defects (form **or AJAX**) refreshes the counts". The server supports it, but **the UI never sends defect rows by AJAX**, so in practice only Finish saves them (RR-03).
2. `docs/QC_RULES.md` §5 says "Finished runs are read-only". The **inspection header** (type, date, remarks, inspectors, orders) stays editable, and it prints on the report (RR-05).
3. `docs/QC_RULES.md` §3 says Final Review is "today" the inspector's verdict. The UI captures it in **Overall Article Result**, which is ignored (Q15, RR-02). The doc flags this already.
4. `CLAUDE.md` says "`section_type` has 12 fixed values". The DB enum has **19** (adds task_list, quantity_sampling, cartons, cover_photo, files_review, defects, finish, conclusion; `conclusion_section` is named `conclusion`).
5. `CLAUDE.md` says "AJAX via Axios, not fetch()". The run edit page uses `fetch()` for every write.
6. The edit page shows "0/15 Completed" for a run with 16 sections (Final Review is not counted; READ-ONLY).
7. `docs/QC_ENHANCEMENT_PROPOSAL.md` says 309 tests pass. The tree has **344**.

---

## 5. Verification log and results

### 5.1 Environment and safety (VERIFIED)

| # | Command / action | Database | Result |
|---|---|---|---|
| 1 | `git status --short` and sha256 of `git diff` / `git diff --cached` at start | — | 6 lines (staged `Notes.md` + 5 docs). Worktree diff empty (`e3b0…b855`). Index diff `9996…c0fe`. Status hash `dd97…d624`. |
| 2 | `curl /up` on :8000 | none (health route, no session) | 200 |
| 3 | Row counts in a READ ONLY transaction (inspections, runs, run sections, run defects, attachments, activity log, sessions, cache, migrations, max activity id) | trade_prod | 6 / 8 / 89 / 4 / 73 / 581 / 1 / 5 / 104 / 581. **Identical at the end.** |
| 4 | `trade_prod` latest migration | trade_prod | `2026_09_07_000002`. **The 7 committed migrations (2026_11_25_*) are not applied.** |
| 5 | Drop the leftover `number_sequences`, then load a fresh `mysqldump --single-transaction trade_prod` | trade_scratch | 104 migrations, 8 runs |
| 6 | `DB_DATABASE=trade_scratch php artisan migrate --force` and `db:seed --class=RolesAndPermissionsSeeder --force` | trade_scratch | 7 migrations DONE; permissions added |
| 7 | `php artisan serve` checked | — | **Unsafe for this purpose.** `ServeCommand::$passthroughVariables` drops `DB_DATABASE`, so the child would use `.env` (trade_prod). |
| 8 | Probe on :8002: config-only boot under `php -S` | — | `getenv=trade_scratch config=trade_scratch` |
| 9 | **Second server:** `cd public && DB_DATABASE=trade_scratch php -S 127.0.0.1:8001 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php` | trade_scratch | GET /login: `trade_scratch.sessions` 1→2, `trade_prod.sessions` 1→1 with an unchanged timestamp |
| 10 | Test users on scratch: no role, Employee, Accountant, Lab Manager (random password, not printed). Admin login uses the seeder password. | trade_scratch | **The production copy still accepts the seeded default admin password** (D2) |

### 5.2 Walkthrough and lifecycle (check 2.1, 2.2; scratch via :8001)

| Check | Expected | Actual | Result |
|---|---|---|---|
| Run 9 edit page | loads | 200 in 2.3 s; 361 KB; 17 cards, 16 sections; 251 inputs, 29 file inputs; only 3 `required` attributes; **no unsaved-changes guard** | PASS (heavy) |
| Show, list, types, sections library, defects, PDF | load | 200 each (0.45–1.0 s); PDF 1.1 MB in 1.9 s | PASS |
| Edit GET writes nothing | read-only | controller :141 reads only; JS writes only on click (`fetch` on buttons) | PASS (READ-ONLY + VERIFIED) |
| Create inspection and run (type 4, sample 18, colour 11) | 16 sections, AQL placeholder | as expected; AQL placeholder lot 0 | PASS |
| Partial save, leave, resume in a new session | data kept | kept | PASS |
| Photo upload | stored | stored; **EXIF kept** | PASS / F-26 confirmed |
| AQL + defects (6 major, 2 minor) with typed counts 0 | counts from rows | found_major 6, `defect_rows`, verdict Fail | PASS |
| Finish with mandatory sections open | not finished, sections listed | "Saved, but the run was not finished…" plus the list | PASS |
| Finish when complete | finished, verdict, roll-up | Fail / Fail | PASS |
| PDF after finish | plan per severity | Critical L/200 0/1; Major J/80 5/6 found 6 FAIL; Minor J/80 7/8 | PASS |
| Writes after finish (13 paths, §5.6g) | blocked | 12 blocked; data byte-identical; **inspection header edit succeeded** | PARTIAL (RR-05) |
| Reopen (Lab Manager / no reason / Admin with reason) | 403 / error / OK + log | 403 / validation error / OK, log has reason and previous verdict | PASS |

### 5.3 Q15 reproduced (check 2.3)

- **`trade_prod`** (SELECT only): the finished run whose Overall Article Result ≠ verdict is **run 3 (inspection 4, report RPT-2026-00004)**. Overall Article Result = Pass, there's no AQL record, and 2 defect rows (major, minor) produce stored verdict **Fail** through the defect-row rule.
- **The same run on scratch:**
  - *Edit screen:* the Pass radio is checked.
  - *Show page and list:* Fail.
  - *PDF:* section summary "Overall Article Result PASS"; body "Overall Article Result: Pass"; cover "Overall Verdict FAIL"; card "Final Inspection Decision FAIL".
- **New fixture run 10:** the inspector chose Pass with the remark "Inspector says pass". AQL Fail made the run verdict Fail, and the PDF prints both.
- **What surprises a user:** the inspector's explicit choice changes nothing, and the customer sees two contradictory results on one report.

### 5.4 Calculations (check 2.4)

- **Independent reference:** Python, built from source A of `docs/AQL_SOURCES.md` (the AQLSchemes grid with arrows resolved). It shares no code with the app.
- **238 cases, all driven through the real save endpoint (PUT):**
  - lot sizes at every Table I boundary, plus 1, 3, 1,000 and 5,000;
  - all 7 levels (I, II, III, S1–S4);
  - all 11 AQL values plus "not allowed", rotated across severities;
  - defects at Ac, at Re, mixed, and zero.
- **Result: 0 mismatches** in code letter, overall n, per-severity n and code letter, Ac/Re, and verdict. Lot 1 gives no plan, as intended.
- **App policy, mirrored and labelled as such:** the overall n is the largest per-severity n; "not allowed" uses the base plan.
- **The printed PDF matches the stored values** (run 10).
- **No disagreement with `docs/AQL_SOURCES.md`.** The tables remain the 1999 / Z1.4-2003 (R2018) generation, and the 2026 edition is unchecked.

Extra cases:

| Case | Actual | Result |
|---|---|---|
| A: zero defects, AQL section pending | Pending | PASS |
| B: zero defects, AQL section complete | Pass | PASS |
| C: variations 300+700, typed lot 7, inspect_qty 9999 and 0 | lot 1000 (typed value silently replaced); **inspect_qty stored as sent (9999)**, never recomputed | FAIL (RR-10) |
| D: typed found_major 9, no rows | 9, manual, Fail | PASS |
| E: typed 9 plus a row of qty 2 | 2, defect_rows, Pass | PASS (rows win) |
| F: last row removed in the form, typed 9 | **still 2**: the row was not deleted (no `defects` key is posted) | FAIL (RR-04) |
| H: functional row, qty 4 | not counted, Pass | as documented (Q27) |
| Empty AQL plan, AQL section completed by AJAX, then Finish | **run Pass**, AQL lot 0, AQL verdict Pending; PDF "AQL Sampling Verdict: Pending" next to "Overall Verdict PASS" | **FAIL (RR-01)** |

### 5.5 One source of truth (check 2.5)

- Edit, show, list and PDF agree on the run verdict, **except** where they print the ignored Overall Article Result (RR-02).
- Typed counts disagreeing with rows: the rows win, and the edit screen shows the derived value with a hint. PASS.
- Removing the last row doesn't apply (RR-04).
- An AQL section marked "complete" by AJAX doesn't mean an AQL plan exists (RR-01).
- No CSV export exists.

### 5.6 The proposal's specific claims (check 2.6)

| Claim | Result | Evidence |
|---|---|---|
| a. Crop keeps the original | **Confirmed fixed** (stage 2) | Original recorded and present after 2 crops; intermediate edit removed. **No UI or PDF can open the original**, and the original keeps EXIF. |
| b. PDF regenerated live; no issue date or revision | **Confirmed** | Finished run 10 reprinted after renaming defect and type and editing the header: type, date (back-dated 01 Jan 2026), remarks and defect name all changed. Only "Report Generated <now>" exists. |
| c. Numbering collisions | **Refuted for the current code** | 4 concurrent CLI workers × 250 numbers from the real `NumberSequenceService`: 1,000 unique, contiguous, interleaved. HTTP parallelism not checkable (single-threaded server). **No unique (inspection_id, run_number) index.** |
| d. Section-data changes logged? | **Confirmed gap** | A section data change logs only status and notes. Photo crop and upload: **not logged**. Defect rows and AQL: logged. Reopen: logged with reason. |
| e. Inspector per run / who may finish | **Confirmed gap** | Inspectors exist only per inspection. A Lab Manager who wasn't the inspector finished a run. The activity log records the user (causer 12), but no "finished by" is stored or printed. |
| f. Double submit | **Partial** | Finish twice: the second is blocked by the lock. Add-defect form twice: no duplicate, but **the second submit deleted the new row and recreated it with a new id** (log: created 2, deleted 1). Photos are keyed to the row id. |
| g. Lock after the stabilise work | **Confirmed** for all 12 run paths: full form (section, defect, AQL), AJAX save (with and without defects), upload, crop, reorder, photo delete, generic attachment delete (403), run delete, inspection delete. Run data byte-identical afterwards. **Header edit not blocked** (RR-05). |

### 5.7 Validation and abuse (check 2.7)

| Input | Result |
|---|---|
| lot −5, 0, 1.5, "abc" | 422 with a field message (JSON verified). Not saved. |
| lot 99,999,999,999 / found 999,999,999 / defect qty 100,000,000 | **HTTP 500** (column overflow, no upper bound). Rolled back, nothing saved. (RR-16) |
| found −1, 2.5; level IV | 422 |
| **AQL value "3.0" or "abc"** | **Accepted**: stores `aql_major=3.000` with no plan; **50 majors ⇒ Pending, not Fail** (RR-09) |
| Posted verdict, completed_at, ac_major, sample_size | ignored. PASS. |
| `<script>` / `<img onerror>` in notes and section data | escaped on screen (0 raw, 2 escaped); printed as text in the PDF. PASS. |
| Defect carton_no over 50 characters | 422 (whole save rejected) |
| Section id of another run in `sections[]` | ignored. PASS. |
| Run upload .html / .svg / .php / 25 MB | 422 each. PASS. |
| Two editors | **last write wins silently** (A's stale submit overwrote B's notes and data) (RR-11) |
| Session expired, then save | **419 Page Expired; all typed data lost.** AJAX shows only "Could not save". The JSON includes `exception` (APP_DEBUG=true). |

### 5.8 Authorization (check 2.8)

(allowed\*) means the role is permitted and the action wasn't executed, to keep the fixtures intact.

| Action | admin | lab mgr | employee | accountant | no role | guest |
|---|---|---|---|---|---|---|
| list / show | 200 | 200 | 200 | 200 | 403 | →login |
| run edit, run PDF, bulk PDF | 200 | 200 | **403** | **403** | 403 | →login |
| **photo file `/storage/…`** | 200 | 200 | 200 | 200 | 200 | **200** |
| run save / section save / upload / delete run / new inspection | allowed\* | allowed\* | 403 | 403 | 403 | →login / 401 |
| reopen | tested OK | 403 | 403 | 403 | 403 | →login |

Read-only roles can't print reports (F-22 confirmed). **Evidence photos are public to anyone holding the URL** (RR-08).

### 5.9 Master data effects (check 2.9): see §3.4

- Defect delete is allowed: the name is lost.
- Inspection type delete is allowed: 7 inspections lost their type and the PDF prints "--".
- Section delete is refused while in use.
- Deactivate a defect: the PDF is fine.
- Deactivate a section: **not checked** (request incomplete).

### 5.10 Real data (check 2.10; trade_prod, SELECT only, counts)

| Query | Count |
|---|---|
| Runs: Pending unfinished / Fail finished / Conditional finished | 5 / 2 / 1 |
| **Finished runs with pending sections** | **3 of 3** (5, 2 and 2 pending sections) |
| Finished runs with zero defect rows | 0 |
| AQL records / with lot size 0 | 4 / **4** |
| `qc:aql-reevaluate --summary` (read-only) | 4 records, 0 changed |
| **Runs whose sample no longer exists** | **5 of 8** |
| Defect rows with a NULL defect / inspections with a NULL type | 0 / 0 |
| Duplicate report numbers / run numbers | 0 / 0 |
| **Run-section attachments whose section no longer exists** | **8** |
| Inspection status stored Pass / Fail / Pending | 2 / 2 / 2 (inspection 6 = Pass with no finished run) |
| Attachment files present on this machine | 22 of 73: **not meaningful** (files not copied with the DB) |

### 5.11 Performance (check 2.11; scratch, :8001 single-threaded; query count = `Questions` delta)

| Page | Result |
|---|---|
| Edit, empty run | 2.4 s, about 29 queries, 346 KB |
| Edit, 80 photos + 50 defects | 2.7 s, about 30 queries (**no N+1**) |
| Show | 2.3 s, about 22 queries |
| List | 1.6 s, about 16 queries |
| **PDF, 80 photos** | **19 s, 3.7 MB** |
| PDF, 82 photos (2 at 4000×3000) | 10.6 s, 4.2 MB |
| Bulk PDF | 14 s |
| Upload, 2 large photos | stored as-is (no resizing) |

- Indexes are used for attachments and defect rows (EXPLAIN).
- Local PHP has no time limit and 1.28 GB of memory. **On typical shared hosting (30 s limit) the PDF is at risk with real phone photos** (HYPOTHESIS: real photos are larger than the synthetic ones).

### 5.12 Usability (check 2.12; headless Chrome on the saved page; screenshots outside git)

- **375 px:** cards, Save / Mark-complete buttons and the Export button are **cut off on the right** (horizontal overflow).
- **768 px and 1024 px:** usable.
- **General Information re-asks, as free text,** for buyer, factory, PO, style, date and **inspector name**, all of which the system already holds.
- Per-section errors are shown as "Could not save. Try again." The server's reason, for example "cannot be skipped", is hidden.
- **Steps to record one defect with a photo:**
  1. open Defect Recording;
  2. search and pick a defect;
  3. set qty, size and carton;
  4. press **Finish Inspection** and confirm the "not complete" dialog (this is the only save);
  5. wait for the page reload;
  6. find the row's photo control;
  7. pick or take the photo (upload).

  That is **at least 7 steps**, using a button labelled "Finish" to save.
- Keyboard and screen-reader use: not checked.

### 5.13 Tests (check 2.13)

`php vendor/bin/phpunit` on **trade_test**: **344 passed, 2,861 assertions** (the guard is intact).

The run-area tests drive real HTTP endpoints and the AQL tests use an independent reference grid. None of them only assert mocks.

**Untested behaviours found here:**
- RR-01 (finish with an empty AQL plan);
- RR-02 (Overall Article Result ignored);
- RR-03 (save only via Finish);
- RR-04 (last defect row);
- RR-05 (header after finish);
- RR-06 (master delete);
- RR-09 (invalid AQL value);
- RR-10 (inspect_qty);
- RR-11 (concurrent editors);
- RR-16 (500 on large numbers);
- the 375 px layout;
- public photo access.

---

## 6. Defect register (ranked)

"Known" refers to audit F-xx, proposal G-xx or open question Q-xx. "New" means not reported before.

| ID | Sev | Where | Reproduce | Failure | Customer impact | Fix | Status | Known? | Retest |
|---|---|---|---|---|---|---|---|---|---|
| RR-01 | **Critical** | `resolveRunVerdict` :774; AJAX `saveSection` lets the `aql` section be `complete` without a plan | Type with AQL; "Mark as Complete" on every section; leave lot size blank; Finish | Run **PASS**; AQL record lot 0, verdict Pending | A report says PASS for an AQL inspection with no sampling | Finishing requires a valid AQL plan (lot ≥ 2, plan present) when the run has an `aql` section. The AQL verdict must not be Pending at finish. | VERIFIED | New (prod has 4/4 AQL records at lot 0) | Feature test: finish refused; message names AQL Sampling |
| RR-02 | **Critical** | `resolveRunVerdict` :780 reads `final_review.overall_verdict`; UI writes `overall_article_result.overall_result` | Any run with Overall Article Result ≠ computed verdict | Inspector's choice ignored; PDF prints both | Contradictory Pass/Fail on one customer report (real: RPT-2026-00004) | Decide Q15. Then one verdict field, mandatory override reason, and the PDF prints one decision plus the override line. | VERIFIED | Known (Q15, F-05) | Feature tests per Q15 decision |
| RR-03 | High | `edit.blade.php`: only `runForm.submit()` (Finish) sends `aql[...]` and `defects`; `collectSectionPayload` :1140 sends data/notes only; no `beforeunload` | Enter defects or AQL, press a section's Save, leave | Nothing saved. Saving needs "Finish". Session expiry gives 419 and loses all. | Missing or late defects on reports; inspectors finish early to save | A "Save run" button (same PUT, no finish flag). AJAX save includes defect rows and AQL for those sections. Unsaved-changes guard. Keep-alive or recover the draft. | VERIFIED | New | Browser test: defect saved without Finish; guard prompts |
| RR-04 | High | defect partial (no sentinel); `update` :381 `array_key_exists('defects', …)` | Remove the last defect row; submit | Row stays; counts and verdict stay | Report can keep a defect the inspector removed (may cause Fail) | Hidden sentinel `sections[ID][defects]` (empty) plus treat present-and-empty as "delete all" | VERIFIED | New | Feature test: last row deleted, counts 0 |
| RR-05 | High | `InspectionController@update`; live PDF (`InspectionExportController`) | Finish a run; edit the header (date, type, remarks) or rename masters; reprint | Issued report changes silently; no issue date or revision | Customer copy ≠ archived copy; back-dating possible | Lock the header when any run is finished (reopen to change). Then issued report snapshots with revisions (proposal d1). | VERIFIED | Known G9/G10/d1. **Header gap new.** | Feature test: header PUT refused after finish; reprint byte-identical once issued |
| RR-06 | High | `Masters\DefectController@destroy`, `InspectionTypeController@destroy`; FK SET NULL / CASCADE | Delete a defect or type used by a finished run | Defect name lost; 7 inspections lost their type; template cascades | Old reports change or lose labels | Refuse delete when referenced (deactivate instead), like the section library | VERIFIED | New | Feature tests: delete refused with a message |
| RR-07 | High | data (`trade_prod`) | SELECT queries in §5.10 | 3/3 finished runs with pending sections; 4/4 AQL at lot 0; 5/8 runs reference missing samples; 8 orphan attachments | Issued reports built on incomplete data | Read-only data report per run, owner decision (Q33). Never auto-fix. | VERIFIED | Partly known (F-25) | Re-run the queries |
| RR-17 | High | deployment | `trade_prod` at migration 2026_09_07; code `c5342d6` | `Unknown column voided_at` / `counts_source`; `number_sequences` missing | If production runs this code unmigrated: dashboard, ledgers, run AQL save and inspection create fail | Run the documented rollout (migrate + permission seeder) **before or with** deploying the code | VERIFIED on trade_prod (SQL errors). Production: HYPOTHESIS. | New | `php artisan migrate:status` on production |
| RR-08 | High | `public` disk, `/storage/...` | GET a photo URL without login | 200 to a guest; EXIF kept | Evidence (possibly factory GPS) reachable by anyone with a link, with no revocation | Serve run evidence through an authorised route (proposal i; Q11/Q31). Strip GPS. | VERIFIED | Partly known (F-26, Q11) | Guest GET returns 302 or 403 |
| RR-09 | Medium | `update` validation `aql.aql_*` = `string` | PUT `aql_major=3.0` with 50 majors | No plan for that severity: Pending, not Fail | Tampered or legacy value can hide a failing class | Validate against `AQL_LEVELS` plus `not_allowed` | VERIFIED | New | 422 test |
| RR-10 | Medium | `update` AQL block; variations stored as posted | PUT variations with inspect_qty 9999 | Stored and printed; typed lot silently replaced by the sum | Wrong distribution table on the report | Recompute inspect_qty server-side (`distributeProportionally`); tell the user when lot = Σ order qty | VERIFIED | New | Feature test |
| RR-11 | Medium | no optimistic locking | Two editors, stale submit | Silent overwrite | Lost inspector input | `updated_at` / version check, 409 with a message | VERIFIED | Known (F-19) | Two-client test |
| RR-12 | Medium | `LogsActivity` options; no `finished_by`; inspectors per inspection | §5.6d/e | Data changes and photo changes not logged; anyone with edit finishes any run | Weak traceability (17020 7.3.2 if relevant) | Log section-data diff keys; log attachment events; store `finished_by` and the inspector per run | VERIFIED | Known (G7, G11) | Activity-log assertions |
| RR-13 | Medium | dompdf with embedded photos | PDF with 80 photos | 19 s, 3.7 MB; no resizing | Timeouts on hosting | Resize or compress on upload (keep the original); thumbnails in the PDF | VERIFIED (local) | New | Time PDF with 80 real photos < 10 s |
| RR-14 | Medium | `runs/edit.blade.php` layout | 375 px screenshot | Horizontal overflow; buttons cut off | Phone data entry hard | Responsive fixes (Bootstrap grid and wrapping) | VERIFIED | New (proposal l unverified) | Screenshot at 375 px |
| RR-15 | Medium | `InspectionExportController` uses `inspections.edit` | Employee or Accountant opens the PDF | 403 | Read-only staff can't print | `inspections.view` for export | VERIFIED | Known (F-22) | 200 for view roles |
| RR-16 | Low | missing `max:` rules | Huge numbers | HTTP 500 | Error page; nothing saved | Upper bounds matching the column types | VERIFIED | New | 422 tests |
| RR-18 | Low | `syncSectionDefects` | Double submit of an add-defect form | Row deleted and recreated with a new id | Photos keyed by row id could be dropped | Idempotency token, or disable the submit button | VERIFIED | New | Double-post test |
| RR-19 | Low | `edit.blade.php` save handler; `APP_DEBUG` | Skip a mandatory section; expired session | Generic error; JSON exposes `exception` | Confusing errors | Show the server message; `APP_DEBUG=false` in production | VERIFIED | New | UI shows the reason |
| RR-20 | Low | `inspection_runs` | `SHOW INDEX` | No unique (inspection_id, run_number) | Relies on the app lock only | Additive unique index (after checking duplicates: 0 today) | VERIFIED | New | Duplicate insert fails |
| RR-21 | Low | `attachments.original_file_path` unused in the UI | — | Original kept but unreachable | Can't show evidence on request | "View original" link (permissioned) | VERIFIED | New | UI link test |
| RR-22 | Low | `general_information` section | Screenshot | Free-text buyer, factory, PO, inspector duplicate masters | Inconsistent reports | Prefill from the header, sample and order; read-only display | VERIFIED | New | Prefill test |

**Counts:**
- Critical 2 (new 1, known 1);
- High 7 (new 5, known 2);
- Medium 7 (new 4, known 3);
- Low 6 (new 6).

**Hypotheses** for RR-17 in production, ranked:
1. Production code updated but not migrated. Confirm with `migrate:status`.
2. Production still on the old code. Confirm with `git log -1` on the server.

---

## 7. Recommendations and roadmap

### 7.1 What works well (verified, with reasons)

- **AQL engine:** 238 of 238 cases equal an independent calculation, per severity, including arrows and the 100% cap.
- **Lock and reopen:** 12 write paths blocked; data byte-identical afterwards; reopen permissioned, reasoned and logged.
- **Finish completeness rule:** data saved, not finished, missing sections listed.
- **Numbering:** 1,000 concurrent numbers, all unique.
- **Input hardening:** escaping on screen and in the PDF; cross-run ids ignored; client-supplied verdicts ignored; uploads allowlisted and size-limited.
- **Section library delete:** guarded.
- **Defect-row severity:** stored on the row, so it survives a master severity change.
- **Queries:** no N+1, indexes used.
- **Tests:** 344 pass, and the test database guard holds.

### 7.2 Recommendations

**A. Fix now** (correctness of customer-facing results):

| What | Why (defect) | Alternatives | Trade-offs | Not when | Effort | Value | Deps | Data risk | Tests |
|---|---|---|---|---|---|---|---|---|---|
| A1. Finish needs a valid AQL plan when the run has an AQL section | RR-01 | Warn only | Inspectors must enter lot size | Types without AQL | S | High | — | none | finish refused / allowed |
| A2. Resolve Q15: one verdict source, override reason, one decision on the PDF | RR-02 | Print only the computed verdict | Workflow change | — | S–M | High | Q15 | none (new saves only) | per decision |
| A3. "Save run" button + AJAX defect/AQL save + unsaved-changes guard | RR-03 | Autosave (more JS) | More UI | — | M | High | — | none | browser and feature tests |
| A4. Defect sentinel (delete the last row) | RR-04 | — | — | — | S | High | — | none | feature test |
| A5. Deploy discipline: migrate + seed **with** the code | RR-17 | — | — | — | S | High | owner | additive only | `migrate:status` |
| A6. Validate AQL values and numeric upper bounds; recompute inspect_qty | RR-09, RR-10, RR-16 | — | — | — | S | Medium-High | — | none | 422 tests |

**B. Before wider use:**

| What | Why | Effort | Value | Data risk |
|---|---|---|---|---|
| B1. Lock the inspection header after any run is finished (reopen to change) | RR-05 | S | High | none |
| B2. Refuse deleting defects and types in use (deactivate instead) | RR-06 | S | High | none |
| B3. Authorised photo route (evidence not public) + GPS strip on upload | RR-08 (Q11, Q31) | M | High | none (paths kept) |
| B4. Inspector per run + `finished_by`; log section-data and attachment events | RR-12 | M | Medium-High | additive |
| B5. Mobile layout at 375 px; show server errors; prefill General Information | RR-14, RR-19, RR-22 | M | Medium | none |
| B6. Resize and compress uploads (keep the original); PDF thumbnails | RR-13 | M | Medium | none (new files) |
| B7. Export permission for view roles | RR-15 | S | Medium | none |
| B8. Read-only report of legacy runs (pending sections, missing samples, orphan files) for an owner decision | RR-07 (Q33) | S | Medium | none |

**C. Worthwhile:**
- C1. Optimistic locking on the run form (RR-11).
- C2. Issued report snapshots with revisions (proposal d1). This depends on B1 and A2.
- C3. Unique (inspection_id, run_number) (RR-20).
- C4. "View original photo" (RR-21).

**D. Optional:** offline entry; switching rules (only if Q17 confirms repeat lots); variables sampling.

### 7.3 Comparison with the 17 slices in `docs/QC_ENHANCEMENT_PROPOSAL.md`

| Proposal slice | Verdict | Reason |
|---|---|---|
| 1 Q15 / F-05 | **Confirm, keep #1** (as A2) | Reproduced on a real report |
| 2 Keep original photo | **Done** (stage 2) | Verified; add RR-21 later |
| 3 Per-severity plan | **Done** | Verified in DB and PDF |
| 4 Derive counts | **Done, with a gap** | Rows win, but RR-03 and RR-04 limit it in the UI |
| 5 Atomic numbering | **Done** | Verified 1,000 unique; add the DB index (C3) |
| 6 Inspectors as users + per run | **Confirm, re-rank to B4** | Verified gap |
| 7 Sign-off | **Keep, after A and B1** | Not useful while header and verdict are unstable |
| 8 Issued reports + revisions | **Keep (C2), after B1** | Live regeneration verified |
| 9 Soft deletes | **Lower** | Deletes are already blocked for finished work; B2 covers masters |
| 10 Lot / cartons / re-inspection link | Keep, unchanged | Not tested here |
| 11 NCR | Keep, unchanged | — |
| 12 KPIs | **Later** | Data too small to need it now; fix the inputs first (A1–A4) |
| 13 Measurements | Keep, unchanged | Confirmed G16: label and result only |
| 14 2026 table check | **Confirm** | Still unchecked |
| 15 Switching rules | **Drop until Q17** | No repeat-lot evidence in data |
| 16 EXIF, retention | **Re-rank up (B3)** | Public photo access verified |
| 17 17020-only items | Unchanged | — |
| **Added** | A1, A3, A4, A5, A6, B1, B2, B5, B6 | New verified defects the proposal didn't see |

### 7.4 Roadmap: thin slices in dependency order (migrations are additive; SQL idea only, never applied)

1. **A5:** migrate and seed with the deploy. Acceptance: `migrate:status` clean in production; dashboard and run AQL save work.
2. **A1:** finish needs a valid AQL plan. Acceptance: the RR-01 scenario is refused, with a message naming AQL Sampling.
3. **A4:** defect sentinel. Acceptance: removing the last row deletes it; counts 0; verdict recomputed.
4. **A6:** validation (AQL values, upper bounds, inspect_qty recomputed). Acceptance: 422 for 3.0, abc and huge values; stored inspect_qty = distribution.
5. **A3:** Save run + AJAX defects/AQL + unsaved guard. Acceptance: a defect saved without Finish; leaving with changes prompts.
6. **A2:** after Q15. Acceptance: one decision on the PDF; override reason stored, logged and printed.
7. **B1 + B2:** header lock, master delete guard. Acceptance: header PUT refused after finish; deleting a defect or type in use is refused.
8. **B3:** evidence route + GPS strip. Acceptance: guest gets 302/403 on photos; GPS absent in stored copies. **DM:** none required (route reads existing paths). Optional `attachments.visibility`.
9. **B4:** **DM:** `ALTER TABLE inspection_runs ADD inspector_employee_id BIGINT UNSIGNED NULL, ADD finished_by BIGINT UNSIGNED NULL;` plus logging. Acceptance: the PDF shows the inspector and who finished.
10. **B5, B6, B7, B8:** layout, errors, prefill, image resize, export permission, legacy-data report.
11. **C1:** **DM:** none (uses `updated_at`). Acceptance: stale submit gives 409 with a message.
12. **C3:** **DM:** `ALTER TABLE inspection_runs ADD UNIQUE KEY uniq_run_number (inspection_id, run_number);` Acceptance: a duplicate insert fails.
13. **C2 (issued reports)**, then sign-off (proposal 7): **DM** as in the proposal (`inspection_reports`, `inspection_signoffs`).

---

## 8. Open questions (also appended to `docs/AUDIT_QUESTIONS.md`)

Q15 (still open) and Q28–Q34. Each one, with its recommended default, is in `docs/AUDIT_QUESTIONS.md`.
