# TradeSyncERP — AI Project Context

## Mandatory Reading

Before starting any task in this repository, you **MUST** read and internalize:

1. [`claude-memory.md`](claude-memory.md) — System knowledge base (models, modules, workflows, DB schema)
2. [`claude-rules.md`](claude-rules.md) — Strict engineering rules and constraints

Do not write a single line of code until you have read both files. Violations of `claude-rules.md` will require rework.

---

## Project Overview

**TradeSyncERP** is a multi-module ERP system built for quality control, sample management, financial accounting, and human resources — designed for textile/garment sourcing companies. It handles the full lifecycle from receiving physical product samples, through inspection workflows, to customer invoicing and salary processing.

The system is used internally by a team of inspectors, finance staff, and administrators. All data is multi-currency (PKR as home currency, USD and others as foreign). The inspection engine implements the ISO 2859-1 AQL sampling standard.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12, PHP 8.2+ |
| Frontend | Blade + TailwindCSS + Alpine.js |
| Build Tool | Vite |
| Database | SQLite (default) / MySQL compatible |
| Auth | Laravel Breeze |
| Permissions | Spatie Laravel Permission v6 |
| Activity Log | Spatie Activity Log v4 |
| PDF Export | Laravel DomPDF v3 |
| File Storage | Laravel public disk |

---

## High-Level Architecture

### Module Namespaces

```
app/Http/Controllers/
├── Auth/          — Breeze authentication
├── Admin/         — User and role management
├── Masters/       — Reference/lookup data (CRUD-heavy)
├── Operations/    — Core business operations (Samples, Inspections, Movements)
├── Finance/       — Accounting, invoicing, salary, payments
├── Reports/       — Ledger views
└── Tools/         — Utilities (AQL calculator)
```

### Request Lifecycle

- All web routes require `auth` + `verified` middleware
- Access control is enforced via Spatie permission middleware on route groups
- Form data is validated through dedicated FormRequest classes (`app/Http/Requests/`)
- Business logic beyond basic CRUD lives in `app/Services/`
- File uploads are stored to `storage/app/public/` via the public disk, with metadata in the `attachments` table (polymorphic)
- PDF exports are generated via DomPDF using Blade templates in `resources/views/exports/`
- Activity logging happens automatically on models that use the `LogsActivity` trait

### Double-Entry Accounting

Every financial event (expense, salary payment, customer payment, bank transfer) creates a `Transaction` record with a `debit_account_id` and `credit_account_id`. The child record (Expense, SalaryRun, CustomerPayment) holds the FK `transaction_id`. This means financial records cannot exist without a transaction record.

### Inspection Hierarchy

```
InspectionType (defines which sections apply)
  └── Inspection (a specific job/assignment)
        └── InspectionRun (one run = one sample inspected once)
              └── InspectionRunSection (one section per step: images, AQL, checklist, etc.)
                    └── Attachment (photos and documents uploaded per section)
```

---

## AI Behavior in This Project

### Core Directives

- Always read `claude-memory.md` first. It contains the authoritative list of models, tables, and workflows.
- Always read `claude-rules.md` before writing code. The rules are non-negotiable.
- Do not invent features, patterns, or conventions not already established in the codebase.
- When uncertain about where code belongs, follow the existing namespace pattern (Masters/Operations/Finance).

### Feature Work

- For new CRUD modules: follow the Masters namespace pattern (Controller → FormRequest → Resource routes → Blade views with index/create/edit/show).
- For new financial flows: always create a Transaction record first, then the child record.
- For inspection/section changes: consult the section_type enum — do not add section types without also updating any switch/match logic that dispatches on `section_type`.

### Debugging

- Check `spatie_activity_log` table for who changed what and when.
- Check that every financial record has a corresponding `transactions` row; missing transactions indicate incomplete flows.
- AQL calculation logic lives exclusively in `app/Services/Inspection/AqlCalculationService.php`.

### What NOT to Do

- Do not add raw SQL queries; use Eloquent.
- Do not write migrations that use unsupported column types for SQLite.
- Do not skip FormRequest validation classes — inline `$request->validate()` is not used in this project.
- Do not generate or expose API routes unless the user explicitly requests an API; this is a server-rendered Blade application.
