<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreProductCategoryRequest;
use App\Http\Requests\Masters\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:categories.index')->only(['index', 'show']);
        $this->middleware('permission:categories.create')->only(['create', 'store']);
        $this->middleware('permission:categories.edit')->only(['edit', 'update']);
        $this->middleware('permission:categories.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $categories = ProductCategory::query()
            ->when($request->search, fn ($q, $s) => $q->where('category_name', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('masters.categories.create');
    }

    public function store(StoreProductCategoryRequest $request)
    {
        $category = ProductCategory::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category]);
        }

        return redirect()->route('masters.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(ProductCategory $category)
    {
        $category->load('testingParameters');
        return view('masters.categories.show', compact('category'));
    }

    public function edit(ProductCategory $category)
    {
        return view('masters.categories.edit', compact('category'));
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $category)
    {
        $category->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category]);
        }

        return redirect()->route('masters.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $category)
    {
        $category->update(['status' => false]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.categories.index')
            ->with('success', 'Category deactivated successfully.');
    }
}
