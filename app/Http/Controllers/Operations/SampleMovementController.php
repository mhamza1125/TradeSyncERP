<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreSampleMovementRequest;
use App\Http\Requests\Operations\UpdateSampleMovementRequest;
use App\Models\Employee;
use App\Models\Sample;
use App\Models\SampleMovement;
use App\Models\Vendor;
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
        $vendors   = Vendor::where('status', true)->orderBy('vendor_name')->get();
        return view('operations.movements.create', compact('sample', 'employees', 'vendors'));
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

    public function show(Sample $sample, SampleMovement $movement)
    {
        return view('operations.movements.show', compact('sample', 'movement'));
    }

    public function edit(Sample $sample, SampleMovement $movement)
    {
        return view('operations.movements.edit', compact('sample', 'movement'));
    }

    public function update(UpdateSampleMovementRequest $request, Sample $sample, SampleMovement $movement)
    {
        $movement->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'movement' => $movement]);
        }

        return redirect()->route('samples.movements.index', $sample)
            ->with('success', 'Movement updated successfully.');
    }

    public function destroy(Sample $sample, SampleMovement $movement)
    {
        $movement->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('samples.movements.index', $sample)
            ->with('success', 'Movement deleted.');
    }
}
