<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\SampleSize;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sizes.index')->only(['index', 'show']);
        $this->middleware('permission:sizes.create')->only(['create', 'store']);
        $this->middleware('permission:sizes.edit')->only(['edit', 'update']);
        $this->middleware('permission:sizes.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $sizes = SampleSize::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->withCount('variations')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('masters.sizes.index', compact('sizes'));
    }

    public function create()
    {
        return view('masters.sizes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:sample_sizes,name'],
        ]);

        SampleSize::create(['name' => $request->name]);

        return redirect()->route('masters.sizes.index')
            ->with('success', 'Size created successfully.');
    }

    public function show(SampleSize $size)
    {
        $size->loadCount('variations');
        return view('masters.sizes.show', compact('size'));
    }

    public function edit(SampleSize $size)
    {
        return view('masters.sizes.edit', compact('size'));
    }

    public function update(Request $request, SampleSize $size)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:sample_sizes,name,' . $size->id],
        ]);

        $size->update(['name' => $request->name]);

        return redirect()->route('masters.sizes.index')
            ->with('success', 'Size updated successfully.');
    }

    public function destroy(SampleSize $size)
    {
        if ($size->variations()->exists()) {
            return redirect()->route('masters.sizes.index')
                ->with('error', 'Cannot delete a size that is used in sample variations.');
        }

        $size->delete();

        return redirect()->route('masters.sizes.index')
            ->with('success', 'Size deleted successfully.');
    }
}
