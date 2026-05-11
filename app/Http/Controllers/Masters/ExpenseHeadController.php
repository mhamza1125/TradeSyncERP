<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreExpenseHeadRequest;
use App\Http\Requests\Masters\UpdateExpenseHeadRequest;
use App\Models\ExpenseHead;
use Illuminate\Http\Request;

class ExpenseHeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:expense-heads.index')->only(['index', 'show']);
        $this->middleware('permission:expense-heads.create')->only(['create', 'store']);
        $this->middleware('permission:expense-heads.edit')->only(['edit', 'update']);
        $this->middleware('permission:expense-heads.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $expenseHeads = ExpenseHead::query()
            ->when($request->search, fn ($q, $s) => $q->where('expense_name', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->withCount('expenses')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.expense-heads.index', compact('expenseHeads'));
    }

    public function create()
    {
        return view('masters.expense-heads.create');
    }

    public function store(StoreExpenseHeadRequest $request)
    {
        $expenseHead = ExpenseHead::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'expenseHead' => $expenseHead]);
        }

        return redirect()->route('masters.expense-heads.index')
            ->with('success', 'Expense head created successfully.');
    }

    public function show(ExpenseHead $expenseHead)
    {
        return view('masters.expense-heads.show', compact('expenseHead'));
    }

    public function edit(ExpenseHead $expenseHead)
    {
        return view('masters.expense-heads.edit', compact('expenseHead'));
    }

    public function update(UpdateExpenseHeadRequest $request, ExpenseHead $expenseHead)
    {
        $expenseHead->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'expenseHead' => $expenseHead]);
        }

        return redirect()->route('masters.expense-heads.index')
            ->with('success', 'Expense head updated successfully.');
    }

    public function destroy(ExpenseHead $expenseHead)
    {
        $expenseHead->update(['status' => false]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.expense-heads.index')
            ->with('success', 'Expense head deactivated successfully.');
    }
}
