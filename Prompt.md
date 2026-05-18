1. http://127.0.0.1:8000/samples/1/edit
We should be able to add / edit colors and sizes by ourselves. Currently there is no sidebar navigaiton for that. 

2. http://127.0.0.1:8000/inspections/1
Display images more effectively, Currently these are too small to work with. We have to click and they open up in the new tab where we can see them. Also we can't delete attachment image in "Could not delete attachment." here http://127.0.0.1:8000/inspections/1/runs/2/edit. 
That would be better if you add separte row for hte image rather than putting these in the column. 


Inspection dates, report dates, Some inspecitons run multiple days

TS-0526-70D-AMS/364
70D is customer
AMS IS supplier
364 is auto generated id

In invoice the euro and total column is the same. We can remove one of them.

25th date, 30% defined salary of that month
Services add ons. into the salary
50k salary 5k pertrol 2k packsage etc. 

Seprate format for each type of inspection

========================================================================================================

Here's a clean, well-structured prompt you can paste into Claude Code:

---

**Project Refactoring Tasks — Full Specification**

I need you to refactor this project according to the following specifications. Please analyze the existing codebase first, then implement each change:

---

**1. Merge Brand into Customer**

- Remove the `brands` module/table entirely
- Add a `brand` text field directly to the `customers` table
- Remove all foreign key relationships between brands and customers
- Update all references, forms, views, and API endpoints accordingly
- The customer IS the brand — no separate entity needed

---

**2. Rework the Sample Module**

- Create two new tables: `sample_colors` and `sample_sizes`
- A sample (article) has general details at the top level, plus multiple color/size/quantity variation rows linked to it
- Update the sample creation form: general details at the top, then a dynamic rows section below where the user selects color + size from dropdowns and enters quantity per variation
- The quantity per variation should be usable/moveable in the inspection module

---

**3. Update the Customer Order Module**

- Remove `product_name` and `units` fields from orders
- Replace with `product_category` (dropdown/select) + `quantity` (number)
- Rename the frontend label `required_by_date` → `ETD` (backend field name stays the same)

---

**4. Rework Customer Invoice**

- Remove `quantity` from invoice line items
- Each invoice row (description) consists of 4 fields: `Supplier` (select) + `Inspection Type` (select) + `PO/Invoice No` (text input) + `Date` (date picker), plus an `Amount` field
- Remove the Foreign Currency Details section entirely
- Add `currency` field to the `customers` table
- When a customer is selected in invoice creation (and other invoice views), display their currency automatically

---

**5. Salary Runs — Leave Deductions**

- Add the ability to record deductible leave days within a salary run entry
- Include a field for the deduction amount (manually entered by the user)

---

**6. Fully Rework Sample Movement & Inspection**

Replace the current single-record inspection/movement with the following hierarchy:

```
Inspection (top-level entry)
  ├── Linked to one or more Customer Orders
  ├── Assigned Employees (multi-select via checkboxes)
  ├── Select Samples (This is what we will go and test in supplier factory)
  └── Inspection Runs (multiple per inspection)
        ├── inspection_type
        ├── Sample Movement record (if a sample was taken)
        └── Inspection Results
              ├── status (dropdown — replaces the redundant pass/fail + status combo)
              └── If rejected → select Defect from dropdown
```

- Build the Inspection creation UI like an order/invoice form
- Allow multiple inspection runs per inspection entry
- For inspection results: remove the separate pass/fail field, keep only the `status` dropdown; if status is "Rejected", show a defect selector

**Defects Table:**
Create a `defects` table with fields: `defect_name`, `corrective_action`. Seed it with:

| Defect | Corrective Action |
|---|---|
| Arm Hole Wrinkle | Open arm hole and inner side stitching & re stitch to remove the wrinkle |
| Belt Loop Uneven | Open one loop and then reattach both loops together with proper alignment |
| Back Pocket Position Uneven | Open pocket stitching and reattach (restitch) at the correct position |

Use this defects table as the dropdown source in inspection results.

---

**General instructions:**
- Analyze the full codebase before starting
- Make all necessary migration files
- Seed the defects table
- Update all affected forms, views, API routes, and controllers
- Flag anything ambiguous before implementing

---

This gives Claude Code clear scope, hierarchy, and enough detail to avoid guessing. If your project uses a specific stack (Laravel, Next.js, etc.), prepend that to the prompt too.