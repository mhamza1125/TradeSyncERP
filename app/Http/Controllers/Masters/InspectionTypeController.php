<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreInspectionTypeRequest;
use App\Http\Requests\Masters\UpdateInspectionTypeRequest;
use App\Models\InspectionSection;
use App\Models\InspectionType;
use App\Models\InspectionTypeSectionDefault;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inspection-types.index')->only(['index', 'show']);
        $this->middleware('permission:inspection-types.create')->only(['create', 'store']);
        $this->middleware('permission:inspection-types.edit')->only(['edit', 'update', 'sections', 'syncSections']);
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
        $inspectionType->load(['inspections', 'sectionDefaults.section', 'sectionDefaults.category']);
        return view('masters.inspection-types.show', compact('inspectionType'));
    }

    public function sections(InspectionType $inspectionType)
    {
        $inspectionType->load(['sectionDefaults.section', 'sectionDefaults.category']);

        $sections   = InspectionSection::where('is_active', true)->orderBy('name')->get();
        $categories = ProductCategory::orderBy('category_name')->get();

        return view('masters.inspection-types.sections', compact('inspectionType', 'sections', 'categories'));
    }

    public function syncSections(Request $request, InspectionType $inspectionType)
    {
        $request->validate([
            'rows'                  => ['nullable', 'array'],
            'rows.*.section_id'     => ['required', 'exists:inspection_sections,id'],
            'rows.*.category_id'    => ['nullable', 'exists:product_categories,id'],
            'rows.*.sort_order'     => ['nullable', 'integer', 'min:0'],
            'rows.*.is_required'    => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $inspectionType) {
            $inspectionType->sectionDefaults()->delete();

            foreach ($request->input('rows', []) as $i => $row) {
                InspectionTypeSectionDefault::create([
                    'inspection_type_id'    => $inspectionType->id,
                    'inspection_section_id' => $row['section_id'],
                    'category_id'           => ($row['category_id'] ?? null) ?: null,
                    'sort_order'            => isset($row['sort_order']) && $row['sort_order'] !== '' ? (int) $row['sort_order'] : ($i + 1) * 10,
                    'is_required'           => ! empty($row['is_required']),
                ]);
            }
        });

        return redirect()->route('masters.inspection-types.sections', $inspectionType)
            ->with('success', 'Section assignments saved successfully.');
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
