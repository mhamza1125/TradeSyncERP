CONFIDENTIAL - do not commit or deploy

# QC standards reference: TradeSyncERP

Advisory, stage 1. Accessed on **2026-11-25** unless stated otherwise. **Everything here must be verified against the purchased standard.** ISO texts are paywalled. The content below comes from official catalogue pages (title, edition, status), the free ISO preview of ISO 2859-1:2026 (foreword, contents, introduction), the publicly available text of ISO/IEC 17020:2012 (incorporated by reference in US regulation), accreditation-body guidance, regulator guidance, and inspection-company guides.

Software does not make an organization "compliant" or "certified". Certification and accreditation apply to **organizations and their management systems**. This document says what the software can **support**. The organization still has to define, run and evidence its own processes.

**Labels.**
- **SOURCE** means it is taken from a cited document.
- **VERIFIED** means it was checked against the code or data.
- **HYPOTHESIS** means it is an inference.
- **Confidence** is H, M or L.

**Note on iso.org:** iso.org refuses automated fetching (HTTP 403). The catalogue facts marked "(catalogue via search index)" are what the iso.org pages showed in search results today. Confirm them in a browser.

---

## 0. Sources

| # | Source | URL | How it was read |
|---|---|---|---|
| S1 | ISO catalogue: ISO 2859-1:2026 | https://www.iso.org/standard/2859-1 | catalogue via search index |
| S2 | ISO catalogue: ISO 2859-1:1999 (withdrawn) | https://www.iso.org/standard/1141.html | catalogue via search index |
| S3 | ISO 2859-1:2026 free preview (foreword, contents, introduction) | https://cdn.standards.iteh.ai/samples/iso/iso-2859-1-2026/82891cb8edcf41d28d2b3ad02502ce20/iso-2859-1-2026.pdf | PDF text read |
| S4 | Ellistat, "ISO 2859-1:2026: What's changing" | https://ellistat.com/en/iso-2859-12026/ | page read |
| S5 | ASC, "ISO 2859-1:2026 changes and AQL sampling explained" | https://ascfoodsafetytraining.com/iso-2859-1-2026-changes-aql-sampling/ | page read |
| S6 | SQCOnline, "Switching rules (ISO 2859-1)" | https://www.sqconline.com/switching-rules-iso-2859-1 | page read (chart text only) |
| S7 | NIST/SEMATECH e-Handbook 6.2.3.1 (MIL-STD-105) | https://www.itl.nist.gov/div898/handbook/pmc/section2/pmc231.htm | page read |
| S8 | QIMA, AQL page | https://www.qima.com/aql-acceptable-quality-limit | page read |
| S9 | ANSI webstore: ANSI/ASQ Z1.4-2003 (R2018), "Most recent" | https://webstore.ansi.org/standards/asq/ANSIASQZ12003R2018 | search result |
| S10 | ASQ Quality Press: Z1.4:2003 (R2018) | https://asq.org/quality-press/display-item?item=T1164 | search result |
| S11 | ISO catalogue: ISO 2859-2:2020 | https://www.iso.org/standard/64505.html | catalogue via search index |
| S12 | ISO catalogue: ISO 2859-3:2005 | https://www.iso.org/standard/34684.html | catalogue via search index |
| S13 | ISO/IEC 17020:2012 full text (law.resource.org) | https://law.resource.org/pub/us/cfr/ibr/inc/iec/iec.17020.2012.pdf | PDF text read |
| S14 | NATA, "Specific Accreditation Guidance ISO/IEC 17020:2026 Gap Analysis" (issued Aug 2026) | https://nata.com.au/files/2026/08/Specific-Accreditation-Guidance-ISO-IEC-17020-2026-Gap-Analysis.pdf | PDF text read |
| S15 | UKAS, ISO/IEC 17020:2026 transition | https://www.ukas.com/accreditation/iso-iec-17020-transition/ | search result (page itself 403) |
| S16 | SAC Singapore, publication of ISO/IEC 17020:2026 | https://www.sac-accreditation.gov.sg/publication-of-iso-iec-17020-2026-requirements-for-bodies-performing-inspections/ | search result |
| S17 | ISO catalogue: ISO 9001:2015/Amd 1:2024 (shows "withdrawn", "ISO 9001:2026 available") | https://www.iso.org/standard/88431.html | catalogue via search index |
| S18 | DNV, "Revision of ISO 9001 progressing to final stage" | https://www.dnv.com/ca/news/2026/revision-of-iso-9001-progressing-to-final-stage/ | search result |
| S19 | Advisera, "ISO 9001:2015 vs ISO 9001:2026" | https://advisera.com/articles/iso-9001-2015-vs-9001-2026-main-differences/ | search result |
| S20 | ISO 9001:2015 clause titles (DNV guidance; TÜV Rheinland CQI/IRCA briefing) | https://www.dnv.it/siteassets/images/pdf-documents/iso-9001-2015-guidance-document-eng.pdf ; https://www.tuv.com/content-media-files/master-content/global-landingpages/images/tuv-rheinland-academy/personnel-certification/pdf/irca/tuv-rheinland-cqi-irca-iso-9001-briefing-note.pdf | search result |
| S21 | ISO catalogue: ISO/IEC 17025:2017 ("confirmed 2023 … remains current") | https://www.iso.org/standard/66912.html | catalogue via search index |
| S22 | ISO catalogue: ISO 10012:2026 (replaces ISO 10012:2003) | https://www.iso.org/standard/10012 | catalogue via search index |
| S23 | US FDA, "Data Integrity and Compliance With Drug CGMP: Q&A" (Dec 2018) | https://www.fda.gov/media/119267/download | PDF text read |
| S24 | PIC/S PI 041-1, "Good Practices for Data Management and Integrity…" (1 July 2021) | https://picscheme.org/docview/4234 | PDF text read |
| S25 | QIMA, pre-shipment inspection | https://www.qima.com/pre-shipment-inspection ; https://blog.qima.com/quality-control/pre-shipment-inspection-procedure | search result |
| S26 | Sofeast / QualityInspection.org, "How to select the cartons to inspect" | https://qualityinspection.org/select-cartons-to-inspect/ | search result |
| S27 | SGS, final random inspection | https://www.sgs.com/en-zm/services/final-random-inspection | search result |
| S28 | TÜV SÜD, pre-shipment inspection | https://www.tuvsud.com/en/services/inspection/pre-shipment-inspection | search result |
| S29 | Pro QC, pre-shipment inspections | https://proqc.com/pre-shipment-inspections/ | search result |
| S30 | OnlineClothingStudy, "What is DHU" | https://www.onlineclothingstudy.com/2013/10/what-is-dhu-how-to-measure-dhu.html | search result |
| S31 | Eurofins, EU regulations for motorcyclist protective garments | https://www.eurofins.com/textile-leather/articles/safeguarding-bikers-navigating-eu-regulations-for-motorcyclist-protective-garments/ | search result |
| S32 | BSI, motorcycle PPE standards | https://www.bsigroup.com/globalassets/localfiles/en-gb/product-certification/personal-safety/bsi-motorocycle-ppe-standards-en-gb.pdf | search result |
| S33 | UNECE, UN Regulation No. 22 helmets (press, leaflet) | https://unece.org/media/press/399547 ; https://unece.org/sites/default/files/datastore/fileadmin/user_upload/Leaflet_Helmets.pdf | search result |
| S34 | EU harmonised standards for PPE (Reg. (EU) 2016/425) | https://single-market-economy.ec.europa.eu/single-market/goods/european-standards/harmonised-standards/personal-protective-equipment_en | search result |

"Search result" means the claim comes from the search engine's summary of that page, not from a full read. It is lower confidence than "page read" or "PDF text read".

---

## 1. Acceptance sampling: ISO 2859-1 and ANSI/ASQ Z1.4

### 1.1 Editions and status

| Document | Edition / status today | Source | Conf. |
|---|---|---|---|
| ISO 2859-1 | **3rd edition, 2026-01, "International Standard published".** It cancels and replaces the 1999 2nd edition and incorporates Amd 1:2011 and Cor 1:2001. | S1, S3 (foreword), S4, S5 | H |
| ISO 2859-1:1999 | **Withdrawn**, with the withdrawal stage dated 2026-01-22 | S2, S5 | M |
| ANSI/ASQ Z1.4 | **Z1.4-2003 (R2018)**, listed as "Most recent" and the current edition | S9, S10 | M (no check newer than 2018) |
| ISO 2859-2 (limiting quality, isolated lots) | 2020 edition, current | S11 | M |
| ISO 2859-3 (skip-lot) | 2005 edition, current. Skip-lot is now also inside ISO 2859-1:2026 (clause 9.5). | S12, S3 | M |
| ISO 28590 (introduction to the 2859 series) | 2017, listed as current | ISO ICS 03.120.30 listing (search result) | L |
| ISO 3951-1 (variables, single characteristic) | 2005 edition; one ISO listing shows stage 95.99 (withdrawn). **Status unclear, check.** | search result | L |

**Important for this app (VERIFIED + SOURCE).**
- `docs/AQL_SOURCES.md` and the code's tables were taken from MIL-STD-105D and a Z1.4 transcription, so they are 1999-era plans.
- S4 says the 2026 edition keeps the code-letter logic ("Letter Code | Unchanged"). S5 says no public source confirms whether any code letter or acceptance number changed. **This is a direct disagreement, so the app's tables must be compared with ISO 2859-1:2026 clauses 15–16 before claiming alignment with the 2026 edition.** Until then, describe the app as aligned with **ISO 2859-1:1999 / ANSI/ASQ Z1.4-2003 (R2018) normal single sampling**.

### 1.2 What the method requires (and what the software must record)

| Topic | What the sources say | Source | Conf. | Implication for software records and reports |
|---|---|---|---|---|
| Scope: a continuing series of lots | ISO 2859-1:2026 "is intended for application to a continuing series of lots of discrete products all supplied by one producer using one production process. If there are different producers or production processes, this document is applied to each one separately." | S3 (introduction, verbatim) | H | Switching rules need **lot history per producer (supplier) and product or process**. Isolated lots are the subject of ISO 2859-2. |
| Inspection levels | 3 general (I, II, III) and 4 special (S-1 to S-4). Level II is the default unless specified. Special levels are for costly or destructive tests. | S7, S5, S8 | M (purpose of special levels from S5 only) | Record the level on every run and print it. |
| Code letter, then plan | The standard gives a sample-size code letter, not a sample size. Clause 10 ("Sampling plans": 10.1 inspection level, 10.2 code letters, 10.3 obtaining a plan). Clause 15 has the code letters; clause 16 has the single sampling tables. | S7, S8, S3 (contents) | H | Record the code letter **and** the resulting n, Ac and Re per severity. |
| Normal, tightened, reduced and skip-lot | Clause 9 covers normal, tightened, reduced and skip-lot sampling inspection: 9.1 start, 9.2 continuation, 9.3 switching rules (9.3.1 normal→tightened, 9.3.2 tightened→normal, 9.3.3 normal→reduced, 9.3.4 reduced→normal, 9.3.5 skip-lot→normal, 9.3.6 reduced→skip-lot), 9.4 discontinuation, 9.5 skip-lot (qualification). | S3 (contents) | H | A **severity state per supplier and product** with a transition log. Each run records which severity applied. |
| Switching thresholds | S6 chart text: "Switching score value is at least 30"; "5 consecutive batches accepted"; "1 batch not accepted"; "2 of 5 or fewer consecutive batches not accepted"; "5 batches not accepted while on tightened inspection" → "Discontinue Inspection". The chart does **not** label which fragment belongs to which transition. The 2011 amendment changed the switching-score condition ("at least 30 or, with the approval of the…"; cut off in the snippet). | S6, search snippet of the 1999 text | **L** | Do not implement thresholds from summaries. Take them from the purchased 2026 text (clauses 9.3–9.4). |
| Skip-lot (new in Part 1) | It is optional. It needs producer and product qualification (9.5.2–9.5.4). Lots to inspect are selected at random (normative Annex C). S4 says qualification uses the results of the 10 preceding lots and a rate of "2 out of 5". | S3, S4 | H (existence), L (numbers) | Optional. It would need lot history and a random-selection record. |
| Arrows | "Use first sampling plan below/above arrow" | MIL-STD-105D Table II-A legend (see `docs/AQL_SOURCES.md`) | M (MIL-STD wording; not confirmed in 2026 text) | Already implemented (VERIFIED, `AqlCalculationService::planFor`). |
| 100% inspection | "If sample size equals, or exceeds, lot or batch size, do 100 percent inspection." | MIL-STD-105D legend (`docs/AQL_SOURCES.md`) | M | Already implemented (VERIFIED). |
| Ac / Re | Accept if found ≤ Ac; reject if found ≥ Re (example: Ac 10 / Re 11 at n=200, AQL 2.5) | S8, S7 | M | Already implemented (VERIFIED). |
| Classes of nonconformity | Clause 4.2, "Classification of nonconformities" | S3 (contents) | H (existence) | Severity per defect (critical, major, minor) is needed, with its definition. |
| Disposition of non-accepted lots | Clause 7.2 "Disposition of non-acceptable lots"; 7.3 "Nonconforming items" | S3 (contents) | H (existence) | Record the disposition per failed lot (see NCR proposal). |
| OC curves | Removed from 2026; methods are in Annex E | S3, S4, S5 | H | Out of scope. |

### 1.3 Relevance of the related parts to this business (HYPOTHESIS, based on Part 1 of the proposal)

| Document | Applies here? | Why |
|---|---|---|
| ISO 2859-1 normal single sampling | **Yes** (already used) | Final inspections with AQL (VERIFIED: inspection types "Final Inspection (AQL)"). |
| Tightened/reduced switching | **Only if** the same customer re-inspects the same supplier and product as a continuing series | The data is too small to tell (3 AQL records, all placeholders). Questions file, Q17. |
| ISO 2859-3 / 2859-1 skip-lot | Unlikely | It needs qualification and long histories, and buyer-paid third-party inspections usually inspect every shipment (HYPOTHESIS). |
| ISO 2859-2 (isolated lots) | Possibly | One-off orders from new suppliers are isolated lots (HYPOTHESIS). |
| ISO 3951-1 (variables) | Low | Measurements (garment dimensions) are recorded as free data; variables sampling is rarely used in softlines PSI (HYPOTHESIS). |
| ISO 28590 | Reading aid only | Introduction to the series. |

---

## 2. ISO/IEC 17020: bodies performing inspection

**This applies only if the organization is, or wants to be, an inspection body** (for example, one accredited to ISO/IEC 17020). See the proposal, Part 1: the data suggests a third-party inspection service (HYPOTHESIS).

### 2.1 Editions

| Edition | Status | Source | Conf. |
|---|---|---|---|
| ISO/IEC 17020:2012 | Superseded by 2026 (catalogue shows "[Withdrawn]") | ISO catalogue via search; S14 | M |
| **ISO/IEC 17020:2026** | Published **27 March 2026**. 3-year transition (to 27 March 2029 per SAC; NAC (US) gives 27 Feb 2029). UKAS and SAC assess only to 2026 from **1 January 2028**. Types A/B/C become **Type A / Type non-A**. It adopts the ISO/CASCO common structure, so clauses are renumbered. | S14, S15, S16 | M (dates from accreditation bodies' summaries) |

### 2.2 Requirements relevant to software (2012 clause = text read in S13; 2026 = NATA mapping S14)

| Topic | ISO/IEC 17020:2012 (SOURCE S13, verbatim or close paraphrase) | 2026 change (SOURCE S14) | Implication for software |
|---|---|---|---|
| Impartiality | 4.1: activities undertaken impartially; risks to impartiality identified on an ongoing basis | Independence moved to 5.1; "threats" wording | An impartiality-risk register is an organizational task. The software can record conflicts (inspector ↔ supplier). |
| Personnel | 6.1.5 procedures for selecting, training, **formally authorizing** and monitoring inspectors; 6.1.10 records of monitoring, education, training, technical knowledge, skills, experience and **authorization** | Competence framed as access to competent personnel | Link inspectors to users. Record authorization scope (inspection types or categories) and training. |
| Equipment / software | 6.2.13: computer software adequate for use (validation of calculations; revalidation on change); **procedures for protecting the integrity and security of data** | **New clause: the data-system shall be validated; changes (incl. configuration) authorized, documented and validated before implementation. Records safeguarded against unauthorized access, tampering and loss; system failures recorded with corrective actions** | Test evidence (the 309-test suite), change log, AQL table validation (`docs/AQL_SOURCES.md`), immutable records, access control, a failure log. |
| Subcontracting | 6.3.1–6.3.4: inform the client; responsibility stays with the body; record the competence check of subcontractors | Results delivered by external providers must be clearly identified | Flag subcontracted runs; show this on the report. |
| Methods | 7.1.7 observations recorded **in a timely manner**; 7.1.8 calculations and data transfers subject to appropriate checks | 7.1.7/7.1.8 merged into the records clause | Timestamps per entry (contemporaneous); automated AQL checks. |
| Items and samples | 7.2.1 items/samples **uniquely identified**; 7.2.3 abnormalities recorded | — | Lot, carton and sample identification. |
| Records | 7.3.1 a record system to demonstrate fulfilment; **7.3.2 report traceable to the inspector(s) who performed it** | **New: amendments to records trackable to previous versions or original observations; original and amended data retained, with date.** Records identify equipment with significant influence. | Audit trail with original values (activity log), versioning, inspector identity on the run. |
| Report content | **7.4.2:** (a) issuing body; (b) unique identification and date of issue; (c) date(s) of inspection; (d) item(s) inspected; (e) **signature or other indication of approval by authorized personnel**; (f) statement of conformity where applicable; (g) results | Adds (i) facility name or location of inspection when relevant; separates unique report ID and date of issue; "simplified" reporting allowed by client agreement (record kept) | Report header fields and an approval record. |
| Amendments | **7.4.5:** corrections or additions after issue recorded; **an amended report shall identify the report it replaces** | Amended report "shall be issued and clearly identify the report … replaced" | Report revisions with a supersedes chain. |
| Complaints and appeals | 7.5–7.6: documented process, available on request, decisions by the body | Title becomes "Handling of appeals" (complaints separated) | Optional complaint/appeal register linked to reports. |
| Records control | 8.4 (Option A) | 8.4.1–8.4.2: identification, storage, protection, **back-up, archive**, retrieval, retention and disposal; retention "consistent with contractual obligations"; records readily available | Retention setting, export, backup (the owner's hosting). |

Confidence: **H** for the 2012 text (primary). **M** for the 2026 mapping: the NATA document is a secondary, accreditation-body interpretation and its PDF table layout interleaves columns, so the **2026 clause numbers are not stated here and must be checked**.

---

## 3. ISO 9001 topics (records and nonconformity)

| Item | Status | Source | Conf. |
|---|---|---|---|
| ISO 9001:2015 | Superseded by **ISO 9001:2026** | S17 (catalogue: Amd 1:2024 "withdrawn", "ISO 9001:2026 available") | M |
| ISO 9001:2026 | Publication reported as **16 Sept 2026**. Transition to 30 Sept 2029 (consultancy sources). Moderate changes: quality culture and ethics (5.1), risks and opportunities split (6.1), knowledge (7.1.6), change management. | S18, S19 and consultancy sites | M/L (not from ISO's own text) |

ISO 9001:2015 clause titles (two secondary sources, S20; verify, and **check 2026 numbering**: no source confirmed that these numbers are unchanged in 2026):

| 2015 clause | Title | What it implies for records (paraphrase; verify) |
|---|---|---|
| 7.1.5 | Monitoring and measuring resources | Where measurements are used for conformity: suitable, maintained, and traceable or calibrated when required; retain evidence |
| 7.5 | Documented information | Create, update, control (identification, approval, protection, retention, disposition) |
| 8.6 | Release of products and services | Retained documented information on release, including traceability to the person(s) authorizing release |
| 8.7 | Control of nonconforming outputs | Identify and control nonconforming outputs; retain records of the nonconformity, actions, concessions and the authority deciding |
| 9.1 | Monitoring, measurement, analysis and evaluation | Decide what to measure; analyse results (supports KPIs) |
| 10.2 | Nonconformity and corrective action | React, evaluate cause, implement and review the effectiveness of action; retain records |

Applies **only if** the organization runs or wants a QMS certified to ISO 9001 (Q18). The software can support records. The QMS itself is organizational.

---

## 4. Calibration traceability: ISO/IEC 17025 and ISO 10012

| Document | Status | Source | Conf. |
|---|---|---|---|
| ISO/IEC 17025:2017 (testing and calibration laboratories) | Current; "last reviewed and confirmed in 2023" | S21 | M |
| ISO 10012 | **2026 2nd edition** ("Quality management — Requirements for measurement management systems") replaces 10012:2003 | S22 | M |

Relevance: **only if measuring instruments influence results**. Examples are tape measures for garment dimensions, scales for carton weights, and colour or moisture meters. ISO/IEC 17020:2012 6.2.6–6.2.10 (S13) requires calibration of equipment with significant influence "where appropriate", with traceability. The 2026 edition adds recording which equipment was used, and when (S14). Question Q19.

---

## 5. Data integrity: ALCOA / ALCOA+ (design guidance, not a requirement)

| Item | Source | Conf. |
|---|---|---|
| ALCOA: data "should be attributable, legible, contemporaneously recorded, original or a true copy, and accurate (ALCOA)" | S23 (FDA 2018, text read) | H |
| ALCOA+: adds **Complete, Consistent, Enduring and Available** | S24 (PIC/S PI 041-1, 2021, §7.4, text read) | H |
| Audit trail: "a secure, computer-generated, time-stamped electronic record that allows for reconstruction of the course of events relating to the creation, modification, or deletion of an electronic record" | S23 | H |

These are **pharmaceutical GMP regulator documents**. Here they are used **only as design guidance**: no regulatory requirement applies to this business from them (HYPOTHESIS: consumer softlines/PPE inspection is not GMP-regulated).

| Principle | Design meaning for TradeSyncERP |
|---|---|
| Attributable | Every entry carries a user (`causer_id` in the activity log); inspectors are linked to users |
| Legible | Readable reports and exports |
| Contemporaneous | Server timestamps per save (already in place), with no back-dating of `completed_at` |
| Original / true copy | Keep original photos; edits are new versions (crop edit currently **replaces** the file, see the proposal §i) |
| Accurate | Validated calculations (AQL tests); derived counts |
| Complete | Nothing hard-deleted; voids instead of deletes |
| Consistent | One rule set (`docs/QC_RULES.md`), one ledger service |
| Enduring | Backups, retention |
| Available | Export and retrieval for the retention period |

---

## 6. Industry practice (NOT standards)

Inspection-company practice, from at least four companies' public pages. Lower authority; it varies by company.

| Practice | What sources say | Sources | Conf. |
|---|---|---|---|
| When a final inspection happens | FRI when 100% of goods are produced and at least 80% packed | S27 (SGS); similar in QIMA S25 | M |
| Sampling basis | ISO 2859-1 / ANSI/ASQ Z1.4 | S25 (QIMA), S29 | M |
| Carton selection | Open at least the **square root** of the total number of cartons (500 cartons → 23 in their example); random selection, avoiding patterns such as 2, 4, 6, 8 | S26 | M (single source for the rule) |
| Defect classes | Critical / major / minor against predetermined tolerances. Critical = unsafe or non-compliant, which usually means an immediate fail. | S25 (QIMA), Wikipedia summary | M |
| Report content | Pass/fail result, summary of key findings, detailed results by category, photos, checklist results, AQL data where applicable | S25 (QIMA), S29 (Pro QC) | M |
| Certificate | PSI certificate issued as a trade document by independent agencies | S28 (TÜV SÜD) | M |
| DHU | DHU = total defects ÷ units inspected × 100. It counts defects, not defective pieces; it differs from "% defective". | S30 (plus a second garment-industry source in search results) | M |

---

## 7. Sector-specific standards (pointers only; not to be implemented)

The sector found in data is **motorcycle protective apparel and accessories**: jackets, boots, gloves, helmet accessories (VERIFIED: sample product names and categories).

| Product | Standard / regulation | Source | Note |
|---|---|---|---|
| Garments | EN 17092-1…6 (classes AAA, AA, A, B, C) | S31, S32 | Lab testing, not visual inspection |
| Impact protectors | EN 1621-1…4 (Part 4 under revision, 2025 draft) | S31, S32 | |
| Gloves | EN 13594 (levels 1/2; 2015 edition per one source) | S32 | |
| Boots | EN 13634 | S32 | |
| Helmets | UN Regulation No. 22, 06 series (in force 2021) | S33 | Helmets are outside the PPE Regulation |
| EU legal framework | Regulation (EU) 2016/425 (PPE): motorcycle clothing is Category II; harmonised standard references in Commission implementing decisions | S31, S34 | Certification by notified bodies |

What these imply for the software (HYPOTHESIS): record **certificate or test-report references** (CE category, notified body, EN class) on the sample or style, and check that labels and markings are present during inspection (a checklist item). Do **not** implement the test methods.
