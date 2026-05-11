<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCustomerPaymentRequest;
use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer-payments.index')->only(['index', 'show']);
        $this->middleware('permission:customer-payments.create')->only(['create', 'store']);
        $this->middleware('permission:customer-payments.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $payments = CustomerPayment::with(['customer', 'account'])
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->from_date, fn ($q) => $q->where('payment_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('payment_date', '<=', $request->to_date))
            ->latest('payment_date')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::where('status', true)->orderBy('customer_name')->get();
        return view('finance.customer-payments.index', compact('payments', 'customers'));
    }

    public function create()
    {
        $customers = Customer::where('status', true)->orderBy('customer_name')->get();
        $accounts  = Account::where('status', true)->whereIn('account_type', ['Cash', 'Bank'])->get();
        return view('finance.customer-payments.create', compact('customers', 'accounts'));
    }

    public function store(StoreCustomerPaymentRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            $data['expected_pkr']  = round($data['received_fc'] * $data['exchange_rate'], 2);
            $data['pkr_gain_loss'] = round($data['actual_pkr_received'] - $data['expected_pkr'], 2);
            $data['fc_gain_loss']  = round($data['invoiced_amount_fc'] - $data['received_fc'], 2);
            $data['deduction_fc']  = round($data['invoiced_amount_fc'] - $data['received_fc'], 2);

            $transaction = Transaction::create([
                'transaction_date'  => $data['payment_date'],
                'transaction_type'  => 'CustomerReceipt',
                'reference_type'    => 'customer_payment',
                'debit_account_id'  => $data['debit_account_id'],
                'credit_account_id' => $data['account_id'],
                'amount'            => $data['actual_pkr_received'],
                'remarks'           => $data['remarks'] ?? null,
                'created_by'        => auth()->id(),
            ]);

            $data['transaction_id'] = $transaction->id;
            unset($data['debit_account_id']);

            $payment = CustomerPayment::create($data);

            $transaction->update(['reference_id' => $payment->id]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'payment' => $payment]);
            }

            return redirect()->route('customer-payments.show', $payment)
                ->with('success', 'Payment recorded successfully.');
        });
    }

    public function show(CustomerPayment $customerPayment)
    {
        $customerPayment->load(['customer', 'account', 'transaction.debitAccount', 'transaction.creditAccount']);
        return view('finance.customer-payments.show', compact('customerPayment'));
    }

    public function destroy(CustomerPayment $customerPayment)
    {
        $customerPayment->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('customer-payments.index')->with('success', 'Payment deleted.');
    }
}
