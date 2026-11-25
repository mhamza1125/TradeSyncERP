# AQL table sources

> **Status: VERIFY AGAINST THE PURCHASED STANDARD.** The tables in `app/Services/Inspection/AqlCalculationService.php` were transcribed from the two public sources below and cross-checked cell by cell. They have not been compared with a licensed copy of ISO 2859-1 or ANSI/ASQ Z1.4. Before using them contractually, compare them with the purchased standard.

Scope: **Table I** (sample size code letters), and **Table II-A** (single sampling plans for normal inspection), restricted to the AQL columns this app supports (0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5). For these tables, MIL-STD-105D/E, ANSI/ASQ Z1.4 and ISO 2859-1 (Table 1 and Table 2-A) carry the same plans.

## Sources

| | Source | URL | Accessed | Form |
|---|---|---|---|---|
| **A** | CRAN R package *AQLSchemes*, file `R/AAZ14Single.R` (matrices `SSCodeLetters`, `ANSIASQSingleNormalss`, `…ac`, `…re`), read via the CRAN GitHub mirror | https://raw.githubusercontent.com/cran/AQLSchemes/master/R/AAZ14Single.R | 2026-11-25 | Machine-readable. Arrow cells are **already resolved**: each cell holds the n, Ac and Re of the plan the arrow points to. |
| **B** | MIL-STD-105D (US Department of Defense, 29 April 1963), Table I (page 9) and Table II-A (page 10), public-domain copy | https://law.resource.org/pub/us/cfr/ibr/003/fedspec/MIL-STD-105D.PDF | 2026-11-25 | Scanned image (no text layer). The two table pages were extracted from the PDF and read visually. Arrows are kept as printed. Legend: "Use first sampling plan below/above arrow. If sample size equals, or exceeds, lot or batch size, do 100 percent inspection." |

Tried but not used: the DTIC copy of MIL-STD-105E (https://apps.dtic.mil/sti/tr/pdf/ADA284013.pdf) returned HTTP 403. A direct download was not permitted in this environment.

## How the comparison was done

1. **Table I**: 15 lot-size rows × 7 levels (S-1 to S-4, I, II, III), compared between A and B. **105 of 105 cells agree.**
2. **Table II-A**: B was transcribed as printed (numbers or ↓/↑). Each arrow was resolved by B's legend: take the first plan in the arrow's direction, **including that plan's sample size**. The result was compared with A's pre-resolved n/Ac/Re. **16 code letters × 11 AQLs = 176 of 176 cells agree** (n, Ac and Re).
3. The same comparison is now executable. The service encodes **source B** (arrows as printed), and `tests/Unit/Services/AqlCalculationServiceTest.php` holds **source A** (resolved). The test asserts every cell, plus invariants that need no source:
   - the sample-size series is 2…2000;
   - Re = Ac + 1;
   - Ac never decreases down a column;
   - every arrow resolves;
   - n is capped at the lot size.

No disagreement was found, so no cell needed an owner decision.

## What the previous code got wrong (for the record)

- **PHP `AQL_TABLE`.** It was keyed by floats, which PHP truncates to integers (`[0,1,2,4,6]`). Only AQL 4.0 was correct. 1.0 returned the 1.5 values, and every other level returned nothing. Arrow cells also kept the original, smaller sample size.
- **JS copies** (run AQL section and AQL calculator). At AQL 0.065 they used n=80 / n=125 with 0/1 for code letters J and K, where the table points to code L (n=200). The run form also posted option values like `0.1`, `0.4`, `1` and `4`, which the JS table (keyed `"0.10"`, `"4.0"`) could not find. Both copies have been removed. The JS now reads `tableForJs()` through `public/assets/js/aql-plan.js`.

## Not covered

Tightened and reduced inspection (Tables II-B and II-C), double and multiple sampling, and the switching rules are not implemented in this app and were not transcribed.
