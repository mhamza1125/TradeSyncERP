<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreAccountRequest;
use App\Http\Requests\Masters\UpdateAccountRequest;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:accounts.index')->only(['index', 'show', 'exportPdf']);
        $this->middleware('permission:accounts.create')->only(['create', 'store']);
        $this->middleware('permission:accounts.edit')->only(['edit', 'update']);
        $this->middleware('permission:accounts.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $accounts = Account::with('bank')
            ->when($request->search, fn ($q, $s) => $q->where('account_name', 'like', "%{$s}%"))
            ->when($request->account_type, fn ($q) => $q->where('account_type', $request->account_type))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.accounts.index', compact('accounts'));
    }

    public function create()
    {
        $banks      = Bank::where('status', true)->orderBy('bank_name')->get();
        $currencies = Currency::where('status', true)->orderBy('currency_code')->get();
        return view('masters.accounts.create', compact('banks', 'currencies'));
    }

    public function store(StoreAccountRequest $request)
    {
        $account = Account::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'account' => $account]);
        }

        return redirect()->route('masters.accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function show(Account $account)
    {
        return view('masters.accounts.show', compact('account'));
    }

    public function edit(Account $account)
    {
        $banks      = Bank::where('status', true)->orderBy('bank_name')->get();
        $currencies = Currency::where('status', true)->orderBy('currency_code')->get();
        return view('masters.accounts.edit', compact('account', 'banks', 'currencies'));
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'account' => $account]);
        }

        return redirect()->route('masters.accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        $account->update(['status' => false]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.accounts.index')
            ->with('success', 'Account deactivated successfully.');
    }

    public function exportPdf(Request $request)
    {
        $accounts = Account::with('bank')
            ->when($request->search, fn ($q, $s) => $q->where('account_name', 'like', "%{$s}%"))
            ->when($request->account_type, fn ($q) => $q->where('account_type', $request->account_type))
            ->orderBy('account_name')
            ->get();

        $pdf = Pdf::loadView('exports.accounts-list-pdf', compact('accounts'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('Accounts-' . now()->format('Y-m-d') . '.pdf');
    }
}
