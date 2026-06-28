# TradeSyncERP - UI, Activity Log, PDF Export & Sample Management Improvements

Please implement the following improvements carefully. Before making changes, review the existing implementation to ensure consistency across the application. Preserve the current design language wherever possible.

---

# 1. Activity Log Improvements

**URL**
`/activities`

## Improve Activity Listing

The current activity list is not meaningful enough.

### Subject Column

* Replace the current subject format (which only displays the model name and record ID).
* Make it human-readable.

Example:

Instead of:

> CustomerOrder #12 updated

Display something like:

> Customer Order CO-2026-00125 was updated

or

> Customer "ABC Textiles" was created

or

> Sample SM-00045 was moved to Testing Department

Use the most meaningful identifier available for each model instead of the database ID.

---

## Improve Change Display

Currently only updated column names are shown.

Instead:

Display a short summary, for example:

* Updated Delivery Date
* Changed Customer
* Updated Payment Status
* Modified Quantity and Unit Price

---

## Activity Details Page

Create a dedicated details page for every activity.

Example:

`/activities/{id}`

This page should contain:

* Activity type
* User
* Date & Time
* IP (if available)
* Model
* Record reference
* Event
* Description

For update events, show two separate sections:

### Before

```json
{
  "status": "Pending",
  "required_date": "2026-06-20"
}
```

### After

```json
{
  "status": "Approved",
  "required_date": "2026-06-28"
}
```

Present these in a clean JSON viewer or side-by-side comparison.

---

## Activity Cleanup

Implement automatic cleanup for old activity logs.

Choose the most suitable strategy.

Recommended:

* Keep last 12 months
* Scheduled cleanup using Laravel Scheduler
* Configurable retention period

---

# 2. Customer Orders - PDF Export Improvements

**URL**

`/customer-orders`

Update the print/PDF export.

## Rename Labels

Replace:

* Delivery Date

With:

* Required Date

---

Replace every occurrence of:

ETD

with

Required Date

throughout:

* Create Order
* Edit Order
* Print
* PDF
* View pages

---

## Remove Unnecessary Header/Footer Content

Remove:

* Customer Orders Confidential
* Customer Orders footer text
* Generated timestamp (e.g. "28 Jun 2026, 17:33")
* "TradeSyncERP Quality Control & ERP System"

The logo alone is sufficient.

Center-align the logo.

---

## Footer Improvements

Center-align the footer content.

Fix page numbering.

Current:

Page 1 of 0

Correct:

Page 1 of X

where X is the total exported pages.

This issue should be fixed across **all PDF exports**.

---

# 3. Customer Order Details PDF

**URL**

`/customer-orders/{id}`

Apply all header/footer improvements described above.

---

## Required Date

The view page correctly displays Required Date.

However, it is missing from the exported PDF.

Review this export and ensure that every PDF accurately reflects its corresponding View page, including:

* labels
* field names
* displayed values
* layout consistency

---

## Requested Items Table

Rename:

Product Name

to

Product Category

because the stored value is actually the selected product category.

Remove the unused:

Unit

column.

---

# 4. Sample Edit Error

**URL**

`/samples/7/edit`

Fix:

```
Call to undefined relationship [testingParameters] on model [App\Models\Sample]
```

Location:

```
app/Http/Controllers/Operations/SampleController.php
```

Problematic code:

```php
$sample->load(
    'variations.color',
    'variations.size',
    'testingParameters.parameter',
    'attachments'
);
```

Investigate whether:

* the relationship was renamed,
* removed,
* or never implemented.

Update the controller to use the correct relationship.

---

# 5. Sample Movement Edit Page

**URL**

`/movements/{id}/edit`

The Edit page does not match the Create page.

Update the edit screen so both pages have:

* identical layout
* same field ordering
* same sections
* same validation behaviour
* same UI components

---

# 6. Sample Status Redesign

**URL**

`/samples`

Remove the manual Priority field entirely.

Redesign Sample Status so it is fully automatic.

## Business Rules

The Sample status should be derived from Sample Movements.

Suggested statuses:

### In Testing

Display when at least one Sample Movement is still open (sample has been sent but not fully received back).

### Received

Display when there are no pending Sample Movements and every moved sample has been received back.

Do not allow manual editing of Sample Status.

Instead:

Sample Movements become the single source of truth.

Ensure Sample Movement supports proper lifecycle states such as:

* Open
* Returned
* Closed

The Sample Status should automatically update whenever movement status changes.

---

# 7. Sample Edit Error

**URL**

`/samples/9/edit`

Same error as item #4.

Fix:

```
Call to undefined relationship [testingParameters]
```

---

# 8. Customer Payments Table

**URL**

`/customer-payments`

Update the table layout.

Requirements:

* Swap the positions of:

  * Customer
  * Invoice Ref

* Make Invoice Ref clickable.

It should open the Payment View page.

* Remove the letter/avatar icon from the Customer column.

* Remove the hyperlink from the Customer name.

Use the Customer Invoices table as the reference implementation:

`/customer-invoices`

---

# 9. Customers Master List

**URL**

`/masters/customers`

Display the customer's assigned currency in the listing table.

The currency relationship already exists but is not currently shown.

Add an appropriate Currency column.

---

# 10. Sidebar Behaviour

**URL**

Entire application

Improve sidebar usability.

When navigating between pages, preserve the sidebar scroll position so that the currently active menu item remains visible instead of resetting to the top.

Implement this in the most reliable and lightweight way possible.

---

# General Requirements

* Do not introduce breaking changes.
* Maintain the existing UI style and component library.
* Review related modules to ensure consistency across the application.
* Verify that all PDF exports follow the same header, footer, spacing, typography, and page numbering conventions.
* Test each change after implementation.
* Ensure there are no new console errors, PHP warnings, or Laravel exceptions.
* Refactor duplicated logic where appropriate instead of applying one-off fixes.
