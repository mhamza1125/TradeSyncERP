<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
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
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $sectionTypes = InspectionSection::distinct()->pluck('section_type')->sort()->values();

        return view('operations.inspection-sections.index', compact('sections', 'sectionTypes'));
    }

    public function create()
    {
        return view('operations.inspection-sections.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'slug'         => ['required', 'string', 'max:100', 'unique:inspection_sections,slug', 'regex:/^[a-z0-9_]+$/'],
            'description'  => ['nullable', 'string', 'max:500'],
            'section_type' => ['required', 'in:images,workmanship,aql,checklist,container,verification,review,task_list,quantity_sampling,cartons,cover_photo,files_review,defects,finish'],
            'icon'         => ['nullable', 'string', 'max:100'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'is_active'    => ['nullable', 'boolean'],
            'default_data' => ['nullable', 'json'],
        ]);

        $data['is_active']    = $request->boolean('is_active', true);
        $data['sort_order']   = $data['sort_order'] ?? 0;
        $data['default_data'] = $data['default_data'] ? json_decode($data['default_data'], true) : null;

        InspectionSection::create($data);

        return redirect()->route('inspection-sections.index')
            ->with('success', 'Inspection section created.');
    }

    public function edit(InspectionSection $inspectionSection)
    {
        return view('operations.inspection-sections.edit', compact('inspectionSection'));
    }

    public function update(Request $request, InspectionSection $inspectionSection)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'slug'         => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/',
                'unique:inspection_sections,slug,' . $inspectionSection->id],
            'description'  => ['nullable', 'string', 'max:500'],
            'section_type' => ['required', 'in:images,workmanship,aql,checklist,container,verification,review,task_list,quantity_sampling,cartons,cover_photo,files_review,defects,finish'],
            'icon'         => ['nullable', 'string', 'max:100'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'is_active'    => ['nullable', 'boolean'],
            'default_data' => ['nullable', 'json'],
        ]);

        $data['is_active']    = $request->boolean('is_active', true);
        $data['sort_order']   = $data['sort_order'] ?? 0;
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
