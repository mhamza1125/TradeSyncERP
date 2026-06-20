<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreExpenseRequest;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:expenses.index')->only(['index', 'show']);
        $this->middleware('permission:expenses.create')->only(['create', 'store']);
        $this->middleware('permission:expenses.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $expenses = Expense::with(['expenseHead', 'account'])
            ->when($request->expense_head_id, fn ($q) => $q->where('expense_head_id', $request->expense_head_id))
            ->when($request->account_id, fn ($q) => $q->where('account_id', $request->account_id))
            ->when($request->from_date, fn ($q) => $q->where('expense_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('expense_date', '<=', $request->to_date))
            ->latest('expense_date')
            ->paginate(20)
            ->withQueryString();

        $expenseHeads = ExpenseHead::where('status', true)->orderBy('expense_name')->get();
        $accounts     = Account::where('status', true)->orderBy('account_name')->get();

        return view('finance.expenses.index', compact('expenses', 'expenseHeads', 'accounts'));
    }

    public function create()
    {
        $expenseHeads = ExpenseHead::with(['children' => fn ($q) => $q->where('status', true)->orderBy('expense_name')])
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('expense_name')
            ->get();
        $accounts = Account::where('status', true)->orderBy('account_name')->get();
        return view('finance.expenses.create', compact('expenseHeads', 'accounts'));
    }

    public function store(StoreExpenseRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')->store('expenses', 'public');
            }

            $account = Account::findOrFail($data['account_id']);

            $transaction = Transaction::create([
                'transaction_date'  => $data['expense_date'],
                'transaction_type'  => 'Expense',
                'reference_type'    => 'expense',
                'debit_account_id'  => $data['account_id'],
                'credit_account_id' => $data['account_id'],
                'amount'            => $data['amount'],
                'remarks'           => $data['description'] ?? null,
                'created_by'        => auth()->id(),
            ]);

            $data['transaction_id'] = $transaction->id;
            $expense = Expense::create($data);

            $transaction->update(['reference_id' => $expense->id]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'expense' => $expense]);
            }

            return redirect()->route('expenses.index')
                ->with('success', 'Expense recorded successfully.');
        });
    }

    public function show(Expense $expense)
    {
        $expense->load(['expenseHead', 'account', 'transaction']);
        return view('finance.expenses.show', compact('expense'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    public function exportPdf(Request $request)
    {
        $expenses = Expense::with(['expenseHead', 'account', 'transaction.creator'])
            ->when($request->expense_head_id, fn ($q) => $q->where('expense_head_id', $request->expense_head_id))
            ->when($request->account_id,      fn ($q) => $q->where('account_id', $request->account_id))
            ->when($request->from_date,       fn ($q) => $q->where('expense_date', '>=', $request->from_date))
            ->when($request->to_date,         fn ($q) => $q->where('expense_date', '<=', $request->to_date))
            ->latest('expense_date')
            ->get();

        $filters = array_filter([
            'Expense Head' => $request->expense_head_id
                ? optional(ExpenseHead::find($request->expense_head_id))->expense_name
                : null,
            'Account'   => $request->account_id
                ? optional(Account::find($request->account_id))->account_name
                : null,
            'From Date' => $request->from_date
                ? \Carbon\Carbon::parse($request->from_date)->format('d M Y')
                : null,
            'To Date'   => $request->to_date
                ? \Carbon\Carbon::parse($request->to_date)->format('d M Y')
                : null,
        ]);

        $total = $expenses->sum('amount');

        $pdf = Pdf::loadView('exports.expenses-pdf', compact('expenses', 'filters', 'total'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'sans-serif');

        $filename = 'Expenses-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
