<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreCustomerRequest;
use App\Http\Requests\Masters\UpdateCustomerRequest;
use App\Models\Currency;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customers.index')->only(['index', 'show', 'exportPdf', 'exportSinglePdf']);
        $this->middleware('permission:customers.create')->only(['create', 'store']);
        $this->middleware('permission:customers.edit')->only(['edit', 'update']);
        $this->middleware('permission:customers.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->search, fn ($q, $s) => $q->where('customer_name', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->with('currency')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.customers.index', compact('customers'));
    }

    public function create()
    {
        $currencies = Currency::where('status', true)->get();
        return view('masters.customers.create', compact('currencies'));
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return redirect()->route('masters.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['currency', 'payments' => fn ($q) => $q->latest()->limit(10), 'attachments']);
        return view('masters.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $currencies = Currency::where('status', true)->get();
        return view('masters.customers.edit', compact('customer', 'currencies'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return redirect()->route('masters.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function exportPdf(Request $request)
    {
        $customers = Customer::with('currency')
            ->when($request->search, fn ($q, $s) => $q->where('customer_name', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->orderBy('customer_name')
            ->get();

        $pdf = Pdf::loadView('exports.customers-list-pdf', compact('customers'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('Customers-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportSinglePdf(Customer $customer)
    {
        $customer->load(['currency', 'payments' => fn ($q) => $q->latest()->limit(20)]);

        $pdf = Pdf::loadView('exports.customer-profile-pdf', compact('customer'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download("Customer-{$customer->customer_name}.pdf");
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.customers.index')
            ->with('success', 'Customer deactivated successfully.');
    }
}
