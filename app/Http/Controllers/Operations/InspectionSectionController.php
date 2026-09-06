<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreInspectionSectionRequest;
use App\Http\Requests\Operations\UpdateInspectionSectionRequest;
use App\Models\InspectionSection;
use Illuminate\Http\Request;

class InspectionSectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inspection-sections.index')->only(['index']);
        $this->middleware('permission:inspection-sections.create')->only(['create', 'store']);
        $this->middleware('permission:inspection-sections.edit')->only(['edit', 'update']);
        $this->middleware('permission:inspection-sections.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $sections = InspectionSection::query()
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%"))
            ->when($request->type, fn($q) =>
                $q->where('section_type', $request->type))
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('is_active', $request->status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $sectionTypes = InspectionSection::distinct()->pluck('section_type')->sort()->values();

        return view('operations.inspection-sections.index', compact('sections', 'sectionTypes'));
    }

    public function create()
    {
        return view('operations.inspection-sections.create');
    }

    public function store(StoreInspectionSectionRequest $request)
    {
        $data = $request->validated();

        $data['is_active']    = $request->boolean('is_active', true);
        $data['sort_order']   = $data['sort_order'] ?? 0;
        $data['skip_policy']  = $data['skip_policy'] ?? 'mandatory';
        $data['default_data'] = $data['default_data'] ? json_decode($data['default_data'], true) : null;

        InspectionSection::create($data);

        return redirect()->route('inspection-sections.index')
            ->with('success', 'Inspection section created.');
    }

    public function edit(InspectionSection $inspectionSection)
    {
        return view('operations.inspection-sections.edit', compact('inspectionSection'));
    }

    public function update(UpdateInspectionSectionRequest $request, InspectionSection $inspectionSection)
    {
        $data = $request->validated();

        $data['is_active']    = $request->boolean('is_active', true);
        $data['sort_order']   = $data['sort_order'] ?? 0;
        $data['skip_policy']  = $data['skip_policy'] ?? 'mandatory';
        $data['default_data'] = $data['default_data'] ? json_decode($data['default_data'], true) : null;

        $inspectionSection->update($data);

        return redirect()->route('inspection-sections.index')
            ->with('success', 'Inspection section updated.');
    }

    public function destroy(InspectionSection $inspectionSection)
    {
        $inUse = $inspectionSection->runSections()->exists()
            || $inspectionSection->typeDefaults()->exists();

        if ($inUse) {
            return back()->with('error', 'Cannot delete a section that is assigned to inspection types or active runs.');
        }

        $inspectionSection->delete();

        return redirect()->route('inspection-sections.index')
            ->with('success', 'Inspection section deleted.');
    }
}
