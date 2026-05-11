<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreVendorRequest;
use App\Http\Requests\Masters\UpdateVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:vendors.index')->only(['index', 'show']);
        $this->middleware('permission:vendors.create')->only(['create', 'store']);
        $this->middleware('permission:vendors.edit')->only(['edit', 'update']);
        $this->middleware('permission:vendors.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $vendors = Vendor::query()
            ->when($request->search, fn ($q, $s) => $q->where('vendor_name', 'like', "%{$s}%")
                ->orWhere('company_name', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('masters.vendors.create');
    }

    public function store(StoreVendorRequest $request)
    {
        $vendor = Vendor::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'vendor' => $vendor]);
        }

        return redirect()->route('masters.vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['bills' => fn ($q) => $q->latest()->limit(10)]);
        return view('masters.vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        return view('masters.vendors.edit', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'vendor' => $vendor]);
        }

        return redirect()->route('masters.vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.vendors.index')
            ->with('success', 'Vendor removed successfully.');
    }
}
