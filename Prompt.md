## Customer Orders PDF Export Improvements

Please review and update the Customer Orders PDF export to match the current order structure and improve the overall layout.

### 1. Update Customer Order PDF Export

**Reference Pages:**

* View: `http://127.0.0.1:8000/customer-orders/2`
* Edit: `http://127.0.0.1:8000/customer-orders/2/edit`

#### Required Changes

* Review the current **Add/Edit Customer Order** form and use it as the source of truth for the PDF layout.
* The application no longer stores or displays **Unit Price** or **Total** values for order items.
* Remove the following columns from the PDF export:

  * Unit Price
  * Total
* Ensure the item table only contains fields that currently exist in the order model.
* Verify that all fields shown in the PDF correspond to the latest Customer Order schema and that no obsolete fields remain.
* Keep the PDF layout clean, well-aligned, and consistent with the current UI.

---

### 2. Simplify PDF Header

**File:** `_pdf-company-header.blade.php`

#### Required Changes

* Remove the following completely:

  * Report Title
  * Report Reference
* The header should contain **only the company logo**.
* Increase the logo height so the header looks more balanced after removing the extra content.
* Search the codebase and remove any logic, variables, or parameters that pass or render the report title and report reference, as they are no longer used.

---

### 3. Update PDF Footer

**File:** `_pdf-company-footer.blade.php`

#### Required Changes

* Restore page numbering in the footer.
* Display page numbers in the format:

  * `Page 1 of 3`
  * or `1 / 3` (whichever matches the existing PDF style).
* Ensure page numbering appears correctly on every page.
* Center-align the entire company information table in the footer.
* The footer currently contains a two-row company information table; both rows should be horizontally centered instead of left-aligned.

---

### 4. Optimize PDF Layout

Since the report title and reference have been removed:

* Reduce the unused whitespace between the header and the page content.
* Move the document content upward to better utilize the available page space.
* Adjust top margins and spacing where necessary while maintaining a professional appearance.
* Ensure the larger company logo does not introduce unnecessary blank space.

---

### Expected Result

* PDF reflects the current Customer Order data model.
* No obsolete columns or fields are displayed.
* Header contains only a larger company logo.
* Footer has centered company information and working page numbers.
* Content begins higher on the page with improved spacing.
* Remove all unused code related to report titles and report references to keep the implementation clean and maintainable.
