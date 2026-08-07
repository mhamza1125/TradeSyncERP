<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerOrder;
use App\Models\Employee;
use App\Models\Movement;
use App\Models\PurchaseOrder;
use App\Models\Sample;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /** Supported polymorphic types and their model classes */
    private array $morphMap = [
        'customers' => Customer::class,
        'suppliers' => Supplier::class,
        'employees' => Employee::class,
        'transactions' => Transaction::class,
        'samples' => Sample::class,
        'customer-invoices' => CustomerInvoice::class,
        'customer-orders' => CustomerOrder::class,
        'movements' => Movement::class,
        'purchase-orders' => PurchaseOrder::class,
    ];

    public function store(Request $request, string $type, int $id)
    {
        abort_unless(array_key_exists($type, $this->morphMap), 404);

        $modelClass = $this->morphMap[$type];
        $entity = $modelClass::findOrFail($id);

        $request->validate([
            'attachments' => ['required', 'array', 'min:1'],
            'attachments.*' => ['required', 'file', 'max:20480'],
            'attachment_titles' => ['required', 'array'],
            'attachment_titles.*' => ['required', 'string', 'max:255'],
        ]);

        foreach ($request->file('attachments') as $index => $file) {
            $path = $file->store("{$type}/{$id}/attachments", 'public');
            $entity->attachments()->create([
                'title' => $request->input("attachment_titles.{$index}"),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'attachment_type' => 'document',
                'uploaded_by' => auth()->id(),
            ]);
        }

        return back()->with('success', count($request->file('attachments')).' file(s) uploaded successfully.');
    }

    public function destroy(Attachment $attachment)
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }
}
