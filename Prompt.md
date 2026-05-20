## Improved Prompt

I am facing an issue in my accounting ledger system.

### URLs (local):

* Bank Ledger: [http://127.0.0.1:8000/ledger/bank](http://127.0.0.1:8000/ledger/bank)
* Cash Ledger: [http://127.0.0.1:8000/ledger/cash](http://127.0.0.1:8000/ledger/cash)

---

## Problem Description

Currently, ledger entries are incorrect in both Bank and Cash ledgers.

The same transaction amount is being shown in **both Debit and Credit columns at the same time**, which is incorrect accounting behavior.

Because of this:

* Debit and Credit cancel each other out
* The **Balance always becomes 0 or incorrect**

---

## Correct Accounting Behavior Required

Each transaction must follow proper double-entry logic in ledger display:

### 1. If money goes OUT (Expense / Payment)

* Show amount ONLY in **Credit column**
* Reduce balance

Example:

* Electricity bill paid
* Salary paid
* Vendor payment

---

### 2. If money comes IN (Income / Receipt)

* Show amount ONLY in **Debit column**
* Increase balance

Example:

* Customer payment received
* Loan received
* Cash deposited into bank

---

## Required Fix

For both:

* Bank Ledger
* Cash Ledger

### You must ensure:

1. Each transaction appears in ONLY ONE column:

   * Either Debit OR Credit (not both)
2. Balance must be calculated correctly based on:

   * Opening balance
   * Running sum of Debit - Credit
3. Remove logic that duplicates same amount into both columns

---

## Expected Result

* Expenses → Credit only
* Receipts → Debit only
* Balance should update correctly after each entry
* Ledger should reflect real accounting behavior

---

## Notes

This issue is happening in both:

* bank ledger view
* cash ledger view

Please fix the rendering logic and balance calculation so it follows standard accounting rules.
