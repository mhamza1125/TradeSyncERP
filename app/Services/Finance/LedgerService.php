<?php

namespace App\Services\Finance;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

/**
 * The one place the cash/bank balance rule lives (docs/FINANCE_RULES.md).
 *
 * The books are a cash book per Cash/Bank account: every non-transfer transaction names
 * the same account as debit and credit, and its direction comes from transaction_type.
 *   CustomerReceipt          → money IN  (+amount)
 *   JournalEntry (transfer)  → IN for the debit (destination) account, OUT for the credit (source)
 *   Expense, Salary, VendorPayment → money OUT (−amount)
 *
 * Sums run in MySQL DECIMAL and running balances in bcmath strings: no PHP floats.
 */
class LedgerService
{
    private const SCALE = 2;

    /** Signed effect of one transaction on an account, as a decimal string. */
    public static function effect(Transaction $txn, int $accountId): string
    {
        $amount = bcadd((string) $txn->amount, '0', self::SCALE);

        $in = $txn->transaction_type === 'CustomerReceipt'
            || ($txn->transaction_type === 'JournalEntry' && (int) $txn->debit_account_id === $accountId);

        return $in ? $amount : bcsub('0', $amount, self::SCALE);
    }

    /** Transactions that touch the account (both sides). */
    public function query(Account $account): Builder
    {
        return Transaction::query()
            ->notVoided()
            ->where(fn ($q) => $q->where('debit_account_id', $account->id)->orWhere('credit_account_id', $account->id));
    }

    /** Opening balance plus everything dated strictly before $date (no date = opening only). */
    public function balanceBefore(Account $account, ?string $date): string
    {
        $opening = $this->opening($account);

        return $date === null
            ? $opening
            : bcadd($opening, $this->sumEffect($this->query($account)->where('transaction_date', '<', $date), $account), self::SCALE);
    }

    /** Balance including everything up to and including $date (no date = all). */
    public function balanceAt(Account $account, ?string $date = null): string
    {
        $query = $this->query($account)->when($date, fn ($q) => $q->where('transaction_date', '<=', $date));

        return bcadd($this->opening($account), $this->sumEffect($query, $account), self::SCALE);
    }

    /**
     * Statement for a period. With $perPage, returns one page whose opening balance is
     * the closing balance of the previous page (rows ordered by date, then id).
     */
    public function statement(Account $account, ?string $from, ?string $to, ?int $perPage = null, int $page = 1): LedgerStatement
    {
        $base = fn () => $this->query($account)
            ->when($from, fn ($q) => $q->where('transaction_date', '>=', $from))
            ->when($to, fn ($q) => $q->where('transaction_date', '<=', $to))
            ->orderBy('transaction_date')
            ->orderBy('id');

        $opening = $this->balanceBefore($account, $from);
        $paginator = null;

        if ($perPage !== null) {
            $paginator = $base()->with(['debitAccount', 'creditAccount', 'creator'])
                ->paginate($perPage, ['*'], 'page', $page);

            $offset = ($paginator->currentPage() - 1) * $perPage;
            if ($offset > 0) {
                $earlier = $base()->select('*')->limit($offset);
                $opening = bcadd($opening, $this->sumEffect(Transaction::query()->fromSub($earlier, 'transactions'), $account), self::SCALE);
            }
            $rows = $paginator->getCollection();
        } else {
            $rows = $base()->with(['debitAccount', 'creditAccount', 'creator'])->get();
        }

        $running = $opening;
        $totalIn = '0.00';
        $totalOut = '0.00';

        foreach ($rows as $txn) {
            $effect = self::effect($txn, $account->id);
            $isIn = bccomp($effect, '0', self::SCALE) >= 0;
            $abs = $isIn ? $effect : bcsub('0', $effect, self::SCALE);

            $running = bcadd($running, $effect, self::SCALE);
            $isIn ? $totalIn = bcadd($totalIn, $abs, self::SCALE) : $totalOut = bcadd($totalOut, $abs, self::SCALE);

            $txn->setAttribute('direction', $isIn ? 'in' : 'out');
            $txn->setAttribute('amount_in', $isIn ? $abs : null);
            $txn->setAttribute('amount_out', $isIn ? null : $abs);
            $txn->setAttribute('running_balance', $running);
            $txn->setAttribute('counter_account', $txn->transaction_type === 'JournalEntry'
                ? ($isIn ? $txn->creditAccount : $txn->debitAccount)
                : null);
        }

        return new LedgerStatement($account, $opening, $running, $totalIn, $totalOut, $rows, $paginator);
    }

    private function opening(Account $account): string
    {
        return bcadd((string) ($account->getRawOriginal('opening_balance') ?? '0'), '0', self::SCALE);
    }

    private function sumEffect(Builder $query, Account $account): string
    {
        $sum = (clone $query)->toBase()
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN transaction_type = 'CustomerReceipt' THEN amount
                    WHEN transaction_type = 'JournalEntry' AND debit_account_id = ? THEN amount
                    ELSE -amount END), 0) AS s",
                [$account->id]
            )
            ->value('s');

        return bcadd((string) $sum, '0', self::SCALE);
    }
}
