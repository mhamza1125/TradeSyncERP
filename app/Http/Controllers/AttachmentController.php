<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
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
    /**
     * Route type => [parent model, permission needed to add/remove its files].
     * Only these parents can be reached through this generic controller; run-section
     * evidence photos are managed by InspectionRunController with its own checks.
     */
    private const TYPES = [
        'customers' => [Customer::class, 'customers.edit'],
        'suppliers' => [Supplier::class, 'suppliers.edit'],
        'employees' => [Employee::class, 'employees.edit'],
        'transactions' => [Transaction::class, 'accounts.edit'],
        'samples' => [Sample::class, 'samples.edit'],
        'customer-invoices' => [CustomerInvoice::class, 'customer-invoices.edit'],
        'customer-orders' => [CustomerOrder::class, 'customer-orders.edit'],
        'movements' => [Movement::class, 'sample-movements.edit'],
        'purchase-orders' => [PurchaseOrder::class, 'purchase-orders.edit'],
    ];

    public function __construct()
    {
        // Runs before the FormRequest, so an unauthorised user gets 403, not validation errors.
        $this->middleware(function (Request $request, $next) {
            if ($attachment = $request->route('attachment')) {
                // The attachment's own parent decides the permission; anything that is not a
                // generic parent type (e.g. inspection evidence) cannot be deleted here.
                $parent = collect(self::TYPES)->first(fn ($t) => $t[0] === $attachment->attachable_type);
                abort_unless($parent && $request->user()->can($parent[1]), 403);
            } else {
                $type = $request->route('type');
                abort_unless(array_key_exists($type, self::TYPES), 404);
                abort_unless($request->user()->can(self::TYPES[$type][1]), 403);
            }

            return $next($request);
        });
    }

    public function store(StoreAttachmentRequest $request, string $type, int $id)
    {
        $entity = self::TYPES[$type][0]::findOrFail($id);

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
        $attachment->deleteFiles();
        $attachment->delete();

        return back()->with('success', 'Attachment removed.');
    }
}
