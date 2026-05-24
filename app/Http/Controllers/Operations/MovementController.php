<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreMovementRequest;
use App\Http\Requests\Operations\UpdateMovementRequest;
use App\Models\Employee;
use App\Models\InspectionRun;
use App\Models\Movement;
use App\Models\Sample;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sample-movements.index')->only(['index', 'show']);
        $this->middleware('permission:sample-movements.create')->only(['create', 'store']);
        $this->middleware('permission:sample-movements.edit')->only(['edit', 'update']);
        $this->middleware('permission:sample-movements.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $movements = Movement::with(['items.sample.customer', 'employees', 'inspectionRun.inspection'])
            ->when($request->search, fn($q) =>
                $q->whereHas('items.sample', fn($sq) =>
                    $sq->where('sample_code', 'like', "%{$request->search}%")
                       ->orWhere('product_name', 'like', "%{$request->search}%")
                ))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('operations.movements.standalone_index', compact('movements'));
    }

    public function create(Request $request)
    {
        $samples = Sample::with(['customer', 'variations.color', 'variations.size'])
            ->orderBy('sample_code')
            ->get();

        $employees = Employee::where('status', true)->orderBy('employee_name')->get();

        $inspectionRuns = InspectionRun::with('inspection')->latest()->get();

        $preselectedRun = null;
        $preselectedEmployeeIds = [];

        if ($request->inspection_run_id) {
            $preselectedRun = InspectionRun::with([
                'inspection.samples.variations.color',
                'inspection.samples.variations.size',
                'inspection.samples.customer',
                'inspection.inspectors',
            ])->find($request->inspection_run_id);

            if ($preselectedRun) {
                $preselectedEmployeeIds = $preselectedRun->inspection->inspectors->pluck('id')->toArray();
            }
        }

        // Build samples data for JavaScript (keyed by ID)
        $samplesJson = $samples->keyBy('id')->map(fn($s) => [
            'code'       => $s->sample_code,
            'product'    => $s->product_name ?? '',
            'customer'   => $s->customer?->customer_name ?? '',
            'variations' => $s->variations->map(fn($v) => [
                'id'    => $v->id,
                'color' => optional($v->color)->name,
                'size'  => optional($v->size)->name,
                'qty'   => $v->quantity,
            ])->values(),
        ]);

        // Build inspection runs data for JS (employee auto-population)
        $inspectionRunsJson = $inspectionRuns->keyBy('id')->map(fn($run) => [
            'label'       => ($run->inspection->report_number ?? 'Inspection #' . $run->inspection_id)
                             . ' — Run #' . ($run->run_number ?? $run->id),
            'employeeIds' => $run->inspection?->inspectors?->pluck('id')->toArray() ?? [],
        ]);

        return view('operations.movements.bulk_create', compact(
            'samples', 'employees', 'inspectionRuns',
            'samplesJson', 'inspectionRunsJson',
            'preselectedRun', 'preselectedEmployeeIds'
        ));
    }

    public function store(StoreMovementRequest $request)
    {
        $data = $request->validated();

        $movement = Movement::create([
            'inspection_run_id'    => $data['inspection_run_id'] ?? null,
            'issue_date'           => $data['issue_date'],
            'expected_return_date' => $data['expected_return_date'] ?? null,
            'alert_days'           => $data['alert_days'] ?? null,
            'remarks'              => $data['remarks'] ?? null,
        ]);

        $movement->employees()->sync($data['employee_ids']);

        $itemsCreated = 0;
        foreach ($data['items'] as $itemData) {
            $qty = (int) ($itemData['quantity'] ?? 0);
            if ($qty < 1) {
                continue; // skip zero-quantity variations
            }
            $movement->items()->create([
                'sample_id'           => $itemData['sample_id'],
                'sample_variation_id' => !empty($itemData['variation_id']) ? $itemData['variation_id'] : null,
                'quantity'            => $qty,
            ]);
            $itemsCreated++;
        }

        if ($itemsCreated === 0) {
            $movement->delete();
            return back()
                ->withErrors(['items' => 'Please enter a quantity greater than zero for at least one variation.'])
                ->withInput();
        }

        return redirect()->route('movements.index')
            ->with('success', "Movement event recorded ({$itemsCreated} variation line(s)).");
    }

    public function show(Movement $movement)
    {
        $movement->load([
            'items.sample.customer',
            'items.variation.color',
            'items.variation.size',
            'employees',
            'inspectionRun.inspection',
        ]);

        return view('operations.movements.show', compact('movement'));
    }

    public function edit(Movement $movement)
    {
        $movement->load([
            'items.sample',
            'items.variation.color',
            'items.variation.size',
            'employees',
        ]);

        $employees = Employee::where('status', true)->orderBy('employee_name')->get();

        return view('operations.movements.edit', compact('movement', 'employees'));
    }

    public function update(UpdateMovementRequest $request, Movement $movement)
    {
        $data = $request->validated();

        $movement->update([
            'actual_return_date' => $data['actual_return_date'] ?? $movement->actual_return_date,
            'status'             => $data['status'],
            'remarks'            => $data['remarks'] ?? $movement->remarks,
        ]);

        if (!empty($data['employee_ids'])) {
            $movement->employees()->sync($data['employee_ids']);
        }

        if (!empty($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $movement->items()
                    ->where('id', $itemData['id'])
                    ->update([
                        'actual_return_date' => $itemData['actual_return_date'] ?? null,
                        'status'             => $itemData['status'] ?: null,
                        'remarks'            => $itemData['remarks'] ?? null,
                    ]);
            }
        }

        return redirect()->route('movements.show', $movement)
            ->with('success', 'Movement updated successfully.');
    }

    public function destroy(Movement $movement)
    {
        $movement->delete();

        return redirect()->route('movements.index')
            ->with('success', 'Movement deleted.');
    }
}
