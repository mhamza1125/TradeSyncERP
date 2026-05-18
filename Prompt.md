I want to refactor my Inspection Management Module into a dynamic section-based international-standard QC inspection system (similar to QIMA-style workflows).

Current system structure:

Inspection (general header)
→ multiple Inspection Runs
→ Products / Samples
→ each product has Testing Parameters
→ each parameter has Reviews (pass/fail, remarks, defects, attachments)
→ defect recording and reporting

Single inspection can have multiple runs (example: DUPRO, Final QC, Re-QC, CLI, PPI, etc.).

Inspection types include:

* Sample Check P1/P2 (SMS, PPS)
* Pre-Production Inspection (PPI)
* Inline Inspection (ILI / DUPPRO)
* Final Quality Check (AQL / 100%)
* Re-Inspection (Re-QC)
* Container Loading Inspection (CLI)

I want to refactor this into a dynamic modular inspection system based on international QC standards (AQL, warehouse inspection, final inspection, container loading, pre-production checks, etc.).

Required architecture:

1. Keep core structure:
   Inspection
   → Inspection Runs
   → Products / Samples
   → Parameters
   → Reviews + Defects + Attachments

Do NOT destroy the existing foundation unless necessary.

2. Introduce dynamic inspection sections/modules

Examples of sections:

* Product screening images
* Workmanship check
* Defect recording
* Critical / Major / Minor defect classification
* AQL sampling summary
* Packing check
* Carton list / carton verification
* Packaging check
* Labels check
* Measurement check
* Functional test
* Marking check
* Approved sample conformity
* Container loading details
* Seal number verification
* Shipment verification
* Pre-production checklist
* Factory readiness
* Corrective action plan (CAP)
* Final review and approval

Users should be able to enable/disable sections using checkbox/toggle during inspection creation.

Only selected sections should:

* appear in the form
* be required in workflow
* appear in final PDF report

3. Inspection Type = Default Preset

Example:
Final QC (AQL) auto-enables:

* workmanship
* AQL
* measurements
* packing
* labels
* defects
* functionality

CLI auto-enables:

* carton verification
* loading supervision
* container condition
* seal number
* shipment photos

PPI auto-enables:

* factory readiness
* raw material
* sample approval
* production readiness

BUT users must still be able to customize manually.

4. UI requirement

Build a single larger inspection result form per sample/product where sections are toggle-based.

Avoid hardcoded separate pages for every inspection type.

Do NOT create one giant messy form.
Use clean modular section blocks/components with proper UX.

Prefer:
Inspection Info
→ Product Info
→ Selected Sections
→ Section Details
→ Final Review

or a well-structured large modular form with collapsible sections.

5. Reporting system

PDF reports must be dynamic.

Only enabled sections should appear in report output.

Reports should follow international inspection report standards like QIMA-style reports:

* professional layout
* defect summary
* pass/fail verdict
* AQL acceptance logic
* inspection remarks
* photos
* approvals
* rejected quantity summary

Prefer:
One report per product/sample (SKU-level)

* optional master inspection summary

6. Technical expectations

Please analyze my current Laravel structure and propose:

* best database design improvements
* required migrations
* models and relationships
* scalable section architecture
* dynamic report generation strategy
* maintainable service layer design
* reusable Blade components
* clean admin UX
* future-safe architecture without overengineering

Avoid excessive plugin complexity or unnecessary abstraction.

I want practical enterprise-grade architecture, not theoretical overengineering.

Please review and implement/refactor accordingly with production-quality structure.
