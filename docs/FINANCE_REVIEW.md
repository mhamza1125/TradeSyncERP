CONFIDENTIAL - do not commit or deploy

# Finance review: TradeSyncERP

Advisory, stage 1. Nothing is changed. Owner decisions apply: extend what exists, no redesign, and invoicing "by project" stays as it is.

**Labels.**
- **VERIFIED** means the code was read and checked, or the database was queried.
- **READ-ONLY** means it was inferred from code.
- **HYPOTHESIS** means it is an inference.

All figures are aggregate counts from **`trade_prod`** (a copy of production; SELECT only, 2026-11-25). No personal data is included.

---

## 1. Current flows and tables

### 1.1 Volumes (VERIFIED, database: trade_prod)

| Item | Count |
|---|---|
| Customer invoices | 5: Paid 2, Overdue 1, others Draft/Sent/Partial. None cancelled or soft-deleted. |
| Invoice lines | 8 |
| Customer payments | 4: 3 paying customers; 1 with withholding tax, 2 with deductions, 4 with an FX gain/loss |
| Expenses | 6 (0 linked to a purchase order) |
| Purchase orders | 0 |
| Salary runs | 0 |
| Transactions | 10: Expense 6, CustomerReceipt 4 |
| Accounts / banks / currencies | 5 / 3 / 5 (PKR default; USD, EUR, GBP, CNY) |

Volumes are tiny (HYPOTHESIS: early use), so results below show the **mechanics**, not business trends.

### 1.2 Flow map (READ-ONLY, code)

| Flow | Tables | Posting | Notes |
|---|---|---|---|
| Customer invoice (lines per supplier + inspection type + PO/invoice no.) | `customer_invoices`, `customer_invoice_items` | none (no receivable posting) | Currency = the **customer's** currency at display time; no currency column on the invoice. Totals are computed in the controller. Number = `InvoiceNumberService` (max(id)+1 into a pattern, unique key). |
| Customer payment | `customer_payments` → `transactions` (CustomerReceipt) | one transaction, debit = credit = receiving account | FC amounts, exchange rate, WHT %, remittance charges, PKR gain/loss. Linked to the invoice **only by text** `invoice_reference`. `CustomerInvoiceController::syncInvoiceStatus()` updates paid, due and status. |
| Expense | `expenses` → `transactions` (Expense) | debit = credit = paying account | Expense head stored on the expense, not as an account |
| Purchase order payment | `purchase_orders` → `transactions` (Expense) + `expenses` | same | `PurchaseOrderController::pay` |
| Salary run pay | `salary_runs` → `transactions` (Salary) | debit = credit = paying account | Created in `SalaryRunController` (CLAUDE.md says `SalaryRun::pay()`; drift) |
| Fund transfer | `transactions` (JournalEntry) | debit = destination, credit = source | The only true two-account entry |
| Ledgers (cash, bank, customer) + PDFs | `transactions`, `customer_payments` | — | `LedgerController`, `reports/ledger/*.blade.php`, `exports/ledger-pdf.blade.php` |

**Key structural finding (VERIFIED in code and data).** **All 10 transactions have `debit_account_id = credit_account_id`** (count from trade_prod). Only transfers use two accounts.
- The system is a **single-entry cash book per Cash/Bank account**, with the direction inferred from `transaction_type`.
- It is **not double-entry**: there are no expense, revenue, receivable or equity accounts. `accounts.account_type` is only Cash or Bank.
- CLAUDE.md's "Double-Entry Accounting" section (debit Expense Head account, credit Cash) does **not** describe the code.

This review keeps the cash-book design (no redesign) and corrects the documentation.

---

## 2. Correctness

### 2.1 Re-verification of audit findings

| ID | Finding | Status today | Evidence |
|---|---|---|---|
| F-09 | Ledger opening balance ignores transactions before `from_date` | **Still present** (READ-ONLY) | `LedgerController.php:35` (`$openingBalance = (float) $account->opening_balance`); the PDF path uses the same. No data impact measured (few transactions). |
| F-10 | Deleting an expense or payment leaves its transaction | **Still present in code** (READ-ONLY): `ExpenseController::destroy` (`:99`) and `CustomerPaymentController::destroy` (`:196`) delete only the child | **Data: 0 orphaned transactions** (VERIFIED: no Expense/CustomerReceipt/Salary transaction without a child). No deletions have happened yet. |
| F-16a | Dashboard sums invoice totals across currencies | **Still present** (READ-ONLY) | `DashboardController` sums `total_amount`/`amount_due` and labels it "PKR". **Data: invoices exist in EUR (3), GBP (1) and USD (1)**, so the dashboard figure is a meaningless mixed sum. |
| F-16b | "Overdue" status is manual | **Still present** | **Data: 2 invoices past due, not Paid, not marked Overdue** (VERIFIED) |
| F-16c | Dashboard overdue movements reads only the legacy table | **Still present** | **Data: legacy `sample_movements` = 0 rows, new `movements` = 6**, so the widget is always 0 (VERIFIED) |
| F-21 | Balance rule implemented in several places, using float | **Still present: 4 places** | `LedgerController` (×2 closures), `reports/ledger/cash.blade.php:123-142`, `bank.blade.php:129-148`, `exports/ledger-pdf.blade.php:110-122`. All use `(float)`. They agree today because all use the type rule. |

### 2.2 New findings from this review

| ID | Finding | Evidence | Severity (proposed) |
|---|---|---|---|
| FN-1 | **The transfer "insufficient balance" check uses a different balance rule from the ledger.** It computes opening + Σdebits − Σcredits. Expenses and receipts post the same account on both sides, so they cancel out: the check sees only opening + transfers. | `TransferController.php:33-35` (READ-ONLY). **Data: for 3 of 5 accounts the check's balance differs from the ledger balance** (VERIFIED) | High: allows overdrawing, or blocks valid transfers |
| FN-2 | **Invoice paid amount ignores withholding tax and deductions.** `syncInvoiceStatus` sums `received_fc` only, so an invoice settled with WHT or a short-payment deduction stays "Partial" forever. | `CustomerInvoiceController.php:188` (READ-ONLY). **Data: 1 payment with WHT, 2 with deductions; 1 invoice whose `amount_paid` ≠ Σ`received_fc` of matching payments, 2 ≠ Σ(received + deduction + WHT)** | High (receivables wrong). The accounting rule needs an accountant (Q22). |
| FN-3 | **Payments link to invoices by free text.** | **Data: 3 of 4 payments reference an invoice number that does not exist** (VERIFIED). Those payments never update any invoice. | High |
| FN-4 | Invoice currency is not stored on the invoice; it is the customer's current currency. Changing a customer's currency silently re-labels history. | schema (VERIFIED) | Medium |
| FN-5 | Transactions are created directly in controllers (5 controllers), against the project rule "the financial model owns that responsibility" | grep (VERIFIED) | Low (structure) |
| FN-6 | No activity log on `CustomerInvoiceItem`, `SalaryRunLine` or `AllowanceType`; others are logged | grep (VERIFIED) | Medium (invoice line edits are untraceable) |
| FN-7 | No period locking or closing; any past entry can be edited or deleted | grep (VERIFIED: no such code) | Medium |
| FN-8 | Expenses can't be linked to projects or customers; "project" does not exist in code | grep (VERIFIED: no "project" in app/views/routes) | Blocks project profitability |

### 2.3 Reconciliation results (VERIFIED, database: trade_prod, counts only)

| Check | Count |
|---|---|
| Transactions (non-transfer) with no child record | 0 |
| Expenses / payments without a transaction | 0 / 0 |
| Expense amount ≠ its transaction amount | 0 |
| Payment PKR received ≠ its transaction amount | 0 |
| Invoice subtotal ≠ Σ lines | 0 |
| Invoice total ≠ subtotal + tax − discount | 0 |
| Invoice amount_due ≠ total − paid | 0 |
| Invoice paid ≠ Σ received_fc of payments by reference | 1 |
| Invoice paid ≠ Σ(received + deduction + WHT) | 2 |
| Payments referencing a non-existent invoice number | **3 of 4** |
| Past due, not Paid/Cancelled, not marked Overdue | 2 |
| Status Paid but amount due > 0 | 0 |
| Due date before invoice date | 0 |
| Negative or zero money values | 0 |
| Transactions with debit account = credit account | **10 of 10** |
| Accounts with a negative ledger balance | 0 |
| Accounts where the transfer-check balance ≠ the ledger balance | **3 of 5** |

The "ledger closing balance vs account balance" comparison isn't possible: accounts store no balance, so the balance is always computed (VERIFIED).

---

## 3. Money handling (READ-ONLY)

- **Columns.** Money is `decimal(15,2)` and the exchange rate is `decimal(15,6)`. Sound.
- **PHP arithmetic.** It uses floats: `(float)` casts (12 occurrences in Finance/Reports/Models), `round(…, 2)` in payment calculations, and `collect()->sum()` for invoice subtotals. At current volumes the error is negligible. Float sums can drift by 0.01, though, and comparisons like `amount_due <= 0` are fragile. **bcmath is available** (VERIFIED `php -m`).
- **Rounding.** WHT and expected PKR are rounded per payment, half-up via `round()`. There's no documented rounding rule.
- **Currency.**
  - The payment stores FC, the rate and PKR. Sound.
  - The invoice stores no currency (FN-4).
  - The dashboard mixes currencies (F-16a).
- **Recomputation.** Invoice totals are recomputed on save from the posted lines. `amount_paid` is recomputed from payments by text reference (FN-2, FN-3).

## 4. Integrity (READ-ONLY)

| Topic | State |
|---|---|
| DB transactions | Store/update flows use `DB::transaction` (payments, transfers). Deletes are not wrapped with their transaction (F-10). |
| Numbering | Invoice and PO numbers are max(id)+1 with a unique key, so concurrent saves get a 500 error, not duplicates |
| Deletes | Hard delete of expenses, payments and transactions. Invoices and POs use soft delete. **No void/reversal concept.** |
| Period close | None (FN-7) |
| Audit trail | Activity log on most finance models; gaps in FN-6. Log entries hold old/new values. |
| Permissions / separation of duties | Per-entity create/edit/delete (plus `salary.pay`, `purchase-orders.pay`). **No approval step**: the same user can raise and pay an expense, and edit or delete payments. Accountant and Admin hold all of them. |
| Attachments | Generic attachment panel (since 2026-11-25: permission and type allowlist). Expense receipts are a single `attachment` path column. |

## 5. Reporting gaps (judged against a buyer-paid inspection service)

| Report | Today | Feasible from current data? |
|---|---|---|
| Receivables ageing by customer | No | Yes, once FN-2/FN-3 are fixed (invoice-linked payments) |
| Receivables by "project" | No | **Only if "project" means something stored.** Candidates: customer order, PO/invoice number on lines, or inspection. Q21. |
| Payables | No (POs exist but have no data) | Partly (POs only) |
| Cash/bank position (all accounts, one date) | No (one ledger per account) | Yes, via a single ledger service |
| Project profitability | No | **No.** Expenses can't be linked to projects or customers (FN-8). It needs an additive link if wanted. |
| Expense by head and month | No | Yes |
| Salary summaries | PDFs per run exist | Yes |
| Revenue by customer / supplier / inspection type | No | Yes (invoice lines carry supplier and inspection type) |
| CSV exports | None | Build with formula-injection escaping (`= + - @` prefixed with `'`) |
| One definitions document | No | Add a `docs/FINANCE_RULES.md` (like `QC_RULES.md`) |

---

## 6. Candidate enhancements (extend the existing design)

Format for each item: **Why** / **Alt** (alternatives) / **Trade-off** / **Not when** / **Effort** / **Value** / **Deps** (dependencies) / **Risk** (to existing data) / **Tests**.

**E1. Single ledger service, used by pages, PDFs, dashboard and the transfer check.** This is F-21 + F-09 + FN-1; see the design in §8.
- **Why:** one rule, correct openings.
- **Alt:** patch each copy.
- **Trade-off:** none meaningful.
- **Effort / Value:** S–M / high.
- **Risk:** none (read-only logic).

**E2. Invoice ↔ payment link and settlement rule.** Fixes FN-2 and FN-3.
- **What (link):** additive `customer_payments.customer_invoice_id` FK NULL, filled on create from an invoice picker (free text kept for history).
- **What (allocation, optional):** a `customer_payment_allocations` table (payment_id, invoice_id, amount_fc) for one payment covering several invoices.
- **What (settlement):** "settled amount" = received + WHT + agreed deduction, **subject to the accountant's rule (Q22)**.
- **Alt:** keep text, but validate that it exists (cheap, partial).
- **Trade-off:** allocation UI work.
- **Effort / Value:** M / high.
- **Risk:** existing payments keep a NULL FK. Backfilling by exact text match needs approval: only 1 of 4 would match today.
- **Tests:** allocation sums ≤ payment; the invoice status follows the settled amount; an over-payment produces a credit balance.

**E3. Derived invoice status and overdue.**
- **What:** `is_overdue` = due_date < today ∧ amount_due > 0 ∧ status ∉ {Paid, Cancelled}, computed in a query scope and the model, not stored. Keep the manual statuses Draft, Sent and Cancelled.
- **Why:** F-16b.
- **Effort:** S.
- **Risk:** none (the stored status is unchanged).
- **Tests:** the boundary at the due date; Paid is never overdue.

**E4. Invoice currency stored.**
- **What:** additive `customer_invoices.currency_code` (and optionally `exchange_rate_at_issue`), set on create.
- **What (existing rows):** **a backfill from the customer's current currency is a data write, so it is a separate approved step.**
- **Why:** FN-4, F-16a.
- **Effort:** S.
- **Tests:** the dashboard groups by currency.

**E5. Voids instead of deletes for expenses, payments and transfers.**
- **What:** additive `voided_at`, `voided_by`, `void_reason` on `transactions`, `expenses` and `customer_payments`. The ledger excludes voided entries. A void needs a permission and a reason, and is logged. The delete routes become void.
- **Why:** F-10; audit trail; ALCOA "complete".
- **Alt:** delete the child and its transaction together (simple, loses history). Or post a reversing entry. The cash-book types can't express a reversal cleanly without adding `Reversal` to the enum.
- **Trade-off:** voided rows stay in tables; list screens need a filter.
- **Effort / Value:** M / high.
- **Risk:** none (additive); existing rows are not voided.
- **Tests:** a voided expense disappears from ledgers and the dashboard; it can't be voided twice; it can't be edited after a void.

**E6. Credit notes.**
- **What:** a `customer_credit_notes` table linked to an invoice. It reduces amount_due and has its own number sequence.
- **Why:** corrections without editing issued invoices.
- **Effort:** M.
- **Not when:** invoices are never corrected after sending (Q23).

**E7. Expense approval threshold.**
- **What:** `expenses.status` ENUM('pending','approved','rejected') DEFAULT 'approved' (existing rows unaffected), plus `approved_by` and `approved_at`. Above a company-setting threshold, a different user with `expenses.approve` must approve before the expense posts.
- **Effort:** M.
- **Not when:** one person runs finance (Q24).

**E8. Bank/cash reconciliation.**
- **What:** a `bank_reconciliations` table (account, statement date, statement balance, reconciled_by) and `transactions.reconciled_at`.
- **Effort:** M.
- **Value:** medium.
- **Deps:** E1.

**E9. Period lock.**
- **What:** `company_settings.books_locked_until` DATE. Creating, editing or voiding finance entries dated on or before it is refused unless the user has a permission.
- **Effort:** S.
- **Tests:** the boundary date.

**E10. Attachment rules for finance.**
- **What:** a receipt is required above a threshold, and the expense `attachment` column is moved into the attachments table (additive; old column kept).
- **Effort:** S–M.

**E11. Finance reports and CSV.**
- **What:** receivables ageing, cash position, expense by head, revenue by customer/supplier/inspection type, salary summary. Definitions go in `docs/FINANCE_RULES.md`. CSVs are escaped.
- **Effort:** M.
- **Deps:** E1–E4.

**E12. Activity log on invoice lines, salary lines and allowance types (FN-6).**
- **Effort:** S.
- **Risk:** none.

**E13. Project profitability.** Only if Q21 defines "project" and the owner wants it.
- **What:** additive `expenses.customer_id` / `project_ref` columns.
- **Effort:** M.

---

## 7. Tax and legal (for an accountant to confirm; not implemented or asserted)

HYPOTHESIS: the company is in **Pakistan**:
- the default currency is PKR;
- the company settings hold `ntn_number` and `strn_number`, which match Pakistan's FBR National Tax Number and Sales Tax Registration Number;
- the payment form defaults withholding tax to 1%.

**Items for an accountant to confirm against current official sources** (for example FBR): this review asserts none of these rules.
1. Whether sales tax on services applies to inspection services billed to foreign customers. Provincial sales tax on services may apply, as may export-of-services treatment.
2. Required invoice content: NTN/STRN, tax breakdown, serial numbering rules, and any e-invoicing integration requirement.
3. The withholding-tax treatment of foreign remittances and how it should settle an invoice (FN-2).
4. The accounting treatment of exchange gains and losses, and the rate to use (receipt-date vs. invoice-date).
5. Record-retention period for books and invoices.
6. Salary withholding and statutory contributions in salary runs (not reviewed in depth).

---

## 8. Designs for pre-approved fixes F-09, F-10, F-16, F-21 (wait for approval before building)

### F-21 + F-09 (+FN-1): `App\Services\Finance\LedgerService`
- **One rule** (the current semantics, written once): for account A, a transaction's effect is:
  - +amount if type = CustomerReceipt;
  - for JournalEntry, +amount if debit = A, and −amount if credit = A;
  - −amount for every other type (Expense, Salary, VendorPayment).
- **SQL**, no PHP floats: `SUM(CASE … END)` computed in MySQL DECIMAL.
  - `openingAt(Account, ?date)` = `opening_balance` + Σ effect of transactions with `transaction_date < date`.
  - `entries(Account, from, to)` returns rows ordered by `(transaction_date, id)`, with a running balance computed in SQL using a window function **or** in PHP with **bcmath** strings.
    - **Window functions need MariaDB ≥ 10.2.** The server is 10.4 (VERIFIED), but shared hosting must also be ≥ 10.2 / MySQL ≥ 8, so ask (Q25). The bcmath fallback is safe everywhere.
  - Pagination keeps a correct opening per page: opening at `from` + Σ effect of earlier rows on the same filter, via an SQL subquery.
- **Consumers:** cash page, bank page, both PDFs, the customer ledger (separate, but the same pattern), the dashboard, and the **transfer balance check** (fixes FN-1). The Blade views only print the precomputed `running_balance`; no maths in Blade.
- **Migrations:** none. Optional index `transactions (debit_account_id, transaction_date)` and `(credit_account_id, transaction_date)`: additive, `CREATE INDEX …`.
- **Tests (MySQL `trade_test`):**
  - the audit worked example: opening 100,000; Jan expense 30,000; filter from 1 Feb gives opening 70,000;
  - pages 1/2/3 openings equal the cumulative sums;
  - the PDF closing equals the page closing;
  - the transfer is refused when the ledger balance is insufficient;
  - decimals: 0.1 + 0.2 style sums are exact;
  - same-date ordering is by id.
- **Risk:** none to data. Visible numbers change where they were wrong (openings with a from-date; the transfer check).

### F-10: voids (E5) — recommended. Alternative: delete the pair.
- **Recommended (E5).** Additive SQL idea:
  ```sql
  ALTER TABLE transactions ADD voided_at TIMESTAMP NULL, ADD voided_by BIGINT UNSIGNED NULL, ADD void_reason VARCHAR(500) NULL;
  ALTER TABLE expenses ADD voided_at TIMESTAMP NULL, ADD voided_by BIGINT UNSIGNED NULL, ADD void_reason VARCHAR(500) NULL;
  ALTER TABLE customer_payments ADD voided_at TIMESTAMP NULL, ADD voided_by BIGINT UNSIGNED NULL, ADD void_reason VARCHAR(500) NULL;
  ```
  - `destroy` becomes `void`. It runs in a DB transaction, sets the fields on both the child and its transaction, re-syncs the invoice, and logs the action.
  - `LedgerService` and the dashboard exclude voided rows.
  - New permissions `expenses.void` and `customer-payments.void` (Admin/Accountant), created through the create-only seeder.
- **Alternative.** Delete the transaction together with the child, in one DB transaction (no migration). This is simpler but leaves only the activity log as history.
- **Before deploying:** rerun the read-only orphan query (0 today).
- **Tests:**
  - a void hides the entry from the ledger and the dashboard;
  - the invoice status recomputes after a payment is voided;
  - a voided entry is uneditable and can't be voided twice;
  - a user without the permission gets 403;
  - **no hard delete remains** (the route gone or returning 405).
- **Risk:** none (additive; existing rows untouched).

### F-16: dashboard figures
1. **Currencies:** group invoiced and receivable totals by currency, from E4's `currency_code`, falling back to the customer currency until it is backfilled. Show one line per currency; never label a mixed sum "PKR".
2. **Overdue invoices:** from E3's derived `is_overdue` (no status writes).
3. **Overdue movements:** count from **`movements`** (status Issued, `expected_return_date < today`, `actual_return_date` NULL) **plus** legacy `sample_movements` while it exists.
4. **Expenses / payments this month:** via `LedgerService` excluding voided rows.
- **Migration:** only E4 (`ALTER TABLE customer_invoices ADD currency_code VARCHAR(10) NULL`). The backfill is a separate, approved data step.
- **Tests:** a mixed-currency fixture shows separate lines; an invoice one day past due counts as overdue; a new-system movement overdue counts.
- **Risk:** none.

### Documentation fixes that go with them
Rewrite CLAUDE.md "Double-Entry Accounting" to describe the cash-book model honestly, and add `docs/FINANCE_RULES.md` (balance rule, settlement rule after Q22, rounding, currency).
