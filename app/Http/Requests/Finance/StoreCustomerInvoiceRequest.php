<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customer-invoices.create');
    }

    public function rules(): array
    {
        return [
            'customer_id'                  => ['required', 'exists:customers,id'],
            'invoice_date'                 => ['required', 'date'],
            'due_date'                     => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'status'                       => ['required', Rule::in(['Draft', 'Sent', 'Partial', 'Paid', 'Overdue', 'Cancelled'])],
            'tax_amount'                   => ['nullable', 'numeric', 'min:0'],
            'discount_amount'              => ['nullable', 'numeric', 'min:0'],
            'remarks'                      => ['nullable', 'string'],
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.supplier_id'          => ['nullable', 'exists:suppliers,id'],
            'items.*.inspection_type_id'   => ['nullable', 'exists:inspection_types,id'],
            'items.*.po_invoice_no'        => ['nullable', 'string', 'max:255'],
            'items.*.item_date'            => ['nullable', 'date'],
            'items.*.amount'               => ['required', 'numeric', 'min:0'],
        ];
    }
}
