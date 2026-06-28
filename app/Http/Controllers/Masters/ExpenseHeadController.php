<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreExpenseHeadRequest;
use App\Http\Requests\Masters\UpdateExpenseHeadRequest;
use App\Models\ExpenseHead;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExpenseHeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:expense-heads.index')->only(['index', 'show', 'exportPdf']);
        $this->middleware('permission:expense-heads.create')->only(['create', 'store']);
        $this->middleware('permission:expense-heads.edit')->only(['edit', 'update']);
        $this->middleware('permission:expense-heads.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $expenseHeads = ExpenseHead::query()
            ->when($request->search, fn ($q, $s) => $q->where('expense_name', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->with('parent', 'children')
            ->withCount('expenses')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.expense-heads.index', compact('expenseHeads'));
    }

    public function create()
    {
        $categories = ExpenseHead::whereNull('parent_id')->where('status', true)->orderBy('expense_name')->get();
        return view('masters.expense-heads.create', compact('categories'));
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
        $expenseHead->load('parent', 'children', 'expenses');
        return view('masters.expense-heads.show', compact('expenseHead'));
    }

    public function edit(ExpenseHead $expenseHead)
    {
        $categories = ExpenseHead::whereNull('parent_id')
            ->where('status', true)
            ->where('id', '!=', $expenseHead->id)
            ->orderBy('expense_name')
            ->get();
        return view('masters.expense-heads.edit', compact('expenseHead', 'categories'));
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

    public function exportPdf(Request $request)
    {
        $expenseHeads = ExpenseHead::with('parent')
            ->when($request->search, fn ($q, $s) => $q->where('expense_name', 'like', "%{$s}%"))
            ->orderBy('expense_name')
            ->get();

        $pdf = Pdf::loadView('exports.expense-heads-list-pdf', compact('expenseHeads'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('ExpenseHeads-' . now()->format('Y-m-d') . '.pdf');
    }
}
