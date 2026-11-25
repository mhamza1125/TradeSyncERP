<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Defect;
use App\Models\InspectionRunDefect;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DefectController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:defects.index')->only(['index', 'show', 'exportPdf']);
        $this->middleware('permission:defects.create')->only(['create', 'store']);
        $this->middleware('permission:defects.edit')->only(['edit', 'update']);
        $this->middleware('permission:defects.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $defects = Defect::query()
            ->when($request->search, fn ($q, $s) => $q->where('defect_name', 'like', "%{$s}%"))
            ->when($request->severity, fn ($q, $s) => $q->where('severity', $s))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->orderBy('defect_name')
            ->paginate(25)
            ->withQueryString();

        return view('masters.defects.index', compact('defects'));
    }

    public function create()
    {
        return view('masters.defects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'defect_name' => ['required', 'string', 'max:255', 'unique:defects,defect_name'],
            'severity' => ['required', 'in:critical,major,minor,functional'],
            'corrective_action' => ['nullable', 'string', 'max:2000'],
            'status' => ['boolean'],
        ]);

        $validated['status'] = $request->boolean('status', true);

        Defect::create($validated);

        return redirect()->route('masters.defects.index')
            ->with('success', 'Defect created successfully.');
    }

    public function edit(Defect $defect)
    {
        return view('masters.defects.edit', compact('defect'));
    }

    public function update(Request $request, Defect $defect)
    {
        $validated = $request->validate([
            'defect_name' => ['required', 'string', 'max:255', 'unique:defects,defect_name,'.$defect->id],
            'severity' => ['required', 'in:critical,major,minor,functional'],
            'corrective_action' => ['nullable', 'string', 'max:2000'],
            'status' => ['boolean'],
        ]);

        $validated['status'] = $request->boolean('status', true);

        $defect->update($validated);

        return redirect()->route('masters.defects.index')
            ->with('success', 'Defect updated successfully.');
    }

    public function destroy(Defect $defect)
    {
        // A finished run's report names this defect; deleting it would blank that name (RR-06, D3/Q30).
        $onFinishedRun = InspectionRunDefect::where('defect_id', $defect->id)
            ->whereHas('runSection.run', fn ($q) => $q->whereNotNull('completed_at'))
            ->exists();

        if ($onFinishedRun) {
            $message = 'Cannot delete a defect that is recorded on finished inspection runs. Deactivate it instead.';

            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => $message, 'errors' => []], 422)
                : back()->with('error', $message);
        }

        $defect->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.defects.index')
            ->with('success', 'Defect deleted.');
    }

    public function exportPdf(Request $request)
    {
        $defects = Defect::query()
            ->when($request->search, fn ($q, $s) => $q->where('defect_name', 'like', "%{$s}%"))
            ->when($request->severity, fn ($q, $s) => $q->where('severity', $s))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->orderBy('defect_name')
            ->get();

        $pdf = Pdf::loadView('exports.defects-list-pdf', compact('defects'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream('Defects-'.now()->format('Y-m-d').'.pdf');
    }
}
