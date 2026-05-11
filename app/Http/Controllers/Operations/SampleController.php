<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreSampleRequest;
use App\Http\Requests\Operations\UpdateSampleRequest;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\ProductCategory;
use App\Models\Sample;
use App\Models\TestingParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SampleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:samples.index')->only(['index', 'show']);
        $this->middleware('permission:samples.create')->only(['create', 'store']);
        $this->middleware('permission:samples.edit')->only(['edit', 'update']);
        $this->middleware('permission:samples.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $samples = Sample::with(['customer', 'brand', 'category'])
            ->when($request->search, fn ($q, $s) => $q->where('sample_code', 'like', "%{$s}%")
                ->orWhere('product_name', 'like', "%{$s}%"))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->brand_id, fn ($q) => $q->where('brand_id', $request->brand_id))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->priority_level, fn ($q) => $q->where('priority_level', $request->priority_level))
            ->when($request->from_date, fn ($q) => $q->where('receive_date', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->where('receive_date', '<=', $request->to_date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $customers  = Customer::where('status', true)->orderBy('customer_name')->get();
        $categories = ProductCategory::where('status', true)->orderBy('category_name')->get();

        return view('operations.samples.index', compact('samples', 'customers', 'categories'));
    }

    public function create()
    {
        $customers  = Customer::where('status', true)->orderBy('customer_name')->get();
        $categories = ProductCategory::where('status', true)->orderBy('category_name')->get();
        return view('operations.samples.create', compact('customers', 'categories'));
    }

    public function store(StoreSampleRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            $data['sample_code'] = $this->generateSampleCode();

            $sample = Sample::create($data);

            if (!empty($data['parameters'])) {
                $sample->testingParameters()->createMany($data['parameters']);
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'sample' => $sample]);
            }

            return redirect()->route('samples.show', $sample)
                ->with('success', "Sample {$sample->sample_code} created successfully.");
        });
    }

    public function show(Sample $sample)
    {
        $sample->load([
            'customer', 'brand', 'category',
            'testingParameters.parameter',
            'movements',
            'inspections.results',
        ]);

        return view('operations.samples.show', compact('sample'));
    }

    public function edit(Sample $sample)
    {
        $customers  = Customer::where('status', true)->orderBy('customer_name')->get();
        $categories = ProductCategory::where('status', true)->orderBy('category_name')->get();
        $brands     = Brand::where('customer_id', $sample->customer_id)->where('status', true)->get();
        $sample->load('testingParameters.parameter');

        return view('operations.samples.edit', compact('sample', 'customers', 'categories', 'brands'));
    }

    public function update(UpdateSampleRequest $request, Sample $sample)
    {
        $sample->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'sample' => $sample]);
        }

        return redirect()->route('samples.show', $sample)
            ->with('success', 'Sample updated successfully.');
    }

    public function destroy(Sample $sample)
    {
        $sample->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('samples.index')
            ->with('success', 'Sample removed successfully.');
    }

    private function generateSampleCode(): string
    {
        $year    = now()->year;
        $lastId  = Sample::withTrashed()->max('id') ?? 0;
        $nextSeq = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
        return "SMP-{$year}-{$nextSeq}";
    }
}
