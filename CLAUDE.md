# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> **Deep references**: [`claude-memory.md`](claude-memory.md) has the full model inventory, DB schema, enum values, and all business workflows. [`claude-rules.md`](claude-rules.md) has exhaustive engineering rules. Read both before any non-trivial task.

---

## Commands

```bash
# Full setup from scratch
composer run setup          # install deps, key:generate, migrate, npm install & build

# Dev server (runs all four concurrently: Laravel, queue, pail log viewer, Vite HMR)
composer run dev

# Individual processes
php artisan serve
npm run dev                 # Vite HMR only

# Assets
npm run build               # production build → public/build/

# Testing
composer run test           # config:clear then php artisan test
php artisan test --filter=ExampleTest   # single test
php artisan test tests/Feature/Finance/ # single folder

# Code style
./vendor/bin/pint           # Laravel Pint (PSR-12)

# Database
php artisan migrate
php artisan migrate:fresh --seed   # full reset with all seeders
php artisan db:seed --class=TestDataSeeder   # realistic test data only

# Storage link (required after fresh install)
php artisan storage:link
```

---

## Architecture

### Controller Namespaces (strict)

| Namespace | Manages |
|---|---|
| `Masters\` | Reference/lookup data — Customers, Suppliers, Employees, Banks, Accounts, Currencies, Expense Heads, Colors, Sizes, Product Categories, Defects, Inspection Types |
| `Operations\` | Core business objects — Samples, Inspections, InspectionRuns, InspectionSections, Movements, CustomerOrders |
| `Finance\` | Financial records — Expenses, SalaryRuns, CustomerInvoices, CustomerPayments, Transfers, AllowanceTypes |
| `Reports\` | Ledger views |
| `Admin\` | Users and roles |
| `Tools\` | Standalone utilities (AQL calculator) |
| *(root)* | Singletons only: Dashboard, Profile, Activity, Attachment |

### Request Lifecycle

1. Route hits `auth` + `verified` middleware → Spatie permission middleware on route groups
2. `store()`/`update()` actions always use a **`FormRequest`** in `app/Http/Requests/{Namespace}/`
3. Business logic beyond simple CRUD lives in `app/Services/` (plain PHP classes)
4. Blade views in `resources/views/{namespace}/{entity}/`; PDF exports in `resources/views/exports/`
5. File uploads → `AttachmentController` → `public` disk → `attachments` table (polymorphic)

### Double-Entry Accounting

Every financial event creates a `Transaction` first, then the child record:

```
Action              Debit Account         Credit Account        transaction_type
────────────────────────────────────────────────────────────────────────────────
Record Expense      Expense Head Acct     Cash/Bank Acct        Expense
Pay Salary          Salary Expense Acct   Cash/Bank Acct        Salary
Receive Payment     Cash/Bank Acct        Customer Acct         CustomerReceipt
Bank Transfer       Destination Acct      Source Acct           JournalEntry
```

Child records (`Expense`, `SalaryRun`, `CustomerPayment`) store `transaction_id`. A child **cannot** exist without its transaction. Never create `Transaction` directly in a controller — the financial model owns that responsibility.

### Inspection Hierarchy

```
InspectionType  →  defines template sections (via inspection_type_section_defaults)
  └── Inspection        (job/assignment; BelongsToMany employees + customer_orders)
        └── InspectionRun     (one sample, one pass)
              └── InspectionRunSection   (one per section; data JSON keyed by section_type)
                    └── Attachment        (photos per section)
```

`section_type` has **12 fixed values**: `images`, `workmanship`, `aql`, `checklist`, `container`, `verification`, `review`, `article_results`, `conclusion_section`, `general_info`, `checkpoint`, `production_stages`. Every `switch`/`match` on `section_type` in Blade and controllers must handle all 12. Adding a new one requires updating all dispatch sites.

AQL calculations flow exclusively through `app/Services/Inspection/AqlCalculationService.php` (ISO 2859-1). Never replicate the lookup tables.

### Salary Run

`SalaryRun` status is `Draft` → `Paid` (irreversible). `SalaryRun::pay()` is the only place that creates the salary `Transaction`. `salary_run_lines.net_payable` is a **DB-generated STORED virtual column** — never insert or update it.

### Movement System

Two coexisting systems: `sample_movements` (legacy, direct FK) and `movements`/`movement_items` (new, grouped with `movement_employees` pivot). Prefer the new system for any new work.

---

## Non-Negotiable Rules

**Models**
- Always declare `$fillable` — never `$guarded = []`
- Cast JSON columns to `array`, decimals to `decimal:N`, booleans to `boolean`
- Financial decimal columns: `decimal(15,2)` minimum; exchange rates: `decimal(15,6)`

**Controllers**
- No inline `$request->validate()` — always use a FormRequest class
- Index actions must paginate (`->paginate()`), never `->get()` on unbounded tables
- No `dd()` or `dump()` in committed code
- No financial amount calculation in Blade views

**Migrations**
- Always implement `down()`; never modify existing migrations — create a new one
- Pivot tables: no `id()`, no `timestamps()`, define composite PK explicitly

**Frontend**
- TailwindCSS utility classes only — no custom CSS files
- Alpine.js for reactive UI — no Vue/React
- AJAX via Axios (globally configured) — not `fetch()`
- JS longer than ~10 lines in Blade → `@push('scripts')` or dedicated JS file

**Routes**
- Protected routes in `routes/web.php` inside `auth` middleware group
- Use `Route::resource()` for CRUD; AJAX endpoints use `POST` + return JSON
- No `routes/api.php` routes for Blade-driven features

**PDF exports**
- Use absolute filesystem paths (`storage_path()`) in DomPDF Blade templates, not `Storage::url()`

**AJAX JSON responses**
```json
{ "success": true,  "message": "...", "data": {} }
{ "success": false, "message": "...", "errors": {} }
```

---

## Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| Controllers | `{Entity}Controller` | `CustomerInvoiceController` |
| FormRequests | `Store{Entity}Request`, `Update{Entity}Request` | `StoreExpenseRequest` |
| Services | `{Domain}{Concept}Service` | `AqlCalculationService` |
| Route prefixes | kebab-case | `customer-orders`, `expense-heads` |
| Route names | dot-notation matching prefix | `customer-orders.index` |
| Blade views | mirror controller namespace | `finance/salary/show.blade.php` |
| Blade partials | underscore prefix | `_form.blade.php` |
| Export templates | `exports/{entity}-{format}.blade.php` | `exports/inspection-run-pdf.blade.php` |
| Blade variables | camelCase singular / plural | `$salaryRun`, `$salaryRuns` |

---

## Key Debugging Pointers

- **Who changed what**: query `activity_log` table (Spatie) or `GET /activities`
- **Broken financial flow**: check that every `expenses`/`salary_runs`/`customer_payments` row has a non-null `transaction_id`
- **AQL issues**: all logic is in `AqlCalculationService` — check the ISO lookup tables there
- **Missing files in browser**: run `php artisan storage:link`
- **PDF image not rendering**: template is using `Storage::url()` instead of `storage_path()`
