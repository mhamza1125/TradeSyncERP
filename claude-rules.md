# TradeSyncERP — Engineering Rules & Constraints

These are mandatory rules. Every rule must be followed exactly. No exceptions unless the user explicitly overrides one in the current session.

---

## 1. Architecture Rules

### 1.1 Controller Namespace Assignment

Controllers must live in the correct namespace. The assignment is strict:

| What it manages | Namespace |
|---|---|
| Reference / lookup data (no business logic) | `App\Http\Controllers\Masters\` |
| Operational business objects (samples, inspections, movements, orders) | `App\Http\Controllers\Operations\` |
| Financial records (invoices, payments, salary, expenses, transfers) | `App\Http\Controllers\Finance\` |
| User management, roles | `App\Http\Controllers\Admin\` |
| Cross-cutting utilities | `App\Http\Controllers\Tools\` or `App\Http\Controllers\Reports\` |

Never create a controller directly under `App\Http\Controllers\` unless it is a singleton (Dashboard, Profile, Activity, Attachment).

### 1.2 Service Layer

- Business logic that is not a simple DB read/write must go in `app/Services/`.
- The only existing service is `AqlCalculationService`. Follow its structure for new services.
- Controllers must not contain calculation logic, complex query building, or multi-step workflows.
- Services are plain PHP classes — no Laravel magic, no dependency injection beyond the constructor.

### 1.3 FormRequest Validation

- Every `store()` and `update()` action must use a dedicated `FormRequest` class.
- FormRequests live in `app/Http/Requests/` mirroring the controller namespace (`Masters/`, `Operations/`, `Finance/`).
- Do not use `$request->validate()` inline in controllers. This is forbidden.
- FormRequest `authorize()` must return `true` (authorization is handled by middleware, not FormRequests).

### 1.4 Routes

- All protected routes go in `routes/web.php` inside the existing `auth` middleware group.
- Use resource routes (`Route::resource`) for standard CRUD; add named additional routes explicitly.
- Route naming follows Laravel convention: `{resource}.index`, `{resource}.create`, `{resource}.store`, `{resource}.show`, `{resource}.edit`, `{resource}.update`, `{resource}.destroy`.
- Do not create API routes in `routes/api.php` for Blade-driven features.
- AJAX endpoints (save section, AQL calculate, file upload) use `POST` and return JSON responses.

---

## 2. Eloquent & Database Rules

### 2.1 Models

- Every model must declare `protected $fillable` — do not use `$guarded = []`.
- Enum-backed columns must list their valid values in a `const` or as a docblock on the model — do not leave enum values scattered only in migrations.
- Casts must be defined for: JSON columns (`array`), decimal columns (`decimal:N`), boolean columns (`boolean`), datetime columns (`datetime`).
- Do not add `$appends` without a corresponding `get{Name}Attribute()` accessor.
- Soft-deleted models (`SoftDeletes`) must scope queries appropriately — never accidentally expose trashed records in index views.

### 2.2 Relationships

- All relationships must be typed via PHPDoc `@return` or PHP 8 return types.
- BelongsToMany relationships must specify the pivot table name explicitly when it is not the Laravel-inferred default.
- Cascade deletes must be defined at the database level (migration `->onDelete('cascade')`), not only in Eloquent events.
- Do not use `->with('*')` or unbounded eager loading — always specify the exact relations needed.

### 2.3 Migrations

- Every migration must be reversible — implement `down()`.
- Enum columns: use `->enum('column', [...values])`. The complete list of valid values is the source of truth.
- Decimal financial columns: minimum `decimal(15, 2)`. For exchange rates: `decimal(15, 6)`.
- Every table must have `->timestamps()` unless there is a documented reason to omit it.
- Pivot tables: no `id()`, no `timestamps()`, define the composite primary key explicitly.
- Do not modify existing migrations — always create a new migration to alter a table.
- The `net_payable` column in `salary_run_lines` is a **GENERATED STORED** virtual column. Do not attempt to insert or update it.

### 2.4 Queries

- Use Eloquent query builder, not raw DB queries, unless performance requires it.
- All index controller actions must paginate results (`->paginate()`). Do not use `->get()` on potentially large tables in index actions.
- Filters in index actions (search, status, date range) must be applied as query builder conditions, not PHP array filtering.
- Never call `->all()` or `->get()` without a `->where()` on tables that grow unboundedly.

---

## 3. Financial Record Rules

### 3.1 Double-Entry Integrity

- Every financial event (expense, salary payment, customer receipt, bank transfer) **must** create a `Transaction` record.
- The child record (Expense, SalaryRun, CustomerPayment) stores `transaction_id` as a FK.
- Never create a child financial record without its transaction.
- `transaction_type` must match what is being recorded: `Expense` for expenses, `Salary` for salary runs, `CustomerReceipt` for payments, `JournalEntry` for transfers.

### 3.2 Currency Handling

- The home currency is PKR. All account balances are stored in PKR.
- Foreign currency values are stored alongside the exchange rate at the time of the transaction.
- Never convert currencies at display time using the current rate — always use the rate stored on the record.
- `exchange_rate` fields are `decimal(15, 6)` — do not truncate to fewer decimal places.

### 3.3 Salary Run

- `SalaryRun::status` transitions: `Draft` → `Paid`. It cannot be reversed.
- The `pay()` method creates the Transaction. Do not create salary transactions outside this method.
- `net_payable` in `SalaryRunLine` is a DB virtual column. Read it; do not write it.

---

## 4. Inspection System Rules

### 4.1 Section Types

- The `section_type` enum has 12 values. Each type controls what UI is rendered and what JSON structure is stored in `data`.
- Never add a new `section_type` without also updating every `switch`/`match` on `section_type` in Blade views and controllers.
- Section `data` is stored as JSON. The structure per section type must remain consistent — changing structure breaks existing inspection records.

### 4.2 AQL

- All AQL calculations must go through `AqlCalculationService`. Never replicate the lookup tables elsewhere.
- Accepted AQL levels are fixed by the ISO standard: `0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5`.
- Inspection levels: `I`, `II`, `III`, `S1`, `S2`, `S3`, `S4`.

### 4.3 Run Sections

- `InspectionRunSection` has a unique constraint on `(inspection_run_id, inspection_section_id)`. Do not attempt to insert duplicates.
- `status` transitions per section: `pending` → `complete` or `na`. Sections cannot go back to `pending` once completed.

---

## 5. File Upload Rules

- All file uploads go through `AttachmentController` or the dedicated `InspectionRunController::uploadAttachment()`.
- Store files on the `public` disk only.
- Always validate MIME type and file size in the FormRequest.
- After upload, create an `Attachment` record (do not store the path directly on the parent model, except for `samples.main_image` which is a legacy direct path column).
- For PDF templates (DomPDF), use absolute filesystem paths via `storage_path()`, not `Storage::url()`.

---

## 6. Naming Conventions

### Controllers & Classes

- Controller names: `{Entity}Controller` — singular, PascalCase.
- FormRequest names: `Store{Entity}Request` and `Update{Entity}Request`.
- Service names: `{Domain}{Concept}Service` (e.g., `AqlCalculationService`).

### Routes

- Route prefixes: lowercase kebab-case (`customer-orders`, `salary`, `expense-heads`).
- Route names: dot-notation matching the prefix (`customer-orders.index`, `salary.pay`).

### Database

- Table names: `snake_case`, plural (`inspection_run_sections`).
- Column names: `snake_case` (`debit_account_id`, `exchange_rate`).
- Foreign keys: `{singular_table_name}_id` (`customer_id`, `inspection_run_id`).
- Pivot table names: alphabetical order of the two entities (`customer_supplier`, not `supplier_customer`).

### Blade Views

- View paths mirror the controller namespace: `masters/customers/index.blade.php`, `finance/salary/show.blade.php`.
- Partials: prefix with underscore (`_form.blade.php`, `_row.blade.php`).
- Export templates: `exports/{entity}-{format}.blade.php` (`exports/inspection-run-pdf.blade.php`).

### Variables

- Blade variables: `$camelCase` for single models, `$pluralCamelCase` for collections.
- Controller-to-view compact: pass only what the view needs — no passing entire model lists when a subset suffices.

---

## 7. Frontend Rules

- Use **TailwindCSS** utility classes only — no custom CSS files.
- Use **Alpine.js** (`x-data`, `x-show`, `x-on`, `x-model`) for reactive UI. Do not add Vue or React.
- AJAX calls use **Axios** (already configured globally via `resources/js/bootstrap.js`). Do not use `fetch()` raw.
- CSRF: Axios automatically includes `X-XSRF-TOKEN` from the cookie. Do not manually attach CSRF headers.
- Blade components live in `resources/views/components/`. Use `<x-component-name />` syntax.
- Do not inline JavaScript longer than ~10 lines in Blade templates — extract to a `@push('scripts')` section or a dedicated JS file.

---

## 8. Forbidden Patterns

- **No raw SQL** via `DB::statement()` or `DB::select()` for business logic. Allowed only for migrations and performance-critical reporting.
- **No `$guarded = []`** on models — always use `$fillable`.
- **No inline `$request->validate()`** — always use FormRequest classes.
- **No unbounded `->get()` or `->all()`** on large tables in controller actions.
- **No `dd()` or `dump()` left in committed code**.
- **No Eloquent relationships defined via raw queries** — use the proper relation methods.
- **No financial amount calculation in Blade views** — amounts must be computed in the controller or model.
- **No direct `Transaction` creation in controllers** — the financial record (Expense, SalaryRun, CustomerPayment) is responsible for creating its transaction.
- **No modifying existing migrations** — always create a new migration for schema changes.
- **No `->with('*')` or `->withAll()`** — eager load only named relations.
- **No status fields updated outside of designated methods** (`SalaryRun::pay()`, `InspectionRunSection::isComplete()`, etc.).
- **No writing to `salary_run_lines.net_payable`** — it is a database-generated virtual column.

---

## 9. Soft Delete Rules

Models with `SoftDeletes`: `Customer`, `Supplier`, `Employee`, `Sample`, `CustomerOrder`, `CustomerInvoice`.

- Index queries on soft-deleted models must use `->whereNull('deleted_at')` implicitly (Eloquent handles this) — do not manually filter.
- `destroy()` actions call `->delete()` (soft), not `->forceDelete()`.
- Do not expose `->withTrashed()` in standard index views.
- Trashed records may still be FK targets — check before hard-deleting anything.

---

## 10. Error Handling & Responses

- AJAX endpoints (JSON) must return structured responses:
  ```json
  { "success": true, "message": "...", "data": {} }
  { "success": false, "message": "...", "errors": {} }
  ```
- Use HTTP status codes correctly: `200` for success, `422` for validation errors, `403` for permission errors, `404` for not found.
- Do not catch exceptions silently — either rethrow or log and return a user-visible error.
- Flash messages for web redirects: use `->with('success', '...')` and `->with('error', '...')` conventions.
