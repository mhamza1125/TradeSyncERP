<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreTransferRequest;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\Finance\LedgerService;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:transfers.create');
    }

    public function create()
    {
        $accounts = Account::where('status', true)->orderBy('account_name')->get();
        return view('finance.transfers.create', compact('accounts'));
    }

    public function store(StoreTransferRequest $request, LedgerService $ledger)
    {
        return DB::transaction(function () use ($request, $ledger) {
            $data = $request->validated();

            $fromAccount = Account::findOrFail($data['from_account_id']);

            // Same balance rule as the ledger (LedgerService), not raw debit − credit sums:
            // non-transfer rows name the account on both sides and would cancel out (audit FN-1).
            $currentBalance = $ledger->balanceAt($fromAccount);

            if (bccomp($currentBalance, bcadd((string) $data['amount'], '0', 2), 2) < 0) {
                return back()
                    ->withInput()
                    ->withErrors(['amount' => 'Insufficient balance in source account. Available: ' . number_format($currentBalance, 2)]);
            }

            // Destination account is debited (money IN), source account is credited (money OUT)
            Transaction::create([
                'transaction_date'  => $data['transaction_date'],
                'transaction_type'  => 'JournalEntry',
                'debit_account_id'  => $data['to_account_id'],
                'credit_account_id' => $data['from_account_id'],
                'amount'            => $data['amount'],
                'remarks'           => $data['remarks'] ?? null,
                'created_by'        => auth()->id(),
            ]);

            return redirect()->route('ledger.cash')
                ->with('success', 'Fund transfer recorded successfully.');
        });
    }
}
