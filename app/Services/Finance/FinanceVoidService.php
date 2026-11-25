<?php

namespace App\Services\Finance;

use App\Http\Controllers\Finance\CustomerInvoiceController;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Audit F-10: an expense or customer payment is never hard-deleted. Voiding marks the record
 * AND its transaction (same DB transaction), so ledgers, invoice status and dashboard figures
 * stop counting it, while the record and its history stay (docs/FINANCE_RULES.md).
 */
class FinanceVoidService
{
    public function voidExpense(Expense $expense, string $reason, User $by): void
    {
        DB::transaction(function () use ($expense, $reason, $by) {
            $this->markVoid($expense, $reason, $by);
            $expense->purchaseOrder?->recalculatePaymentStatus();
        });
    }

    public function voidPayment(CustomerPayment $payment, string $reason, User $by): void
    {
        DB::transaction(function () use ($payment, $reason, $by) {
            $this->markVoid($payment, $reason, $by);
            CustomerInvoiceController::syncInvoiceStatus($payment->invoice_reference);
        });
    }

    private function markVoid(Expense|CustomerPayment $record, string $reason, User $by): void
    {
        if ($record->isVoided()) {
            throw new RuntimeException('This record is already voided.');
        }

        $fields = ['voided_at' => now(), 'voided_by' => $by->id, 'void_reason' => $reason];

        $record->update($fields);
        if ($txn = Transaction::find($record->transaction_id)) {
            $txn->update($fields);
        }

        $this->log($record, $reason, $by);
    }

    private function log(Model $record, string $reason, User $by): void
    {
        activity()->performedOn($record)->causedBy($by)->event('voided')
            ->withProperties(['reason' => $reason, 'transaction_id' => $record->transaction_id])
            ->log('Voided');
    }
}
