<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreCurrencyRequest;
use App\Http\Requests\Masters\UpdateCurrencyRequest;
use App\Models\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:currencies.index')->only(['index', 'show', 'exportPdf']);
        $this->middleware('permission:currencies.create')->only(['create', 'store']);
        $this->middleware('permission:currencies.edit')->only(['edit', 'update']);
        $this->middleware('permission:currencies.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $currencies = Currency::query()
            ->when($request->search, fn ($q, $s) => $q->where('currency_name', 'like', "%{$s}%")
                ->orWhere('currency_code', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.currencies.index', compact('currencies'));
    }

    public function create()
    {
        return view('masters.currencies.create');
    }

    public function store(StoreCurrencyRequest $request)
    {
        $data = $request->validated();

        if (!empty($data['is_default'])) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency = Currency::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'currency' => $currency]);
        }

        return redirect()->route('masters.currencies.index')
            ->with('success', 'Currency created successfully.');
    }

    public function show(Currency $currency)
    {
        return view('masters.currencies.show', compact('currency'));
    }

    public function edit(Currency $currency)
    {
        return view('masters.currencies.edit', compact('currency'));
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        $data = $request->validated();

        if (!empty($data['is_default'])) {
            Currency::where('is_default', true)->where('id', '!=', $currency->id)->update(['is_default' => false]);
        }

        $currency->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'currency' => $currency]);
        }

        return redirect()->route('masters.currencies.index')
            ->with('success', 'Currency updated successfully.');
    }

    public function destroy(Currency $currency)
    {
        $currency->update(['status' => false]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.currencies.index')
            ->with('success', 'Currency deactivated successfully.');
    }

    public function exportPdf(Request $request)
    {
        $currencies = Currency::query()
            ->when($request->search, fn ($q, $s) => $q->where('currency_name', 'like', "%{$s}%")
                ->orWhere('currency_code', 'like', "%{$s}%"))
            ->orderBy('currency_name')
            ->get();

        $pdf = Pdf::loadView('exports.currencies-list-pdf', compact('currencies'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('Currencies-' . now()->format('Y-m-d') . '.pdf');
    }
}
