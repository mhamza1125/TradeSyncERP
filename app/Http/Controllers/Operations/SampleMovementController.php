<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreSampleMovementRequest;
use App\Http\Requests\Operations\UpdateSampleMovementRequest;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\InspectionRun;
use App\Models\Sample;
use App\Models\SampleMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SampleMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sample-movements.index')->only(['index', 'standaloneIndex', 'show']);
        $this->middleware('permission:sample-movements.create')->only(['create', 'store', 'bulkCreate', 'bulkStore']);
        $this->middleware('permission:sample-movements.edit')->only(['edit', 'update']);
        $this->middleware('permission:sample-movements.delete')->only('destroy');
    }

    // ── Standalone (all-samples) ──────────────────────────────────────────────

    public function standaloneIndex(Request $request)
    {
        $movements = SampleMovement::with(['sample.customer', 'inspectionRun.inspection'])
            ->when($request->search, fn($q) =>
                $q->whereHas('sample', fn($sq) =>
                    $sq->where('sample_code', 'like', "%{$request->search}%")
                       ->orWhere('product_name', 'like', "%{$request->search}%")
                ))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('operations.movements.standalone_index', compact('movements'));
    }

    public function bulkCreate(Request $request)
    {
        $samples       = Sample::with('customer')->orderBy('sample_code')->get();
        $employees     = Employee::where('status', true)->orderBy('employee_name')->get();
        $suppliers     = Supplier::where('status', true)->orderBy('name')->get();
        $customers     = Customer::where('status', true)->orderBy('customer_name')->get();
        $inspectionRuns = InspectionRun::with('inspection')->latest()->get();

        // If launched from an inspection run page, pre-populate that run and its samples
        $preselectedRun = $request->inspection_run_id
            ? InspectionRun::with('inspection.samples')->find($request->inspection_run_id)
            : null;

        return view('operations.movements.bulk_create', compact(
            'samples', 'employees', 'suppliers', 'customers', 'inspectionRuns', 'preselectedRun'
        ));
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'sample_ids'           => ['required', 'array', 'min:1'],
            'sample_ids.*'         => ['exists:samples,id'],
            'inspection_run_id'    => ['nullable', 'exists:inspection_runs,id'],
            'moved_by_type'        => ['required', 'in:Employee,User'],
            'moved_by_id'          => ['required', 'integer'],
            'assigned_to_type'     => ['required', Rule::in(['Employee', 'Supplier', 'Storage', 'Customer'])],
            'assigned_to_id'       => ['required', 'integer'],
            'issue_date'           => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'alert_days'           => ['nullable', 'integer', 'min:1'],
            'remarks'              => ['nullable', 'string'],
        ]);

        $shared = collect($validated)->except('sample_ids')->toArray();

        foreach ($validated['sample_ids'] as $sampleId) {
            SampleMovement::create(array_merge($shared, ['sample_id' => $sampleId]));
        }

        $count = count($validated['sample_ids']);

        return redirect()->route('movements.index')
            ->with('success', "Movement recorded for {$count} sample(s).");
    }

    // ── Sample-nested (legacy, kept for backward compat) ─────────────────────

    public function index(Request $request, Sample $sample)
    {
        $movements = $sample->movements()->with('sample')->latest()->paginate(20);
        return view('operations.movements.index', compact('sample', 'movements'));
    }

    public function create(Sample $sample)
    {
        $employees = Employee::where('status', true)->orderBy('employee_name')->get();
        $suppliers = Supplier::where('status', true)->orderBy('name')->get();
        $customers = Customer::where('status', true)->orderBy('customer_name')->get();
        return view('operations.movements.create', compact('sample', 'employees', 'suppliers', 'customers'));
    }

    public function store(StoreSampleMovementRequest $request, Sample $sample)
    {
        $data = $request->validated();
        $data['sample_id'] = $sample->id;

        $movement = SampleMovement::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'movement' => $movement]);
        }

        return redirect()->route('samples.movements.index', $sample)
            ->with('success', 'Movement recorded successfully.');
    }

    // ── Shallow routes: show / edit / update / destroy ────────────────────────

    public function show(SampleMovement $movement)
    {
        $movement->load('sample.customer', 'inspectionRun.inspection');
        $sample = $movement->sample;
        return view('operations.movements.show', compact('sample', 'movement'));
    }

    public function edit(SampleMovement $movement)
    {
        $sample    = $movement->load('sample.customer')->sample;
        $employees = Employee::where('status', true)->orderBy('employee_name')->get();
        $suppliers = Supplier::where('status', true)->orderBy('name')->get();
        $customers = Customer::where('status', true)->orderBy('customer_name')->get();
        return view('operations.movements.edit', compact('sample', 'movement', 'employees', 'suppliers', 'customers'));
    }

    public function update(UpdateSampleMovementRequest $request, SampleMovement $movement)
    {
        $sample = $movement->sample;
        $movement->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'movement' => $movement]);
        }

        return redirect()->route('movements.index')
            ->with('success', 'Movement updated successfully.');
    }

    public function destroy(SampleMovement $movement)
    {
        $movement->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('movements.index')
            ->with('success', 'Movement deleted.');
    }
}
