<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inspections.index')->only(['index', 'show']);
        $this->middleware('permission:inspections.create')->only(['create', 'store']);
        $this->middleware('permission:inspections.edit')->only(['edit', 'update']);
        $this->middleware('permission:inspections.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $inspections = Inspection::with(['inspectionType', 'inspectors', 'runs'])
            ->when($request->search, fn($q) =>
                $q->where('report_number', 'like', "%{$request->search}%"))
            ->when($request->from_date, fn($q) =>
                $q->where('inspection_date', '>=', $request->from_date))
            ->when($request->to_date, fn($q) =>
                $q->where('inspection_date', '<=', $request->to_date))
            ->when($request->status, fn($q) =>
                $q->where('overall_status', $request->status))
            ->latest('inspection_date')
            ->paginate(20)
            ->withQueryString();

        return view('operations.inspections.index', compact('inspections'));
    }

    public function create()
    {
        [$employees, $customerOrders, $inspectionTypes] = $this->formData();

        return view('operations.inspections.create', compact('employees', 'customerOrders', 'inspectionTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'inspection_type_id'   => ['required', 'exists:inspection_types,id'],
            'inspection_date'      => ['required', 'date'],
            'overall_status'       => ['required', 'in:Pass,Fail,Pending'],
            'customer_order_ids'   => ['array'],
            'customer_order_ids.*' => ['exists:customer_orders,id'],
            'inspector_ids'        => ['array'],
            'remarks'              => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($request) {
            $inspection = Inspection::create([
                'report_number'      => $this->generateReportNumber(),
                'inspection_type_id' => $request->inspection_type_id,
                'inspection_date'    => $request->inspection_date,
                'overall_status'     => $request->overall_status ?? 'Pending',
                'remarks'            => $request->remarks,
            ]);

            $inspection->customerOrders()->sync($request->input('customer_order_ids', []));
            $inspection->inspectors()->sync($request->input('inspector_ids', []));

            return redirect()->route('inspections.edit', $inspection)
                ->with('success', "Inspection {$inspection->report_number} created. Add runs below.");
        });
    }

    public function show(Inspection $inspection)
    {
        $inspection->load([
            'inspectionType',
            'customerOrders.customer',
            'inspectors',
            'runs.sample.customer',
            'runs.sample.category',
            'runs.runSections.section',
            'runs.movements',
        ]);

        return view('operations.inspections.show', compact('inspection'));
    }

    public function edit(Inspection $inspection)
    {
        $inspection->load([
            'inspectionType',
            'customerOrders.customer',
            'inspectors',
            'runs.sample.customer',
            'runs.runSections',
        ]);

        [$employees, $customerOrders, $inspectionTypes] = $this->formData();

        return view('operations.inspections.edit', compact(
            'inspection', 'employees', 'customerOrders', 'inspectionTypes'
        ));
    }

    public function update(Request $request, Inspection $inspection)
    {
        $request->validate([
            'inspection_type_id'   => ['required', 'exists:inspection_types,id'],
            'inspection_date'      => ['required', 'date'],
            'overall_status'       => ['required', 'in:Pass,Fail,Pending'],
            'customer_order_ids'   => ['array'],
            'customer_order_ids.*' => ['exists:customer_orders,id'],
            'inspector_ids'        => ['array'],
            'remarks'              => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($request, $inspection) {
            $inspection->update([
                'inspection_type_id' => $request->inspection_type_id,
                'inspection_date'    => $request->inspection_date,
                'overall_status'     => $request->overall_status,
                'remarks'            => $request->remarks,
            ]);

            $inspection->customerOrders()->sync($request->input('customer_order_ids', []));
            $inspection->inspectors()->sync($request->input('inspector_ids', []));

            return redirect()->route('inspections.edit', $inspection)
                ->with('success', 'Inspection details updated.');
        });
    }

    public function destroy(Inspection $inspection)
    {
        $inspection->delete();

        return redirect()->route('inspections.index')
            ->with('success', 'Inspection deleted.');
    }

    private function formData(): array
    {
        $employees = Employee::where('status', true)->orderBy('employee_name')->get();

        $customerOrders = CustomerOrder::with('customer')
            ->orderBy('order_code')
            ->get()
            ->map(fn($o) => [
                'id'   => $o->id,
                'text' => $o->order_code . ($o->customer ? ' — ' . $o->customer->customer_name : ''),
            ]);

        $inspectionTypes = InspectionType::where('status', true)->orderBy('name')->get();

        return [$employees, $customerOrders, $inspectionTypes];
    }

    private function generateReportNumber(): string
    {
        $year = now()->year;
        $seq  = str_pad((Inspection::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
        return "RPT-{$year}-{$seq}";
    }
}
