<?php

namespace Tests\Support;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;

/** Finance fixtures for feature tests (trade_test only — see tests/TestCase.php). */
trait BuildsFinance
{
    protected function makeAccount(string $type = 'Cash', string $opening = '0.00', string $name = 'Main Cash'): Account
    {
        return Account::create([
            'account_name' => $name,
            'account_type' => $type,
            'currency' => 'PKR',
            'opening_balance' => $opening,
            'status' => true,
        ]);
    }

    /**
     * The app's cash-book convention: every non-transfer transaction names the same
     * Cash/Bank account on both sides; transfers use from (credit) / to (debit).
     */
    protected function postTxn(string $type, Account $account, string $amount, string $date, ?Account $to = null): Transaction
    {
        return Transaction::create([
            'transaction_date' => $date,
            'transaction_type' => $type,
            'debit_account_id' => $to?->id ?? $account->id,
            'credit_account_id' => $account->id,
            'amount' => $amount,
            'created_by' => User::query()->value('id'),
        ]);
    }
}
