<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreBrandRequest;
use App\Http\Requests\Masters\UpdateBrandRequest;
use App\Models\Brand;
use App\Models\Customer;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:brands.index')->only(['index', 'show']);
        $this->middleware('permission:brands.create')->only(['create', 'store']);
        $this->middleware('permission:brands.edit')->only(['edit', 'update']);
        $this->middleware('permission:brands.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $brands = Brand::with('customer')
            ->when($request->search, fn ($q, $s) => $q->where('brand_name', 'like', "%{$s}%"))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::where('status', true)->orderBy('customer_name')->get();

        return view('masters.brands.index', compact('brands', 'customers'));
    }

    public function create()
    {
        $customers = Customer::where('status', true)->orderBy('customer_name')->get();
        return view('masters.brands.create', compact('customers'));
    }

    public function store(StoreBrandRequest $request)
    {
        $brand = Brand::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'brand' => $brand->load('customer')]);
        }

        return redirect()->route('masters.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function show(Brand $brand)
    {
        $brand->load(['customer', 'samples']);
        return view('masters.brands.show', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        $customers = Customer::where('status', true)->orderBy('customer_name')->get();
        return view('masters.brands.edit', compact('brand', 'customers'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'brand' => $brand]);
        }

        return redirect()->route('masters.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $brand->update(['status' => false]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.brands.index')
            ->with('success', 'Brand deactivated successfully.');
    }
}
