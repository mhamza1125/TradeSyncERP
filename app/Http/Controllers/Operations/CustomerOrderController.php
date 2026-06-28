<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreCustomerOrderRequest;
use App\Http\Requests\Operations\UpdateCustomerOrderRequest;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\ProductCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer-orders.index')->only(['index', 'show', 'exportPdf', 'exportListPdf']);
        $this->middleware('permission:customer-orders.create')->only(['create', 'store']);
        $this->middleware('permission:customer-orders.edit')->only(['edit', 'update']);
        $this->middleware('permission:customer-orders.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $orders = CustomerOrder::with('customer')
            ->when($request->search, fn ($q) => $q->where('order_code', 'like', "%{$request->search}%"))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from_date, fn ($q) => $q->where('order_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('order_date', '<=', $request->to_date))
            ->latest('order_date')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::where('status', true)->orderBy('customer_name')->get();

        return view('operations.customer-orders.index', compact('orders', 'customers'));
    }

    public function create()
    {
        $customers  = Customer::where('status', true)->orderBy('customer_name')->get();
        $categories = ProductCategory::orderBy('category_name')->get();

        return view('operations.customer-orders.create', compact('customers', 'categories'));
    }

    public function store(StoreCustomerOrderRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['order_code'] = $this->generateOrderCode();

            $items = $data['items'];
            unset($data['items']);

            $order = CustomerOrder::create($data);
            $order->items()->createMany($items);

            return redirect()->route('customer-orders.show', $order)
                ->with('success', "Customer order {$order->order_code} created successfully.");
        });
    }

    public function show(CustomerOrder $customerOrder)
    {
        $customerOrder->load(['customer', 'items.productCategory']);

        return view('operations.customer-orders.show', compact('customerOrder'));
    }

    public function edit(CustomerOrder $customerOrder)
    {
        $customerOrder->load('items');
        $customers  = Customer::where('status', true)->orderBy('customer_name')->get();
        $categories = ProductCategory::orderBy('category_name')->get();

        return view('operations.customer-orders.edit', compact('customerOrder', 'customers', 'categories'));
    }

    public function update(UpdateCustomerOrderRequest $request, CustomerOrder $customerOrder)
    {
        return DB::transaction(function () use ($request, $customerOrder) {
            $data  = $request->validated();
            $items = $data['items'];
            unset($data['items']);

            $customerOrder->update($data);
            $customerOrder->items()->delete();
            $customerOrder->items()->createMany($items);

            return redirect()->route('customer-orders.show', $customerOrder)
                ->with('success', 'Customer order updated successfully.');
        });
    }

    public function destroy(CustomerOrder $customerOrder)
    {
        $customerOrder->delete();

        return redirect()->route('customer-orders.index')
            ->with('success', 'Customer order deleted.');
    }

    public function exportListPdf(Request $request)
    {
        $orders = CustomerOrder::with(['customer', 'items'])
            ->when($request->search, fn ($q) => $q->where('order_code', 'like', "%{$request->search}%"))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->get();

        $pdf = Pdf::loadView('exports.customer-orders-list-pdf', compact('orders'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('CustomerOrders-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportPdf(CustomerOrder $customerOrder)
    {
        $customerOrder->load(['customer', 'items.productCategory']);

        $pdf = Pdf::loadView('exports.customer-order-pdf', ['order' => $customerOrder])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download("Order-{$customerOrder->order_code}.pdf");
    }

    private function generateOrderCode(): string
    {
        $year = now()->year;
        $last = CustomerOrder::withTrashed()
            ->where('order_code', 'like', "CSO-{$year}-%")
            ->count();

        return sprintf('CSO-%d-%05d', $year, $last + 1);
    }
}
