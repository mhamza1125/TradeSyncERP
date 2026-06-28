<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreSupplierRequest;
use App\Http\Requests\Masters\UpdateSupplierRequest;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:suppliers.index')->only(['index', 'show', 'exportPdf', 'exportSinglePdf']);
        $this->middleware('permission:suppliers.create')->only(['create', 'store']);
        $this->middleware('permission:suppliers.edit')->only(['edit', 'update']);
        $this->middleware('permission:suppliers.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $suppliers = Supplier::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('city', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('masters.suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'supplier' => $supplier]);
        }

        return redirect()->route('masters.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('samples', 'customers');
        return view('masters.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('masters.suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'supplier' => $supplier]);
        }

        return redirect()->route('masters.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function exportPdf(Request $request)
    {
        $suppliers = Supplier::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.suppliers-list-pdf', compact('suppliers'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('Suppliers-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportSinglePdf(Supplier $supplier)
    {
        $supplier->load(['samples', 'customers']);

        $pdf = Pdf::loadView('exports.supplier-profile-pdf', compact('supplier'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download("Supplier-{$supplier->name}.pdf");
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.suppliers.index')
            ->with('success', 'Supplier removed successfully.');
    }
}
