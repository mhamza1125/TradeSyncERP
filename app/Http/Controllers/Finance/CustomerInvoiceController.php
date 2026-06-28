<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCustomerInvoiceRequest;
use App\Http\Requests\Finance\UpdateCustomerInvoiceRequest;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerPayment;
use App\Models\InspectionType;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer-invoices.index')->only(['index', 'show', 'byCustomer', 'exportPdf', 'exportListPdf']);
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

    public function byCustomer(Request $request)
    {
        $request->validate(['customer_id' => 'required|integer']);

        $invoices = CustomerInvoice::where('customer_id', $request->customer_id)
            ->where(function ($q) {
                $q->whereIn('status', ['Draft', 'Sent', 'Partial', 'Overdue'])
                  ->orWhere('amount_due', '>', 0);
            })
            ->orderBy('invoice_date', 'desc')
            ->get(['id', 'invoice_number', 'total_amount', 'amount_paid', 'amount_due', 'status']);

        return response()->json($invoices);
    }

    public function exportListPdf(Request $request)
    {
        $invoices = CustomerInvoice::with('customer')
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from_date, fn ($q) => $q->where('invoice_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('invoice_date', '<=', $request->to_date))
            ->latest('invoice_date')
            ->get();

        $pdf = Pdf::loadView('exports.customer-invoices-list-pdf', compact('invoices'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('CustomerInvoices-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportPdf(CustomerInvoice $customerInvoice)
    {
        $customerInvoice->load(['customer.currency', 'items.supplier', 'items.inspectionType']);

        $pdf = Pdf::loadView('exports.customer-invoice-pdf', ['invoice' => $customerInvoice])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download("Invoice-{$customerInvoice->invoice_number}.pdf");
    }

    public static function syncInvoiceStatus(?string $invoiceNumber): void
    {
        if (blank($invoiceNumber)) {
            return;
        }

        $invoice = CustomerInvoice::where('invoice_number', $invoiceNumber)->first();
        if (!$invoice) {
            return;
        }

        $totalPaid = CustomerPayment::where('invoice_reference', $invoiceNumber)->sum('received_fc');

        $invoice->amount_paid = $totalPaid;
        $invoice->amount_due  = max(0, $invoice->total_amount - $totalPaid);

        if ($invoice->amount_due <= 0) {
            $invoice->status = 'Paid';
        } elseif ($totalPaid > 0) {
            $invoice->status = 'Partial';
        }

        $invoice->save();
    }

    private function generateInvoiceNumber(): string
    {
        $year    = now()->year;
        $lastId  = CustomerInvoice::withTrashed()->max('id') ?? 0;
        $nextSeq = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
        return "INV-{$year}-{$nextSeq}";
    }
}
