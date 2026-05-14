<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreCustomerOrderRequest;
use App\Http\Requests\Operations\UpdateCustomerOrderRequest;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\CustomerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer-orders.index')->only(['index', 'show']);
        $this->middleware('permission:customer-orders.create')->only(['create', 'store']);
        $this->middleware('permission:customer-orders.edit')->only(['edit', 'update']);
        $this->middleware('permission:customer-orders.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $orders = CustomerOrder::with(['customer', 'brand'])
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
        $customers = Customer::where('status', true)->orderBy('customer_name')->get();
        $brands    = Brand::where('status', true)->orderBy('brand_name')->get();

        return view('operations.customer-orders.create', compact('customers', 'brands'));
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
        $customerOrder->load(['customer', 'brand', 'items']);

        return view('operations.customer-orders.show', compact('customerOrder'));
    }

    public function edit(CustomerOrder $customerOrder)
    {
        $customerOrder->load('items');
        $customers = Customer::where('status', true)->orderBy('customer_name')->get();
        $brands    = Brand::where('status', true)
            ->where('customer_id', $customerOrder->customer_id)
            ->orderBy('brand_name')
            ->get();

        return view('operations.customer-orders.edit', compact('customerOrder', 'customers', 'brands'));
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

    private function generateOrderCode(): string
    {
        $year  = now()->year;
        $last  = CustomerOrder::withTrashed()
            ->where('order_code', 'like', "CSO-{$year}-%")
            ->count();

        return sprintf('CSO-%d-%05d', $year, $last + 1);
    }
}
