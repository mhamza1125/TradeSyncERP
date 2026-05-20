<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports.view');
    }

    public function cash(Request $request)
    {
        $accounts    = Account::where('account_type', 'Cash')->where('status', true)->get();
        $account     = $accounts->firstWhere('id', $request->account_id) ?? $accounts->first();
        $transactions   = [];
        $openingBalance = 0;

        if ($account) {
            $transactions = Transaction::with(['debitAccount', 'creditAccount', 'creator'])
                ->where(fn ($q) => $q->where('debit_account_id', $account->id)->orWhere('credit_account_id', $account->id))
                ->when($request->from_date, fn ($q) => $q->where('transaction_date', '>=', $request->from_date))
                ->when($request->to_date, fn ($q) => $q->where('transaction_date', '<=', $request->to_date))
                ->orderBy('transaction_date')
                ->paginate(50)
                ->withQueryString();

            $openingBalance = (float) $account->opening_balance;
            if ($transactions->currentPage() > 1) {
                $offset = ($transactions->currentPage() - 1) * $transactions->perPage();
                $openingBalance += Transaction::select('debit_account_id', 'credit_account_id', 'transaction_type', 'amount')
                    ->where(fn ($q) => $q->where('debit_account_id', $account->id)->orWhere('credit_account_id', $account->id))
                    ->when($request->from_date, fn ($q) => $q->where('transaction_date', '>=', $request->from_date))
                    ->when($request->to_date, fn ($q) => $q->where('transaction_date', '<=', $request->to_date))
                    ->orderBy('transaction_date')
                    ->take($offset)
                    ->get()
                    ->reduce(function ($carry, $txn) use ($account) {
                        if ($txn->transaction_type === 'JournalEntry') {
                            return $carry + ($txn->debit_account_id == $account->id ? (float) $txn->amount : -(float) $txn->amount);
                        }
                        return $carry + ($txn->transaction_type === 'CustomerReceipt' ? (float) $txn->amount : -(float) $txn->amount);
                    }, 0.0);
            }
        }

        return view('reports.ledger.cash', compact('accounts', 'account', 'transactions', 'openingBalance'));
    }

    public function bank(Request $request)
    {
        $accounts    = Account::where('account_type', 'Bank')->where('status', true)->get();
        $account     = $accounts->firstWhere('id', $request->account_id) ?? $accounts->first();
        $transactions   = [];
        $openingBalance = 0;

        if ($account) {
            $transactions = Transaction::with(['debitAccount', 'creditAccount', 'creator'])
                ->where(fn ($q) => $q->where('debit_account_id', $account->id)->orWhere('credit_account_id', $account->id))
                ->when($request->from_date, fn ($q) => $q->where('transaction_date', '>=', $request->from_date))
                ->when($request->to_date, fn ($q) => $q->where('transaction_date', '<=', $request->to_date))
                ->orderBy('transaction_date')
                ->paginate(50)
                ->withQueryString();

            $openingBalance = (float) $account->opening_balance;
            if ($transactions->currentPage() > 1) {
                $offset = ($transactions->currentPage() - 1) * $transactions->perPage();
                $openingBalance += Transaction::select('debit_account_id', 'credit_account_id', 'transaction_type', 'amount')
                    ->where(fn ($q) => $q->where('debit_account_id', $account->id)->orWhere('credit_account_id', $account->id))
                    ->when($request->from_date, fn ($q) => $q->where('transaction_date', '>=', $request->from_date))
                    ->when($request->to_date, fn ($q) => $q->where('transaction_date', '<=', $request->to_date))
                    ->orderBy('transaction_date')
                    ->take($offset)
                    ->get()
                    ->reduce(function ($carry, $txn) use ($account) {
                        if ($txn->transaction_type === 'JournalEntry') {
                            return $carry + ($txn->debit_account_id == $account->id ? (float) $txn->amount : -(float) $txn->amount);
                        }
                        return $carry + ($txn->transaction_type === 'CustomerReceipt' ? (float) $txn->amount : -(float) $txn->amount);
                    }, 0.0);
            }
        }

        return view('reports.ledger.bank', compact('accounts', 'account', 'transactions', 'openingBalance'));
    }

    public function customer(Request $request, Customer $customer)
    {
        $payments = $customer->payments()
            ->with('account')
            ->when($request->from_date, fn ($q) => $q->where('payment_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('payment_date', '<=', $request->to_date))
            ->latest('payment_date')
            ->paginate(50)
            ->withQueryString();

        return view('reports.ledger.customer', compact('customer', 'payments'));
    }
}
