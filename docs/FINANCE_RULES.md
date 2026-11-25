# Finance rules: as implemented

This is the single place these rules are written down. If you change a rule, change the code and this file in the same commit. Background and open items: `docs/FINANCE_REVIEW.md`. Open questions: Q21–Q25 in `docs/AUDIT_QUESTIONS.md`.

## 1. Book model: a cash book per Cash/Bank account

`accounts` holds only Cash and Bank accounts. Every **non-transfer** transaction names the same account as debit **and** credit, and its direction comes from `transaction_type`. This is not double-entry bookkeeping: there are no expense, revenue or receivable accounts.

| transaction_type | Effect on the account |
|---|---|
| `CustomerReceipt` | money **in** (+amount) |
| `JournalEntry` (fund transfer) | **in** for the debit (destination) account, **out** for the credit (source) account |
| `Expense`, `Salary`, `VendorPayment` | money **out** (−amount) |

Code: `App\Services\Finance\LedgerService` is the only implementation. The cash and bank pages, both ledger PDFs and the transfer balance check use it. Blade only prints its results.

## 2. Balances

- **Opening balance on a statement:** `accounts.opening_balance` plus the effect of every non-voided transaction dated **before** the "from" date.
- **Pagination:** rows are ordered by `(transaction_date, id)`. Each page opens with the previous page's closing balance.
- **Column convention (pages and PDF):** Debit (Dr) = money in; Credit (Cr) = money out.
- **Transfer check:** a transfer is refused when it exceeds the source account's **ledger** balance (all non-voided transactions to date).
- **Arithmetic:** sums run in MySQL `DECIMAL`, and running balances use bcmath strings at 2 decimals. No PHP floats are involved.

## 3. Voids instead of deletes

- An expense or a customer payment is never hard-deleted. "Void":
  - needs the existing `expenses.delete` / `customer-payments.delete` permission and a written reason (3–500 characters);
  - marks the record **and** its transaction (`voided_at`, `voided_by`, `void_reason`) in one DB transaction;
  - is written to the activity log (event `voided`).
- Voided rows are kept and shown with a "Voided" badge. They are excluded from:
  - ledger balances;
  - invoice paid amounts;
  - purchase-order paid amounts;
  - dashboard totals;
  - the customer ledger.
- A voided record can't be voided again. A voided payment can't be edited. Expenses have no edit screen.

Code: `App\Services\Finance\FinanceVoidService`.

## 4. Invoices and payments

- **Invoice currency:** the customer's currency. The invoice stores no currency of its own (FN-4, open).
- **Payment ↔ invoice link:**
  - A new payment's `invoice_reference` must be an existing invoice of the selected customer. The payment then stores `customer_invoice_id`.
  - Payments from before this rule keep their text reference. They are shown as "not linked" and still count when their text matches.
  - Editing such a payment may keep its old reference unchanged.
- **Paid amount:** the sum of `received_fc` of the invoice's non-voided payments (linked by id, or legacy rows matched by text).
  - Withholding tax and deductions are **not** counted. This is pending the accountant's rule (Q22).
  - When the amount due reaches 0 the status becomes Paid; when something has been paid it becomes Partial.
- **Overdue:** derived, never stored. An invoice is overdue when:
  - its status is not Paid or Cancelled;
  - its amount due is above 0;
  - its due date is before today. On the due date itself it is not overdue.

  The invoice list badge shows "Overdue" when this applies.

Code: `CustomerInvoice::scopeOverdue()` / `isOverdue()` / `display_status`; `CustomerInvoiceController::syncInvoiceStatus()`.

## 5. Dashboard figures

| Figure | Definition |
|---|---|
| Invoiced / Outstanding | Sum of `total_amount` / `amount_due` **per currency**, excluding Cancelled (and, for Outstanding, Paid). Currencies are never added together. |
| Overdue invoices | The count of invoices matching the overdue rule in §4 |
| Payments this month | Sum of `actual_pkr_received` (PKR) of non-voided payments dated this month |
| Expenses this month | Sum of `amount` (PKR) of non-voided expenses dated this month |
| Overdue movements | Legacy `sample_movements` **plus** `movements` with status Issued or Overdue, an expected return date before today, and no actual return date |

## 6. Document numbers

- **Report numbers** (`RPT-{year}-{00000}`), **invoice numbers** (Company Settings pattern) and **PO numbers** (`PO-{year}-{00000}`) come from `number_sequences`, taken under a row lock.
- Each series started from the value the old `max(id)+1` logic would have given, so it continues from there.
- Numbers are **never reused**, even after a delete. Gaps are possible if a save fails.
- Run numbers per inspection are taken while holding a lock on the inspection row.

Code: `App\Services\NumberSequenceService`.
