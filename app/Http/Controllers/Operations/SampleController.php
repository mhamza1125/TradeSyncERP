<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreSampleRequest;
use App\Http\Requests\Operations\UpdateSampleRequest;
use App\Models\Customer;
use App\Models\ProductCategory;
use App\Models\Sample;
use App\Models\SampleColor;
use App\Models\SampleSize;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $samples = Sample::withSum('variations', 'quantity')->with(['customer', 'category'])
            ->when($request->search, fn ($q, $s) => $q->where('sample_code', 'like', "%{$s}%")
                ->orWhere('product_name', 'like', "%{$s}%"))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
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
        $suppliers  = Supplier::where('status', true)->orderBy('name')->get();
        $colors     = SampleColor::orderBy('name')->get();
        $sizes      = SampleSize::orderBy('name')->get();

        return view('operations.samples.create', compact('customers', 'categories', 'suppliers', 'colors', 'sizes'));
    }

    public function store(StoreSampleRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['sample_code'] = $this->generateSampleCode();

            // Handle main image upload
            if ($request->hasFile('main_image_file')) {
                $data['main_image'] = $request->file('main_image_file')->store('samples/main', 'public');
            }

            $variations = $data['variations'] ?? [];
            unset($data['variations']);

            $sample = Sample::create($data);

            // Save color/size/qty variations
            foreach ($variations as $variation) {
                if (!empty($variation['quantity'])) {
                    $sample->variations()->create([
                        'color_id' => $variation['color_id'] ?? null,
                        'size_id'  => $variation['size_id'] ?? null,
                        'quantity' => $variation['quantity'],
                    ]);
                }
            }

            // Gallery images via attachments
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $path = $file->store('samples/gallery', 'public');
                    $sample->attachments()->create([
                        'title'           => $file->getClientOriginalName(),
                        'file_name'       => $file->getClientOriginalName(),
                        'file_path'       => $path,
                        'mime_type'       => $file->getMimeType(),
                        'file_size'       => $file->getSize(),
                        'attachment_type' => 'gallery',
                        'uploaded_by'     => auth()->id(),
                    ]);
                }
            }

            // Other attachments (documents)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    $path = $file->store('samples/attachments', 'public');
                    $sample->attachments()->create([
                        'title'           => $request->input("attachment_titles.{$index}", $file->getClientOriginalName()),
                        'file_name'       => $file->getClientOriginalName(),
                        'file_path'       => $path,
                        'mime_type'       => $file->getMimeType(),
                        'file_size'       => $file->getSize(),
                        'attachment_type' => 'document',
                        'uploaded_by'     => auth()->id(),
                    ]);
                }
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
            'customer',
            'category',
            'variations.color', 'variations.size',
            'movements',
            'inspections.runs',
            'attachments',
        ]);

        return view('operations.samples.show', compact('sample'));
    }

    public function edit(Sample $sample)
    {
        $customers  = Customer::where('status', true)->orderBy('customer_name')->get();
        $categories = ProductCategory::where('status', true)->orderBy('category_name')->get();
        $suppliers  = Supplier::where('status', true)->orderBy('name')->get();
        $colors     = SampleColor::orderBy('name')->get();
        $sizes      = SampleSize::orderBy('name')->get();
        $sample->load('variations.color', 'variations.size', 'testingParameters.parameter', 'attachments');

        return view('operations.samples.edit', compact('sample', 'customers', 'categories', 'suppliers', 'colors', 'sizes'));
    }

    public function update(UpdateSampleRequest $request, Sample $sample)
    {
        return DB::transaction(function () use ($request, $sample) {
            $data = $request->validated();

            // Handle main image replacement
            if ($request->hasFile('main_image_file')) {
                if ($sample->main_image) {
                    Storage::disk('public')->delete($sample->main_image);
                }
                $data['main_image'] = $request->file('main_image_file')->store('samples/main', 'public');
            }

            $variations = $data['variations'] ?? [];
            unset($data['variations']);

            $sample->update($data);

            // Replace variations
            $sample->variations()->delete();
            foreach ($variations as $variation) {
                if (!empty($variation['quantity'])) {
                    $sample->variations()->create([
                        'color_id' => $variation['color_id'] ?? null,
                        'size_id'  => $variation['size_id'] ?? null,
                        'quantity' => $variation['quantity'],
                    ]);
                }
            }

            // Append new gallery images
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $path = $file->store('samples/gallery', 'public');
                    $sample->attachments()->create([
                        'title'           => $file->getClientOriginalName(),
                        'file_name'       => $file->getClientOriginalName(),
                        'file_path'       => $path,
                        'mime_type'       => $file->getMimeType(),
                        'file_size'       => $file->getSize(),
                        'attachment_type' => 'gallery',
                        'uploaded_by'     => auth()->id(),
                    ]);
                }
            }

            // Append new documents
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    $path = $file->store('samples/attachments', 'public');
                    $sample->attachments()->create([
                        'title'           => $request->input("attachment_titles.{$index}", $file->getClientOriginalName()),
                        'file_name'       => $file->getClientOriginalName(),
                        'file_path'       => $path,
                        'mime_type'       => $file->getMimeType(),
                        'file_size'       => $file->getSize(),
                        'attachment_type' => 'document',
                        'uploaded_by'     => auth()->id(),
                    ]);
                }
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'sample' => $sample]);
            }

            return redirect()->route('samples.show', $sample)
                ->with('success', 'Sample updated successfully.');
        });
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
