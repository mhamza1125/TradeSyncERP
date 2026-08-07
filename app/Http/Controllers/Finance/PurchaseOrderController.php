<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StorePurchaseOrderRequest;
use App\Http\Requests\Finance\UpdatePurchaseOrderRequest;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchase-orders.index')->only(['index', 'show', 'exportPdf', 'exportListPdf']);
        $this->middleware('permission:purchase-orders.create')->only(['create', 'store']);
        $this->middleware('permission:purchase-orders.edit')->only(['edit', 'update']);
        $this->middleware('permission:purchase-orders.delete')->only('destroy');
        $this->middleware('permission:purchase-orders.pay')->only('pay');
    }

    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::query()
            ->when($request->search, fn ($q, $s) => $q->where('po_number', 'like', "%{$s}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from_date, fn ($q) => $q->where('po_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('po_date', '<=', $request->to_date))
            ->latest('po_date')
            ->paginate(20)
            ->withQueryString();

        return view('finance.purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        return view('finance.purchase-orders.create');
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);

            $data['po_number'] = $this->generatePoNumber();
            $data['total_amount'] = $this->calculateItemsTotal($items);
            $data['amount_due'] = $data['total_amount'];

            $purchaseOrder = PurchaseOrder::create($data);
            $this->saveItems($purchaseOrder, $items);

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', "Purchase order {$purchaseOrder->po_number} created successfully.");
        });
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items', 'expenses.account', 'attachments']);

        $accounts = Account::where('status', true)->whereIn('account_type', ['Cash', 'Bank'])->get();

        return view('finance.purchase-orders.show', compact('purchaseOrder', 'accounts'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        abort_if($purchaseOrder->isPaid(), 403, 'A fully paid purchase order cannot be edited.');

        $purchaseOrder->load('items');

        return view('finance.purchase-orders.edit', compact('purchaseOrder'));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        abort_if($purchaseOrder->isPaid(), 403, 'A fully paid purchase order cannot be edited.');

        return DB::transaction(function () use ($request, $purchaseOrder) {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);

            $newTotal = $this->calculateItemsTotal($items);

            if ($newTotal < $purchaseOrder->amount_paid) {
                return back()
                    ->withErrors(['items' => 'The order total cannot be less than the amount already paid ('.number_format($purchaseOrder->amount_paid, 2).').'])
                    ->withInput();
            }

            $data['total_amount'] = $newTotal;
            $purchaseOrder->update($data);

            $purchaseOrder->items()->delete();
            $this->saveItems($purchaseOrder, $items);

            $purchaseOrder->recalculatePaymentStatus();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order updated successfully.');
        });
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        abort_if($purchaseOrder->amount_paid > 0, 403, 'A purchase order with recorded payments cannot be deleted.');

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted.');
    }

    /**
     * Record a payment against a purchase order. Creates the Transaction +
     * Expense (linked back via purchase_order_id) the same way
     * ExpenseController::store() does, then recalculates the PO's payment
     * status. Supports partial payments — this can be called repeatedly
     * until the order is fully paid.
     */
    public function pay(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_if($purchaseOrder->isPaid(), 403, 'This purchase order is already fully paid.');

        $data = $request->validate([
            'payment_date' => ['required', 'date'],
            'account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.max((float) $purchaseOrder->amount_due, 0.01)],
            'remarks' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($data, $purchaseOrder) {
            $expenseHead = ExpenseHead::firstOrCreate(
                ['expense_name' => PurchaseOrder::EXPENSE_HEAD_NAME],
                ['status' => true]
            );

            $transaction = Transaction::create([
                'transaction_date' => $data['payment_date'],
                'transaction_type' => 'Expense',
                'reference_type' => 'expense',
                'debit_account_id' => $data['account_id'],
                'credit_account_id' => $data['account_id'],
                'amount' => $data['amount'],
                'remarks' => $data['remarks'] ?? "Payment for PO {$purchaseOrder->po_number}",
                'created_by' => auth()->id(),
            ]);

            $expense = Expense::create([
                'expense_head_id' => $expenseHead->id,
                'purchase_order_id' => $purchaseOrder->id,
                'account_id' => $data['account_id'],
                'transaction_id' => $transaction->id,
                'amount' => $data['amount'],
                'expense_date' => $data['payment_date'],
                'description' => $data['remarks'] ?? "Payment for PO {$purchaseOrder->po_number}",
            ]);

            $transaction->update(['reference_id' => $expense->id]);

            $purchaseOrder->recalculatePaymentStatus();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'expense' => $expense]);
            }

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Payment recorded successfully.');
        });
    }

    public function exportListPdf(Request $request)
    {
        $purchaseOrders = PurchaseOrder::query()
            ->when($request->search, fn ($q, $s) => $q->where('po_number', 'like', "%{$s}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('po_date')
            ->get();

        $pdf = Pdf::loadView('exports.purchase-orders-list-pdf', compact('purchaseOrders'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream('PurchaseOrders-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items', 'expenses.account']);

        $pdf = Pdf::loadView('exports.purchase-order-pdf', ['purchaseOrder' => $purchaseOrder])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream("PurchaseOrder-{$purchaseOrder->po_number}.pdf");
    }

    private function calculateItemsTotal(array $items): float
    {
        return round(collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']), 2);
    }

    private function saveItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        foreach ($items as $item) {
            $purchaseOrder->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_amount' => round($item['quantity'] * $item['unit_price'], 2),
            ]);
        }
    }

    private function generatePoNumber(): string
    {
        $year = now()->year;
        $lastId = PurchaseOrder::withTrashed()->max('id') ?? 0;
        $nextSeq = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

        return "PO-{$year}-{$nextSeq}";
    }
}
