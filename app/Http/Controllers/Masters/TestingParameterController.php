<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreTestingParameterRequest;
use App\Http\Requests\Masters\UpdateTestingParameterRequest;
use App\Models\ProductCategory;
use App\Models\TestingParameter;
use Illuminate\Http\Request;

class TestingParameterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:parameters.index')->only(['index', 'show']);
        $this->middleware('permission:parameters.create')->only(['create', 'store']);
        $this->middleware('permission:parameters.edit')->only(['edit', 'update']);
        $this->middleware('permission:parameters.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $parameters = TestingParameter::with('category')
            ->when($request->search, fn ($q, $s) => $q->where('parameter_name', 'like', "%{$s}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = ProductCategory::where('status', true)->orderBy('category_name')->get();

        return view('masters.parameters.index', compact('parameters', 'categories'));
    }

    public function create()
    {
        $categories = ProductCategory::where('status', true)->orderBy('category_name')->get();
        return view('masters.parameters.create', compact('categories'));
    }

    public function store(StoreTestingParameterRequest $request)
    {
        $parameter = TestingParameter::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'parameter' => $parameter->load('category')]);
        }

        return redirect()->route('masters.parameters.index')
            ->with('success', 'Testing parameter created successfully.');
    }

    public function show(TestingParameter $parameter)
    {
        $parameter->load('category');
        return view('masters.parameters.show', compact('parameter'));
    }

    public function edit(TestingParameter $parameter)
    {
        $categories = ProductCategory::where('status', true)->orderBy('category_name')->get();
        return view('masters.parameters.edit', compact('parameter', 'categories'));
    }

    public function update(UpdateTestingParameterRequest $request, TestingParameter $parameter)
    {
        $parameter->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'parameter' => $parameter]);
        }

        return redirect()->route('masters.parameters.index')
            ->with('success', 'Testing parameter updated successfully.');
    }

    public function destroy(TestingParameter $parameter)
    {
        $parameter->update(['status' => false]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.parameters.index')
            ->with('success', 'Parameter deactivated successfully.');
    }
}
