<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreSampleMovementRequest;
use App\Http\Requests\Operations\UpdateSampleMovementRequest;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Sample;
use App\Models\SampleMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SampleMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sample-movements.index')->only(['index', 'show']);
        $this->middleware('permission:sample-movements.create')->only(['create', 'store']);
        $this->middleware('permission:sample-movements.edit')->only(['edit', 'update']);
        $this->middleware('permission:sample-movements.delete')->only('destroy');
    }

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

    // Shallow routes: no {sample} in URL — load sample from relationship
    public function show(SampleMovement $movement)
    {
        $movement->load('sample.customer');
        $sample = $movement->sample;
        return view('operations.movements.show', compact('sample', 'movement'));
    }

    public function edit(SampleMovement $movement)
    {
        $sample = $movement->load('sample.customer')->sample;
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

        return redirect()->route('samples.movements.index', $sample)
            ->with('success', 'Movement updated successfully.');
    }

    public function destroy(SampleMovement $movement)
    {
        $sample = $movement->sample;
        $movement->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('samples.movements.index', $sample)
            ->with('success', 'Movement deleted.');
    }
}
