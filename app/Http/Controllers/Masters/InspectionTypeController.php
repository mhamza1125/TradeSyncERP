<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreInspectionTypeRequest;
use App\Http\Requests\Masters\UpdateInspectionTypeRequest;
use App\Models\InspectionType;
use Illuminate\Http\Request;

class InspectionTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inspection-types.index')->only(['index', 'show']);
        $this->middleware('permission:inspection-types.create')->only(['create', 'store']);
        $this->middleware('permission:inspection-types.edit')->only(['edit', 'update']);
        $this->middleware('permission:inspection-types.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $inspectionTypes = InspectionType::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->withCount('runs')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.inspection-types.index', compact('inspectionTypes'));
    }

    public function create()
    {
        return view('masters.inspection-types.create');
    }

    public function store(StoreInspectionTypeRequest $request)
    {
        $inspectionType = InspectionType::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'inspectionType' => $inspectionType]);
        }

        return redirect()->route('masters.inspection-types.index')
            ->with('success', 'Inspection type created successfully.');
    }

    public function show(InspectionType $inspectionType)
    {
        $inspectionType->load('runs');
        return view('masters.inspection-types.show', compact('inspectionType'));
    }

    public function edit(InspectionType $inspectionType)
    {
        return view('masters.inspection-types.edit', compact('inspectionType'));
    }

    public function update(UpdateInspectionTypeRequest $request, InspectionType $inspectionType)
    {
        $inspectionType->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'inspectionType' => $inspectionType]);
        }

        return redirect()->route('masters.inspection-types.index')
            ->with('success', 'Inspection type updated successfully.');
    }

    public function destroy(InspectionType $inspectionType)
    {
        $inspectionType->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.inspection-types.index')
            ->with('success', 'Inspection type removed successfully.');
    }
}
