<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Vendor;
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
        $transactions = [];

        if ($account) {
            $transactions = Transaction::with(['debitAccount', 'creditAccount', 'creator'])
                ->where(fn ($q) => $q->where('debit_account_id', $account->id)->orWhere('credit_account_id', $account->id))
                ->when($request->from_date, fn ($q) => $q->where('transaction_date', '>=', $request->from_date))
                ->when($request->to_date, fn ($q) => $q->where('transaction_date', '<=', $request->to_date))
                ->orderBy('transaction_date')
                ->paginate(50)
                ->withQueryString();
        }

        return view('reports.ledger.cash', compact('accounts', 'account', 'transactions'));
    }

    public function bank(Request $request)
    {
        $accounts    = Account::where('account_type', 'Bank')->where('status', true)->get();
        $account     = $accounts->firstWhere('id', $request->account_id) ?? $accounts->first();
        $transactions = [];

        if ($account) {
            $transactions = Transaction::with(['debitAccount', 'creditAccount', 'creator'])
                ->where(fn ($q) => $q->where('debit_account_id', $account->id)->orWhere('credit_account_id', $account->id))
                ->when($request->from_date, fn ($q) => $q->where('transaction_date', '>=', $request->from_date))
                ->when($request->to_date, fn ($q) => $q->where('transaction_date', '<=', $request->to_date))
                ->orderBy('transaction_date')
                ->paginate(50)
                ->withQueryString();
        }

        return view('reports.ledger.bank', compact('accounts', 'account', 'transactions'));
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

    public function vendor(Request $request, Vendor $vendor)
    {
        $bills = $vendor->bills()
            ->with(['items', 'transaction'])
            ->when($request->from_date, fn ($q) => $q->where('bill_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('bill_date', '<=', $request->to_date))
            ->latest('bill_date')
            ->paginate(50)
            ->withQueryString();

        return view('reports.ledger.vendor', compact('vendor', 'bills'));
    }
}
