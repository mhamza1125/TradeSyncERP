# TradeSyncERP — System Knowledge Base

This file is the persistent "project brain." It documents what the system is, what it knows, and how it works. Read it fully before any development task.

---

## 1. System Modules

| Module | Namespace | Description |
|---|---|---|
| Authentication | `Auth/` | Laravel Breeze login/register/password reset |
| User & Role Admin | `Admin/` | Manage users and Spatie permission roles |
| Master Data | `Masters/` | Customers, Employees, Suppliers, Currencies, Banks, Accounts, Expense Heads, Inspection Types, Defects, Colors, Sizes, Product Categories |
| Sample Management | `Operations/Samples` | Receive, store, and track physical product samples |
| Inspection Engine | `Operations/Inspections` | Run structured QC inspections with AQL sampling |
| Sample Movement | `Operations/Movements` | Track issuance and return of samples between parties |
| Customer Orders | `Operations/CustomerOrders` | Purchase orders linked to customers |
| Financial Accounting | `Finance/` | Double-entry transactions, expenses, transfers |
| Customer Invoicing | `Finance/CustomerInvoices` | Invoices with line items and multi-currency support |
| Customer Payments | `Finance/CustomerPayments` | Receipt tracking with FX gain/loss calculation |
| Salary Processing | `Finance/Salary` | Monthly salary runs with allowances, deductions, loans |
| Ledger Reports | `Reports/` | Cash, Bank, and Customer ledger views |
| Tools | `Tools/` | Standalone AQL calculator |
| Activity Log | (Spatie) | All model mutations tracked automatically |

---

## 2. Folder Structure

```
app/
├── Helpers/NumberToWords.php          — Numeric-to-words for financial docs
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                     — UserController, RoleController
│   │   ├── Auth/                      — Breeze controllers
│   │   ├── Finance/                   — Salary, Invoice, Payment, Expense, Transfer, AllowanceType
│   │   ├── Masters/                   — All reference data controllers
│   │   ├── Operations/                — Sample, Inspection, InspectionRun, Movement, CustomerOrder
│   │   ├── Reports/LedgerController
│   │   ├── Tools/AqlCalculatorController
│   │   ├── AttachmentController       — Polymorphic file upload/delete
│   │   ├── ActivityController
│   │   ├── DashboardController
│   │   └── ProfileController
│   └── Requests/
│       ├── Masters/                   — FormRequests for all master CRUD
│       ├── Operations/                — FormRequests for samples, inspections, movements
│       └── Finance/                   — FormRequests for salary, expenses, invoices, payments
├── Models/                            — 37 Eloquent models (see §4)
└── Services/
    └── Inspection/AqlCalculationService.php

database/
├── migrations/                        — All schema definitions (see §5)
└── seeders/                           — 15 seeders covering all reference data

resources/
└── views/
    ├── layouts/                       — App shell layout
    ├── components/                    — Reusable Blade components
    ├── auth/                          — Login, register, password flows
    ├── admin/                         — User & role views
    ├── masters/                       — CRUD views per master entity
    ├── operations/                    — Sample, inspection, movement views
    ├── finance/                       — Salary, invoices, payments, expense views
    ├── reports/                       — Ledger report views
    ├── exports/                       — DomPDF Blade templates
    ├── tools/                         — AQL calculator view
    └── partials/                      — Shared partials

routes/
├── web.php                            — All protected web routes
└── auth.php                           — Breeze auth routes
```

---

## 3. Key Business Workflows

### 3.1 Sample Lifecycle

1. Sample is received → `samples` record created (code, category, customer, supplier, storage location)
2. Variations recorded → `sample_variations` (color + size + quantity)
3. Gallery photos and documents uploaded → `attachments` (polymorphic, `attachable_type = App\Models\Sample`)
4. Sample status progresses: `Received` → `In Testing` → `Completed` / `Returned`
5. Overdue check: if `alert_days` elapsed since `receive_date` without status change, `isOverdue()` returns true

### 3.2 Inspection Lifecycle

1. **Inspection Type** defines the template: which `InspectionSection` records apply (via `inspection_type_section_defaults`), per product category if needed
2. **Inspection** record created: type, date, inspector(s) assigned, linked customer orders
3. **InspectionRun** created per sample: one run = one sample being inspected in one pass
4. For each run, **InspectionRunSection** records are created (one per section enabled by the type)
5. Each section has a `section_type` that controls the UI rendered and data format stored in the `data` JSON column:
   - `images` — photo gallery uploads
   - `aql` — links to `inspection_run_aql` record
   - `checklist` — array of checkbox items in JSON
   - `workmanship` — defect-by-defect recording
   - `container`, `verification`, `review`, `article_results`, `conclusion_section`, `general_info`, `checkpoint`, `production_stages` — structured JSON data
6. **AQL Section**: `AqlCalculationService` computes code letter, sample size, and AC/RE numbers from lot size and inspection level. Found defects (critical/major/minor) are compared to thresholds to produce a verdict
7. Run verdict recorded (`Pass`/`Fail`/`Pending`)
8. **PDF Export**: `InspectionExportController::exportRun()` generates a DomPDF report using `resources/views/exports/inspection-run-pdf.blade.php`

### 3.3 Double-Entry Accounting Flow

Every financial event follows this pattern:

```
Action              Debit Account         Credit Account
─────────────────────────────────────────────────────────
Record Expense      Expense Head Acct      Cash/Bank Acct
Pay Salary          Salary Expense Acct    Cash/Bank Acct
Receive Payment     Cash/Bank Acct         Customer Acct
Bank Transfer       Destination Acct       Source Acct
```

Implementation:
1. Create `Transaction` (debit_account_id, credit_account_id, amount, transaction_type)
2. Create child record (Expense / SalaryRun / CustomerPayment) with `transaction_id` FK

### 3.4 Customer Payment Flow (Multi-Currency)

Customer payments involve FX calculations:
- `invoiced_amount_fc` — amount on the invoice in foreign currency
- `deduction_fc` — deductions in foreign currency
- `received_fc` — actual FC received
- `exchange_rate` — rate used to convert to PKR
- `expected_pkr` = (`invoiced_amount_fc` - `deduction_fc`) × `exchange_rate`
- `actual_pkr_received` — what was physically received in PKR
- `pkr_gain_loss` = `actual_pkr_received` - `expected_pkr`
- `fc_gain_loss` = `received_fc` - (`invoiced_amount_fc` - `deduction_fc`)

### 3.5 Salary Run Flow

1. Create `SalaryRun` for a specific month (YYYY-MM, unique per month)
2. System populates `SalaryRunLine` records for each active employee
3. Admin adjusts: bonus, deduction, advance, allowances, leave days, loan deduction, late deduction
4. `net_payable` is a **stored virtual column** computed by the database (do not set it manually)
5. Optional `SalaryRunLineAllowance` records link additional allowance types per line
6. `SalaryRun::pay()` marks status as `Paid` and creates the Transaction record

### 3.6 Sample Movement Tracking

Two systems coexist:
- **Legacy**: `sample_movements` — one record per sample per move, direct FK to a single `assigned_to`
- **New grouped system**: `movements` (header) → `movement_items` (one per sample), with `movement_employees` pivot for the team receiving the samples. The new system is preferred for new work.

---

## 4. All Eloquent Models (37 Total)

### Authentication
| Model | Key Traits/Interfaces |
|---|---|
| `User` | HasRoles (Spatie), LogsActivity, Notifiable |

### Finance
| Model | Table | Notable |
|---|---|---|
| `Currency` | `currencies` | `is_default` bool; `exchange_rate` decimal:6 |
| `Bank` | `banks` | Linked to Account |
| `Account` | `accounts` | `account_type` enum: Cash/Bank; polymorphic attachments |
| `Transaction` | `transactions` | Double-entry: debit + credit account FKs |
| `Expense` | `expenses` | FK to transaction, account, expense_head |
| `ExpenseHead` | `expense_heads` | Self-referencing parent_id; `isCategory()` / `isSubcategory()` |
| `CustomerPayment` | `customer_payments` | Multi-currency with gain/loss fields |

### Salary
| Model | Table | Notable |
|---|---|---|
| `SalaryRun` | `salary_runs` | `month` unique (YYYY-MM); `isPaid()` |
| `SalaryRunLine` | `salary_run_lines` | `net_payable` is a DB-generated virtual column |
| `AllowanceType` | `allowance_types` | `active()` scope |
| `SalaryRunLineAllowance` | `salary_run_line_allowances` | Pivot-style: line → allowance type + amount |

### Master Data
| Model | Table | Notable |
|---|---|---|
| `Customer` | `customers` | SoftDeletes; BelongsToMany Supplier |
| `Supplier` | `suppliers` | SoftDeletes; BelongsToMany Customer |
| `Employee` | `employees` | SoftDeletes; gender/marital status enums |
| `EmployeeExperience` | `employee_experiences` | FK to employee |
| `ProductCategory` | `product_categories` | Linked to samples |
| `Brand` | `brands` | FK to customer |

### Sample Domain
| Model | Table | Notable |
|---|---|---|
| `Sample` | `samples` | SoftDeletes; priority_level enum; `isOverdue()` |
| `SampleColor` | `sample_colors` | Lookup |
| `SampleSize` | `sample_sizes` | Lookup |
| `SampleVariation` | `sample_variations` | color + size + quantity per sample |
| `SampleMovement` | `sample_movements` | Legacy movement (prefer new Movement model) |

### Inspection Domain
| Model | Table | Notable |
|---|---|---|
| `InspectionType` | `inspection_types` | `resolvedSectionsForCategory()` — category-aware section resolution |
| `InspectionSection` | `inspection_sections` | Library of section templates; `default_data` JSON |
| `InspectionTypeSectionDefault` | `inspection_type_section_defaults` | Links types to sections, optionally scoped to product category |
| `Inspection` | `inspections` | Header; BelongsToMany employees, customer orders |
| `InspectionRun` | `inspection_runs` | One run per sample; `hasSectionEnabled()`, `getSectionData()` |
| `InspectionRunSection` | `inspection_run_sections` | `data` JSON varies by `section_type`; unique on (run_id, section_id) |
| `InspectionRunAql` | `inspection_run_aql` | AQL thresholds + found counts + verdict; `calculateVerdict()` |
| `Defect` | `defects` | severity enum: critical/major/minor/functional |

### Customer Commerce
| Model | Table | Notable |
|---|---|---|
| `CustomerOrder` | `customer_orders` | SoftDeletes; BelongsToMany inspections |
| `CustomerOrderItem` | `customer_order_items` | FK to order and product category |
| `CustomerInvoice` | `customer_invoices` | SoftDeletes; FX fields; `isOverdue()` |
| `CustomerInvoiceItem` | `customer_invoice_items` | Line items with supplier/inspection type links |

### Movement (New System)
| Model | Table | Notable |
|---|---|---|
| `Movement` | `movements` | FK to inspection_run; BelongsToMany employees; `isOverdue()` |
| `MovementItem` | `movement_items` | FK to movement, sample, and variation; `effectiveStatus()` inherits from parent |

### Polymorphic
| Model | Table | Notable |
|---|---|---|
| `Attachment` | `attachments` | MorphTo `attachable`; `attachment_type` enum; `getUrlAttribute()` |

---

## 5. Database Schema Summary

### Enum Values Reference

| Field | Valid Values |
|---|---|
| `accounts.account_type` | `Cash`, `Bank` |
| `samples.priority_level` | `Low`, `Medium`, `High`, `Urgent` |
| `samples.status` | `Received`, `In Testing`, `Completed`, `Returned` |
| `customer_orders.status` | `Draft`, `Confirmed`, `Processing`, `Dispatched`, `Cancelled` |
| `customer_invoices.status` | `Draft`, `Sent`, `Partial`, `Paid`, `Overdue`, `Cancelled` |
| `salary_runs.status` | `Draft`, `Paid` |
| `movements.status` / `sample_movements.status` | `Issued`, `Returned`, `Overdue` |
| `inspections.overall_status` | `Pass`, `Fail`, `Pending` |
| `inspection_run_sections.status` | `pending`, `complete`, `na` |
| `inspection_sections.section_type` | `images`, `workmanship`, `aql`, `checklist`, `container`, `verification`, `review`, `article_results`, `conclusion_section`, `general_info`, `checkpoint`, `production_stages` |
| `employees.gender` | `Male`, `Female`, `Other` |
| `employees.marital_status` | `Single`, `Married`, `Divorced`, `Widowed` |
| `transactions.transaction_type` | `Expense`, `Salary`, `VendorPayment`, `CustomerReceipt`, `JournalEntry` |
| `defects.severity` | `critical`, `major`, `minor`, `functional` |
| `attachments.attachment_type` | `main_image`, `gallery`, `document`, `receipt`, `invoice`, `other` |

### Key Foreign Key Relationships

- `brands.customer_id` → `customers.id` (cascade delete)
- `accounts.bank_id` → `banks.id`
- `samples.category_id` → `product_categories.id`
- `samples.customer_id` / `supplier_id` → respective tables
- `sample_variations.sample_id` → `samples.id` (cascade delete)
- `inspection_type_section_defaults.(inspection_type_id, inspection_section_id)` → respective tables (cascade delete)
- `inspection_run_sections.(inspection_run_id, inspection_section_id)` → unique constraint
- `inspection_run_aql.inspection_run_id` → `inspection_runs.id`
- `transactions.(debit_account_id, credit_account_id)` → `accounts.id`
- `expenses.transaction_id` → `transactions.id`
- `salary_runs.transaction_id` → `transactions.id` (nullable until paid)
- `salary_run_lines.net_payable` — GENERATED STORED virtual column (do not insert/update)

### Pivot Tables

| Table | Links |
|---|---|
| `customer_supplier` | customers ↔ suppliers |
| `employee_inspection` | inspections ↔ employees |
| `inspection_customer_orders` | inspections ↔ customer_orders |
| `movement_employees` | movements ↔ employees |

---

## 6. AQL Calculation Service

File: `app/Services/Inspection/AqlCalculationService.php`

Implements ISO 2859-1 (ANSI/ASQ Z1.4) standard:

- **Input**: lot size, inspection level (I, II, III, S1–S4), AQL thresholds for critical/major/minor
- **Processing**: lot size → code letter → sample size → AC/RE numbers per AQL level
- **Output**: `sample_size`, `code_letter`, acceptance numbers, rejection numbers, `verdict` (Pass/Fail)
- Supported AQL levels: `0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5`
- `tableForJs()` exports the lookup tables as JSON for the frontend AQL calculator tool

---

## 7. Permissions & Roles

- Managed by Spatie Laravel Permission v6
- `User` model uses `HasRoles` trait
- Roles and permissions seeded by `RolesAndPermissionsSeeder`
- Route-level enforcement via Spatie middleware (applied in route groups in `web.php`)
- Initial admin user created by `AdminUserSeeder`

---

## 8. File Storage & Attachments

- All files stored on the `public` disk: `storage/app/public/`
- `php artisan storage:link` required for public access via `storage/` URL
- `Attachment` model is polymorphic — it can attach to: `Sample`, `Customer`, `Employee`, `Account`, `Transaction`, `CustomerInvoice`, `InspectionRunSection`
- `AttachmentController::store($type, $id)` handles upload; resolves model class from `$type` slug
- `Attachment::getUrlAttribute()` returns the public URL via `Storage::url($file_path)`
- DomPDF exports use embedded images — absolute storage paths required in PDF templates

---

## 9. PDF Export System

- Controller: `Operations\InspectionExportController`
  - `exportRun()` — single inspection run PDF
  - `bulkExport()` — multiple runs merged into one PDF
- Template: `resources/views/exports/inspection-run-pdf.blade.php`
- Finance export: `Finance\ExpenseController::exportPdf()` with its own Blade template
- DomPDF config: `config/dompdf.php` (or inline options in controller)
- Known constraint: image paths in PDF templates must use absolute filesystem paths (not public URLs)

---

## 10. Activity Logging

Models with `LogsActivity` trait: `User`, `Customer`, `Supplier`, `Employee`, `Sample`, `SampleMovement`, `Inspection`, `InspectionType`, `Movement`, `CustomerOrder`, `CustomerInvoice`, `Transaction`

- Logs are stored in `activity_log` table (Spatie)
- Accessible via `GET /activities` → `ActivityController::index()`
- By default logs `updated` and `created` events on the `fillable` attributes

---

## 11. Seeded Reference Data

| Seeder | What It Creates |
|---|---|
| `AdminUserSeeder` | One admin user |
| `RolesAndPermissionsSeeder` | All roles and permissions |
| `CurrenciesSeeder` | PKR (default), USD, and other currencies |
| `AccountsSeeder` | Default cash and bank accounts |
| `ExpenseHeadsSeeder` | Hierarchical expense categories |
| `SampleColorsSeeder` | Common color names |
| `SampleSizesSeeder` | Common size names |
| `AllowanceTypeSeeder` | HRA, DA, and other allowance types |
| `InspectionTypeSeeder` | Default inspection types (e.g., Pre-Production, Final) |
| `InspectionSectionSeeder` | Full section library with `default_data` JSON |
| `InspectionTypeSectionDefaultSeeder` | Wires types to their default sections |
| `DefectsSeeder` | Defect catalog with categories |
| `TestDataSeeder` | Realistic test data (customers, employees, samples, etc.) |

---

## 12. Frontend Stack

- **Blade** templating with component system (`resources/views/components/`)
- **TailwindCSS** for all styling — no custom CSS framework
- **Alpine.js** for reactive UI (modals, dropdowns, dynamic form rows)
- **Vite** as the build tool; assets compiled to `public/build/`
- **Axios** for AJAX calls (section saves, AQL calculations, file uploads)
- No Vue, React, or Inertia — this is a classic server-rendered Blade app with sprinkled Alpine interactivity
