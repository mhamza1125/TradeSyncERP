<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCustomerInvoiceRequest;
use App\Http\Requests\Finance\UpdateCustomerInvoiceRequest;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\InspectionType;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer-invoices.index')->only(['index', 'show']);
        $this->middleware('permission:customer-invoices.create')->only(['create', 'store']);
        $this->middleware('permission:customer-invoices.edit')->only(['edit', 'update']);
        $this->middleware('permission:customer-invoices.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $invoices = CustomerInvoice::with('customer')
            ->when($request->search, fn ($q, $s) =>
                $q->where('invoice_number', 'like', "%{$s}%"))
            ->when($request->customer_id, fn ($q) =>
                $q->where('customer_id', $request->customer_id))
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status))
            ->when($request->from_date, fn ($q) =>
                $q->where('invoice_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) =>
                $q->where('invoice_date', '<=', $request->to_date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::where('status', true)->orderBy('customer_name')->get();

        return view('finance.customer-invoices.index', compact('invoices', 'customers'));
    }

    public function create()
    {
        $customers       = Customer::with('currency')->where('status', true)->orderBy('customer_name')->get();
        $suppliers       = Supplier::orderBy('name')->get();
        $inspectionTypes = InspectionType::where('status', true)->orderBy('name')->get();

        return view('finance.customer-invoices.create', compact('customers', 'suppliers', 'inspectionTypes'));
    }

    public function store(StoreCustomerInvoiceRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['invoice_number'] = $this->generateInvoiceNumber();

            $subtotal = collect($data['items'])->sum('amount');
            $data['subtotal']        = $subtotal;
            $data['tax_amount']      = $data['tax_amount'] ?? 0;
            $data['discount_amount'] = $data['discount_amount'] ?? 0;
            $data['total_amount']    = $subtotal + $data['tax_amount'] - $data['discount_amount'];
            $data['amount_due']      = $data['total_amount'];

            $items = $data['items'];
            unset($data['items']);

            $invoice = CustomerInvoice::create($data);
            $invoice->items()->createMany($items);

            return redirect()->route('customer-invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
        });
    }

    public function show(CustomerInvoice $customerInvoice)
    {
        $customerInvoice->load(['customer.currency', 'items.supplier', 'items.inspectionType', 'attachments']);
        return view('finance.customer-invoices.show', compact('customerInvoice'));
    }

    public function edit(CustomerInvoice $customerInvoice)
    {
        $customers       = Customer::with('currency')->where('status', true)->orderBy('customer_name')->get();
        $suppliers       = Supplier::orderBy('name')->get();
        $inspectionTypes = InspectionType::where('status', true)->orderBy('name')->get();
        $customerInvoice->load(['items', 'attachments']);

        return view('finance.customer-invoices.edit', compact('customerInvoice', 'customers', 'suppliers', 'inspectionTypes'));
    }

    public function update(UpdateCustomerInvoiceRequest $request, CustomerInvoice $customerInvoice)
    {
        return DB::transaction(function () use ($request, $customerInvoice) {
            $data = $request->validated();

            $subtotal = collect($data['items'])->sum('amount');
            $data['subtotal']        = $subtotal;
            $data['tax_amount']      = $data['tax_amount'] ?? 0;
            $data['discount_amount'] = $data['discount_amount'] ?? 0;
            $data['total_amount']    = $subtotal + $data['tax_amount'] - $data['discount_amount'];
            $data['amount_due']      = $data['total_amount'] - $customerInvoice->amount_paid;

            $items = $data['items'];
            unset($data['items']);

            $customerInvoice->update($data);
            $customerInvoice->items()->delete();
            $customerInvoice->items()->createMany($items);

            return redirect()->route('customer-invoices.show', $customerInvoice)
                ->with('success', 'Invoice updated successfully.');
        });
    }

    public function destroy(CustomerInvoice $customerInvoice)
    {
        $customerInvoice->delete();
        return redirect()->route('customer-invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    private function generateInvoiceNumber(): string
    {
        $year    = now()->year;
        $lastId  = CustomerInvoice::withTrashed()->max('id') ?? 0;
        $nextSeq = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
        return "INV-{$year}-{$nextSeq}";
    }
}
