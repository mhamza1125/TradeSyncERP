<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreBankRequest;
use App\Http\Requests\Masters\UpdateBankRequest;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:banks.index')->only(['index', 'show']);
        $this->middleware('permission:banks.create')->only(['create', 'store']);
        $this->middleware('permission:banks.edit')->only(['edit', 'update']);
        $this->middleware('permission:banks.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $banks = Bank::query()
            ->when($request->search, fn ($q, $s) => $q->where('bank_name', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->withCount('accounts')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.banks.index', compact('banks'));
    }

    public function create()
    {
        return view('masters.banks.create');
    }

    public function store(StoreBankRequest $request)
    {
        $bank = Bank::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'bank' => $bank]);
        }

        return redirect()->route('masters.banks.index')
            ->with('success', 'Bank created successfully.');
    }

    public function show(Bank $bank)
    {
        $bank->load('accounts');
        return view('masters.banks.show', compact('bank'));
    }

    public function edit(Bank $bank)
    {
        return view('masters.banks.edit', compact('bank'));
    }

    public function update(UpdateBankRequest $request, Bank $bank)
    {
        $bank->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'bank' => $bank]);
        }

        return redirect()->route('masters.banks.index')
            ->with('success', 'Bank updated successfully.');
    }

    public function destroy(Bank $bank)
    {
        $bank->update(['status' => false]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.banks.index')
            ->with('success', 'Bank deactivated successfully.');
    }
}
