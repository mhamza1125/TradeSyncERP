<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\PayVendorBillRequest;
use App\Http\Requests\Finance\StoreVendorBillRequest;
use App\Http\Requests\Finance\UpdateVendorBillRequest;
use App\Models\Account;
use App\Models\Inspection;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\VendorBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorBillController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:vendor-bills.index')->only(['index', 'show']);
        $this->middleware('permission:vendor-bills.create')->only(['create', 'store']);
        $this->middleware('permission:vendor-bills.edit')->only(['edit', 'update']);
        $this->middleware('permission:vendor-bills.delete')->only('destroy');
        $this->middleware('permission:vendor-bills.pay')->only('pay');
    }

    public function index(Request $request)
    {
        $bills = VendorBill::with('vendor')
            ->when($request->vendor_id, fn ($q) => $q->where('vendor_id', $request->vendor_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from_date, fn ($q) => $q->where('bill_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('bill_date', '<=', $request->to_date))
            ->latest('bill_date')
            ->paginate(20)
            ->withQueryString();

        $vendors = Vendor::where('status', true)->orderBy('vendor_name')->get();
        return view('finance.vendor-bills.index', compact('bills', 'vendors'));
    }

    public function create()
    {
        $vendors     = Vendor::where('status', true)->orderBy('vendor_name')->get();
        $inspections = Inspection::where('overall_status', '!=', 'Pending')->get();
        return view('finance.vendor-bills.create', compact('vendors', 'inspections'));
    }

    public function store(StoreVendorBillRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['bill_number'] = $this->generateBillNumber();

            $bill = VendorBill::create($data);

            foreach ($data['items'] as $item) {
                $item['line_total'] = round($item['quantity'] * $item['unit_price'], 2);
                $bill->items()->create($item);
            }

            $bill->recalculateTotal();

            if (!empty($data['inspection_ids'])) {
                $bill->inspections()->sync($data['inspection_ids']);
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'bill' => $bill->load('items', 'vendor')]);
            }

            return redirect()->route('vendor-bills.show', $bill)
                ->with('success', "Bill {$bill->bill_number} created.");
        });
    }

    public function show(VendorBill $vendorBill)
    {
        $vendorBill->load(['vendor', 'items', 'inspections.sample', 'transaction']);
        return view('finance.vendor-bills.show', compact('vendorBill'));
    }

    public function edit(VendorBill $vendorBill)
    {
        abort_if($vendorBill->status === 'Paid', 403, 'Paid bills cannot be edited.');

        $vendors     = Vendor::where('status', true)->orderBy('vendor_name')->get();
        $inspections = Inspection::where('overall_status', '!=', 'Pending')->get();
        $vendorBill->load('items', 'inspections');

        return view('finance.vendor-bills.edit', compact('vendorBill', 'vendors', 'inspections'));
    }

    public function update(UpdateVendorBillRequest $request, VendorBill $vendorBill)
    {
        abort_if($vendorBill->status === 'Paid', 403, 'Paid bills cannot be edited.');

        return DB::transaction(function () use ($request, $vendorBill) {
            $data = $request->validated();

            $vendorBill->update($data);
            $vendorBill->items()->delete();

            foreach ($data['items'] as $item) {
                $item['line_total'] = round($item['quantity'] * $item['unit_price'], 2);
                $vendorBill->items()->create($item);
            }

            $vendorBill->recalculateTotal();

            if (!empty($data['inspection_ids'])) {
                $vendorBill->inspections()->sync($data['inspection_ids']);
            } else {
                $vendorBill->inspections()->detach();
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('vendor-bills.show', $vendorBill)
                ->with('success', 'Bill updated.');
        });
    }

    public function destroy(VendorBill $vendorBill)
    {
        abort_if($vendorBill->status === 'Paid', 403, 'Paid bills cannot be deleted.');
        $vendorBill->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('vendor-bills.index')->with('success', 'Bill deleted.');
    }

    public function pay(PayVendorBillRequest $request, VendorBill $vendorBill)
    {
        abort_if($vendorBill->status === 'Paid', 403, 'Bill already paid.');

        return DB::transaction(function () use ($request, $vendorBill) {
            $account = Account::findOrFail($request->account_id);

            $transaction = Transaction::create([
                'transaction_date' => $request->payment_date,
                'transaction_type' => 'VendorPayment',
                'reference_type'   => 'vendor_bill',
                'reference_id'     => $vendorBill->id,
                'debit_account_id' => $vendorBill->vendor_id,
                'credit_account_id' => $account->id,
                'amount'           => $vendorBill->total_amount,
                'remarks'          => $request->remarks,
                'created_by'       => auth()->id(),
            ]);

            $vendorBill->update([
                'status'         => 'Paid',
                'transaction_id' => $transaction->id,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('vendor-bills.show', $vendorBill)
                ->with('success', 'Bill marked as paid.');
        });
    }

    private function generateBillNumber(): string
    {
        $year    = now()->year;
        $lastId  = VendorBill::max('id') ?? 0;
        $nextSeq = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
        return "BILL-{$year}-{$nextSeq}";
    }
}
