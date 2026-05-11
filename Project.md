# ERP System — Full Build Prompt for Claude Code

## Project Overview
Build a complete Quality Testing, Sample Tracking, and Financial Management ERP system using Laravel. The system manages product sample testing workflows, inspection reports, employee/vendor coordination, financial transactions, and multi-currency customer payments.

## Already Installed (Do NOT reinstall)
- `spatie/laravel-permission` — roles & permissions
- `spatie/laravel-activitylog` — audit logging (use this for all model change tracking)
- Laravel Breeze — authentication scaffolding

---

## Roles & Permissions

Four roles. Set these up via a seeder using spatie/laravel-permission:

- **Admin** — full access to everything
- **Lab Manager** — full access to samples, inspections, movements, reports. Read-only on finance.
- **Accountant** — full access to all financial modules. Read-only on samples/inspections.
- **Employee** — read-only on assigned samples and their own movement records only

Use `spatie/laravel-permission` middleware on all route groups. Seed default roles and one default admin user.

---

## Database Schema

### MASTER TABLES

#### 1. customers
- id (PK)
- customer_name (varchar)
- contact_person (varchar)
- phone (varchar)
- email (varchar)
- address (text)
- default_currency (varchar, e.g. EUR, USD, GBP — default PKR)
- opening_balance (decimal 15,2 — in their default currency)
- opening_balance_currency (varchar)
- status (boolean, default true)
- created_at, updated_at

#### 2. brands
- id (PK)
- customer_id (FK → customers)
- brand_name (varchar)
- remarks (text, nullable)
- status (boolean, default true)
- created_at, updated_at

#### 3. product_categories
- id (PK)
- category_name (varchar) — e.g. Shoes, Garments, Bags
- status (boolean, default true)
- created_at, updated_at

#### 4. employees
- id (PK)
- employee_name (varchar)
- department (varchar)
- designation (varchar)
- phone (varchar)
- joining_date (date)
- basic_salary (decimal 15,2)
- status (boolean, default true)
- created_at, updated_at

#### 5. vendors
- id (PK)
- vendor_name (varchar)
- company_name (varchar)
- phone (varchar)
- email (varchar, nullable)
- address (text, nullable)
- payment_terms (varchar, nullable)
- opening_balance (decimal 15,2, default 0)
- status (boolean, default true)
- created_at, updated_at

#### 6. testing_parameters_master
- id (PK)
- category_id (FK → product_categories)
- parameter_name (varchar) — e.g. Sole Strength, Color Fastness
- description (text, nullable)
- status (boolean, default true)
- created_at, updated_at

#### 7. accounts
- id (PK)
- account_name (varchar) — e.g. Main Cash, HBL Bank, Petty Cash
- account_type (enum: Cash, Bank, Ledger)
- currency (varchar, default PKR)
- opening_balance (decimal 15,2, default 0)
- status (boolean, default true)
- created_at, updated_at

#### 8. expense_heads
- id (PK)
- expense_name (varchar) — e.g. Rent, Electricity, Petrol, Maintenance
- status (boolean, default true)
- created_at, updated_at

---

### CORE OPERATION TABLES

#### 9. samples
- id (PK)
- sample_code (varchar, UNIQUE) — auto-generated format: SMP-YYYY-XXXXX
- category_id (FK → product_categories)
- customer_id (FK → customers)
- brand_id (FK → brands)
- product_name (varchar)
- shipment_reference (varchar, nullable)
- receive_date (date)
- quantity (int)
- priority_level (enum: Low, Medium, High, Urgent)
- alert_days (int, default 7 — days before due to trigger alert)
- status (enum: Received, In Testing, Completed, Returned)
- remarks (text, nullable)
- created_at, updated_at

#### 10. sample_testing_parameters
- id (PK)
- sample_id (FK → samples)
- parameter_id (FK → testing_parameters_master)
- requirement_standard (varchar, nullable) — e.g. ISO 105-B02
- remarks (text, nullable)
- created_at, updated_at

#### 11. sample_movements
- id (PK)
- sample_id (FK → samples)
- moved_by_type (varchar) — 'Employee' or 'User'
- moved_by_id (int) — polymorphic: ID from employees or users table
- assigned_to_type (enum: Employee, Vendor, Storage, Customer)
- assigned_to_id (int) — ID from the relevant table based on type
- issue_date (date)
- expected_return_date (date, nullable)
- actual_return_date (date, nullable)
- alert_days (int, nullable)
- status (enum: Issued, Returned, Overdue)
- remarks (text, nullable)
- created_at, updated_at

#### 12. inspections
- id (PK)
- sample_id (FK → samples)
- report_number (varchar, UNIQUE) — auto-generated: RPT-YYYY-XXXXX
- inspection_date (date)
- inspector_type (enum: Employee, Vendor)
- inspector_id (int) — ID from employees or vendors table based on inspector_type
- overall_status (enum: Pass, Fail, Pending)
- remarks (text, nullable)
- created_at, updated_at

#### 13. inspection_results
- id (PK)
- inspection_id (FK → inspections)
- sample_testing_parameter_id (FK → sample_testing_parameters)
- actual_result (varchar)
- pass_fail (enum: Pass, Fail)
- remarks (text, nullable)
- attachment (varchar, nullable — file path)
- created_at, updated_at

---

### VENDOR BILL TABLES

#### 14. vendor_bills
- id (PK)
- vendor_id (FK → vendors)
- bill_number (varchar, UNIQUE) — auto-generated: BILL-YYYY-XXXXX
- bill_date (date)
- due_date (date, nullable)
- total_amount (decimal 15,2 — auto-calculated as sum of line items)
- status (enum: Unpaid, Paid, Partial, Overdue)
- remarks (text, nullable)
- transaction_id (FK → transactions, nullable — null until paid)
- created_at, updated_at

#### 15. vendor_bill_items
- id (PK)
- vendor_bill_id (FK → vendor_bills)
- description (text)
- quantity (decimal 10,3)
- unit (varchar, nullable) — e.g. pcs, kg, hrs
- unit_price (decimal 15,2)
- line_total (decimal 15,2 — stored computed: quantity × unit_price)
- created_at, updated_at

#### 16. vendor_bill_inspections (bridge table — optional links)
- id (PK)
- vendor_bill_id (FK → vendor_bills)
- inspection_id (FK → inspections)
- created_at, updated_at
- UNIQUE constraint on (vendor_bill_id, inspection_id)

---

### FINANCIAL TABLES

#### 17. transactions
This is the single source of truth for ALL financial activity. Every payment, expense, salary, and receipt creates exactly one transaction record.

- id (PK)
- transaction_date (date)
- transaction_type (enum: Expense, Salary, VendorPayment, CustomerReceipt, JournalEntry)
- reference_type (varchar, nullable) — e.g. 'expense', 'salary_run', 'vendor_bill', 'customer_payment'
- reference_id (int, nullable) — ID of the linked record
- debit_account_id (FK → accounts)
- credit_account_id (FK → accounts)
- amount (decimal 15,2)
- remarks (text, nullable)
- attachment (varchar, nullable)
- created_by (FK → users)
- created_at, updated_at

#### 18. expenses
- id (PK)
- expense_head_id (FK → expense_heads)
- account_id (FK → accounts — which account was debited)
- transaction_id (FK → transactions)
- amount (decimal 15,2)
- expense_date (date)
- description (text, nullable)
- attachment (varchar, nullable)
- created_at, updated_at

#### 19. salary_runs
One record per month representing a full salary batch for all employees.

- id (PK)
- month (varchar) — format: YYYY-MM e.g. 2025-01
- account_id (FK → accounts — bank/cash account to pay from)
- total_net_payable (decimal 15,2 — sum of all lines)
- status (enum: Draft, Paid)
- payment_date (date, nullable)
- transaction_id (FK → transactions, nullable — created when marked Paid)
- processed_by (FK → users)
- created_at, updated_at
- UNIQUE constraint on (month)

#### 20. salary_run_lines
One row per employee in a salary run. Auto-populated from employees table but fully editable before payment.

- id (PK)
- salary_run_id (FK → salary_runs)
- employee_id (FK → employees)
- basic_salary (decimal 15,2 — pre-filled from employee record, editable)
- bonus (decimal 15,2, default 0)
- deduction (decimal 15,2, default 0)
- advance (decimal 15,2, default 0)
- net_payable (decimal 15,2 — computed: basic_salary + bonus - deduction - advance)
- remarks (text, nullable)
- created_at, updated_at
- UNIQUE constraint on (salary_run_id, employee_id)

#### 21. customer_payments
Multi-currency payment tracking. The customer's ledger is always in their default foreign currency. PKR conversion is tracked separately.

- id (PK)
- customer_id (FK → customers)
- transaction_id (FK → transactions)
- payment_date (date)
- invoice_reference (varchar, nullable)
- foreign_currency (varchar) — e.g. EUR, USD
- invoiced_amount_fc (decimal 15,2) — full amount customer owed, clears their ledger
- deduction_fc (decimal 15,2, default 0) — bank/transfer fee in foreign currency
- received_fc (decimal 15,2) — auto-calculated: invoiced_amount_fc - deduction_fc
  (if user enters received_fc, deduction_fc is auto-calculated instead)
- exchange_rate (decimal 15,6) — rate used for PKR conversion
- expected_pkr (decimal 15,2) — auto-calculated: received_fc × exchange_rate
- actual_pkr_received (decimal 15,2) — what actually arrived in account
- pkr_gain_loss (decimal 15,2) — auto-calculated: actual_pkr_received - expected_pkr
- fc_gain_loss (decimal 15,2) — auto-calculated: invoiced_amount_fc - received_fc (same as deduction_fc but signed)
- account_id (FK → accounts — which bank/cash received the PKR)
- remarks (text, nullable)
- created_at, updated_at

**Auto-calculation rules (enforce in both frontend JS and backend):**
- received_fc = invoiced_amount_fc - deduction_fc
- deduction_fc = invoiced_amount_fc - received_fc (if received_fc entered first)
- expected_pkr = received_fc × exchange_rate
- pkr_gain_loss = actual_pkr_received - expected_pkr
- fc_gain_loss = invoiced_amount_fc - received_fc

---

### SYSTEM TABLES

#### 22. users (managed by Breeze — extend only)
- id (PK)
- name (varchar)
- email (varchar, UNIQUE)
- password (varchar, hashed)
- status (boolean, default true)
- created_at, updated_at
- Roles assigned via spatie/laravel-permission (no role_id column needed)

---

## Relationships Summary

- customers → brands (one to many)
- product_categories → testing_parameters_master (one to many)
- samples → customers, brands, product_categories (many to one each)
- samples → sample_testing_parameters (one to many)
- samples → sample_movements (one to many)
- samples → inspections (one to many)
- inspections → inspection_results (one to many)
- sample_testing_parameters → inspection_results (one to many)
- vendor_bills → vendor_bill_items (one to many)
- vendor_bills ↔ inspections via vendor_bill_inspections (many to many, optional)
- salary_runs → salary_run_lines (one to many)
- transactions ← expenses, salary_runs, vendor_bills, customer_payments (each has one transaction)

---

## Auto-Generated Codes
Generate these automatically on record creation:
- samples.sample_code → `SMP-{YYYY}-{5-digit-padded-id}` e.g. SMP-2025-00001
- inspections.report_number → `RPT-{YYYY}-{5-digit-padded-id}` e.g. RPT-2025-00001
- vendor_bills.bill_number → `BILL-{YYYY}-{5-digit-padded-id}` e.g. BILL-2025-00001

---

You can also create more tables like currencies to use to define the main and other currencies and that will be assigned to the customers as there default currency.
Also you need to create the banks table to define the banks and that will be assigned to the accounts. Like this you can create more master tables and define there relation in the respective tables as needed.

## Business Logic Rules

### Sample Alerts
- When a sample's `receive_date + alert_days` is approaching or passed and status is not Completed or Returned, flag it as overdue.
- When a sample_movement's `expected_return_date` is passed and `actual_return_date` is null, set status to Overdue automatically (can use a scheduled job or compute on-the-fly).

### Vendor Bill Total
- `vendor_bills.total_amount` must always equal the sum of all `vendor_bill_items.line_total`.
- Recalculate and update on every line item add/edit/delete.

### Salary Run Generation
- When creating a salary run for a month, auto-populate `salary_run_lines` with all active employees and their current `basic_salary` from the employees table.
- All fields (bonus, deduction, advance) default to 0 and are editable per employee before paying.
- `net_payable` per line = `basic_salary + bonus - deduction - advance` (compute live in UI and store on save).
- When status changes to Paid, create one transaction record for the total batch. Cannot edit lines after marking Paid.

### Customer Payment Forex Logic
- Customer ledger is always debited in their `default_currency` for the full `invoiced_amount_fc` (they are considered fully paid).
- PKR amounts only affect internal accounts, not the customer ledger.
- The `transaction` linked to a customer_payment should credit the `account_id` with `actual_pkr_received`.
- Gain/loss should be recorded as a separate journal entry or as a note on the transaction.

### Transactions (Double Entry)
- Every financial event must produce exactly one transaction with a debit_account and credit_account.
- Examples:
  - Expense: debit Expense Account (conceptual) / credit Cash or Bank account
  - Vendor Payment: debit Vendor Ledger / credit Cash or Bank
  - Customer Receipt: debit Cash or Bank / credit Customer Ledger
  - Salary: debit Salary Expense / credit Cash or Bank

---

## Modules & Pages to Build

### 1. Master Data Management
- CRUD for: Customers, Brands, Product Categories, Employees, Vendors, Testing Parameters, Accounts, Expense Heads
- All lists with search, filter by status, pagination
- Soft delete or status toggle (no hard deletes on masters)

### 2. Sample Management
- Register new sample (auto-generates sample_code)
- Assign testing parameters from the master list (dynamic, based on category)
- View sample detail with full timeline: movements + inspections
- Sample list with filters: customer, brand, category, status, date range, priority
- Overdue alert badge/indicator on list and dashboard

### 3. Sample Movement Tracking
- Issue sample to Employee / Vendor / Storage / Customer
- Record return (sets actual_return_date, updates status)
- Full movement history per sample
- Overdue movements list

### 4. Inspection Management
- Create inspection for a sample (auto-generates report_number)
- Add results per testing parameter (all parameters from sample_testing_parameters shown)
- Support multiple independent inspections per sample
- Mark overall Pass / Fail / Pending
- Printable inspection report (PDF) — clean layout with: report number, sample info, customer, brand, inspector, date, parameter results table, overall status, remarks

### 5. Vendor Bill Management
- Create bill with dynamic line items (add/remove rows, auto-calculate totals)
- Optional: link to one or more inspections
- Mark as Paid (creates transaction, sets transaction_id)
- Vendor bill list with filters: vendor, status, date range
- Printable bill / payment voucher

### 6. Salary Management
- Generate monthly salary run (button: "Generate for Month YYYY-MM")
- Auto-fills all active employees with their basic salary
- Editable sheet view — all employees in one table, edit bonus/deduction/advance per row
- Net payable calculated live per row and total at bottom
- Single "Mark as Paid" action for the whole batch
- Cannot edit after marking Paid
- Salary history by month

### 7. Expense Management
- Add expense (select expense head, account, amount, date, description, optional attachment)
- Expense list with filters: expense head, account, date range
- Expense summary by head and by account

### 8. Customer Payment (Forex)
- Payment entry form with all forex fields
- Live auto-calculation: enter any 2 of (invoiced, deduction, received) → third auto-fills
- Live calculation of expected_pkr, gain/loss fields
- Customer ledger view: shows balance in their currency
- Payment history per customer

### 9. Vendor Ledger & Payments
- Vendor ledger showing bills and payments
- Running balance per vendor
- Payment against bill

### 10. Financial Ledger & Reports
- Cash book: all transactions for a cash account by date
- Bank book: all transactions for a bank account by date
- Customer ledger: per customer, in their currency
- Vendor ledger: per vendor
- Expense report: filterable by head, account, date range
- Salary report: by month, by employee

### 11. Dashboard
- Summary cards: Active samples, Overdue samples, Overdue movements, Pending inspections
- Recent activity feed (via spatie/laravel-activitylog)
- Quick links to common actions

### 12. Reports (Printable / PDF)
All reports should be printable and exportable to PDF:
- Inspection report (per inspection)
- Sample summary report (filtered list)
- Vendor bill / payment voucher
- Salary sheet (per month)
- Customer account statement
- Vendor account statement
- Cash / bank ledger
- Expense summary

---

## Technical Stack & Conventions

- **Framework**: Laravel (latest stable)
- **Auth**: Laravel Breeze (already installed)
- **Roles**: spatie/laravel-permission (already installed) — use `@can` directives in Blade and middleware on routes
- **Audit**: spatie/laravel-activitylog (already installed) — add `LogsActivity` trait to all main models
- **Frontend**: Blade + Alpine.js + Tailwind CSS (Breeze default stack)
- **PDF**: Use `barryvdh/laravel-dompdf` for printable reports
- **Tables/UI**: Keep it clean and functional — no heavy JS frameworks needed
- **Live calculation**: Use Alpine.js for the salary sheet live totals and customer payment forex auto-calc

### Conventions
- Use Resource Controllers for all CRUD modules
- Use Form Requests for all validation
- Use Eloquent relationships — no raw queries except for complex reports
- All money fields stored as `decimal(15,2)` — never float
- All dates stored as `date` type, datetimes as `datetime`
- Soft deletes on: customers, employees, vendors, samples
- No hard deletes anywhere in the system
- Use database transactions (DB::transaction) when creating financial records that touch multiple tables

### Route Structure
```
/dashboard
/masters/customers
/masters/brands
/masters/categories
/masters/employees
/masters/vendors
/masters/parameters
/masters/accounts
/masters/expense-heads
/samples
/samples/{id}/movements
/samples/{id}/inspections
/inspections
/vendor-bills
/salary
/expenses
/customer-payments
/ledger/cash
/ledger/bank
/ledger/customers/{id}
/ledger/vendors/{id}
/reports/...
```

---

## Seeders to Create

1. **RolesAndPermissionsSeeder** — create 4 roles, assign permissions
2. **AdminUserSeeder** — create default admin: admin@erp.test / password
3. **AccountsSeeder** — seed: Main Cash (Cash/PKR), HBL Bank (Bank/PKR)
4. **ExpenseHeadsSeeder** — seed: Rent, Electricity, Petrol, Salaries, Maintenance, Miscellaneous
5. **DemoDataSeeder** (optional) — 2-3 customers, brands, employees, one sample with inspection

---

## Build Order (Recommended)

1. Run migrations for all tables above
2. Run RolesAndPermissionsSeeder and AdminUserSeeder
3. Build Master CRUD modules (customers, brands, categories, employees, vendors, parameters, accounts, expense heads)
4. Build Sample registration + testing parameter assignment
5. Build Sample movements
6. Build Inspections + results + printable report
7. Build Vendor bills with line items + inspection linking
8. Build Expenses
9. Build Salary runs (batch sheet)
10. Build Customer payments with forex logic
11. Build Ledgers and reports
12. Build Dashboard
13. Apply role-based access control across all routes and views
14. Add LogsActivity trait to all main models