<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Services\Finance\LedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function __construct(private readonly LedgerService $ledger)
    {
        $this->middleware('permission:reports.view');
    }

    public function cash(Request $request)
    {
        return $this->accountLedger($request, 'Cash', 'reports.ledger.cash');
    }

    public function bank(Request $request)
    {
        return $this->accountLedger($request, 'Bank', 'reports.ledger.bank');
    }

    /** Cash and bank pages: one rule, one opening, via LedgerService (docs/FINANCE_RULES.md). */
    private function accountLedger(Request $request, string $type, string $view)
    {
        $accounts = Account::where('account_type', $type)->where('status', true)->get();
        $account = $accounts->firstWhere('id', $request->account_id) ?? $accounts->first();
        $statement = $account
            ? $this->ledger->statement($account, $request->from_date, $request->to_date, 50, max(1, (int) $request->input('page', 1)))
            : null;

        $transactions = $statement?->paginator?->withQueryString() ?? collect();
        $openingBalance = $statement?->opening ?? '0.00';

        return view($view, compact('accounts', 'account', 'statement', 'transactions', 'openingBalance'));
    }

    public function customer(Request $request, Customer $customer)
    {
        $payments = $customer->payments()
            ->notVoided()
            ->with('account')
            ->when($request->from_date, fn ($q) => $q->where('payment_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('payment_date', '<=', $request->to_date))
            ->latest('payment_date')
            ->paginate(50)
            ->withQueryString();

        return view('reports.ledger.customer', compact('customer', 'payments'));
    }

    public function exportCash(Request $request)
    {
        $accounts = Account::where('account_type', 'Cash')->where('status', true)->get();
        $account = $accounts->firstWhere('id', $request->account_id) ?? $accounts->first();

        $statement = $account ? $this->ledger->statement($account, $request->from_date, $request->to_date) : null;
        $transactions = $statement?->rows ?? collect();
        $openingBalance = $statement?->opening ?? '0.00';
        $closingBalance = $statement?->closing ?? '0.00';

        $pdf = Pdf::loadView('exports.ledger-pdf', [
            'title' => 'Cash Ledger',
            'account' => $account,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'filters' => $request->only('from_date', 'to_date'),
            'customer' => null,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream('CashLedger-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportBank(Request $request)
    {
        $accounts = Account::where('account_type', 'Bank')->where('status', true)->get();
        $account = $accounts->firstWhere('id', $request->account_id) ?? $accounts->first();

        $statement = $account ? $this->ledger->statement($account, $request->from_date, $request->to_date) : null;
        $transactions = $statement?->rows ?? collect();
        $openingBalance = $statement?->opening ?? '0.00';
        $closingBalance = $statement?->closing ?? '0.00';

        $pdf = Pdf::loadView('exports.ledger-pdf', [
            'title' => 'Bank Ledger',
            'account' => $account,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'filters' => $request->only('from_date', 'to_date'),
            'customer' => null,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream('BankLedger-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportCustomer(Request $request, Customer $customer)
    {
        $transactions = $customer->payments()
            ->notVoided()
            ->with('account')
            ->when($request->from_date, fn ($q) => $q->where('payment_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('payment_date', '<=', $request->to_date))
            ->latest('payment_date')
            ->get();

        $pdf = Pdf::loadView('exports.ledger-pdf', [
            'title' => 'Customer Ledger',
            'customer' => $customer->load('currency'),
            'transactions' => $transactions,
            'account' => null,
            'openingBalance' => 0,
            'closingBalance' => 0,
            'filters' => $request->only('from_date', 'to_date'),
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream("CustomerLedger-{$customer->customer_name}-".now()->format('Y-m-d').'.pdf');
    }
}
