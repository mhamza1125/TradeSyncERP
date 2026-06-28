<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\SampleColor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:colors.index')->only(['index', 'show', 'exportPdf']);
        $this->middleware('permission:colors.create')->only(['create', 'store']);
        $this->middleware('permission:colors.edit')->only(['edit', 'update']);
        $this->middleware('permission:colors.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $colors = SampleColor::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->withCount('variations')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('masters.colors.index', compact('colors'));
    }

    public function create()
    {
        return view('masters.colors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:sample_colors,name'],
        ]);

        SampleColor::create(['name' => $request->name]);

        return redirect()->route('masters.colors.index')
            ->with('success', 'Color created successfully.');
    }

    public function show(SampleColor $color)
    {
        $color->loadCount('variations');
        return view('masters.colors.show', compact('color'));
    }

    public function edit(SampleColor $color)
    {
        return view('masters.colors.edit', compact('color'));
    }

    public function update(Request $request, SampleColor $color)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:sample_colors,name,' . $color->id],
        ]);

        $color->update(['name' => $request->name]);

        return redirect()->route('masters.colors.index')
            ->with('success', 'Color updated successfully.');
    }

    public function destroy(SampleColor $color)
    {
        if ($color->variations()->exists()) {
            return redirect()->route('masters.colors.index')
                ->with('error', 'Cannot delete a color that is used in sample variations.');
        }

        $color->delete();

        return redirect()->route('masters.colors.index')
            ->with('success', 'Color deleted successfully.');
    }

    public function exportPdf(Request $request)
    {
        $colors = SampleColor::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('exports.colors-list-pdf', compact('colors'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('Colors-' . now()->format('Y-m-d') . '.pdf');
    }
}
