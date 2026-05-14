<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customer-invoices.edit');
    }

    public function rules(): array
    {
        return [
            'customer_id'        => ['required', 'exists:customers,id'],
            'invoice_date'       => ['required', 'date'],
            'due_date'           => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'status'             => ['required', Rule::in(['Draft', 'Sent', 'Partial', 'Paid', 'Overdue', 'Cancelled'])],
            'tax_amount'         => ['nullable', 'numeric', 'min:0'],
            'discount_amount'    => ['nullable', 'numeric', 'min:0'],
            'foreign_currency_id' => ['nullable', 'exists:currencies,id'],
            'exchange_rate'      => ['nullable', 'numeric', 'min:0'],
            'foreign_amount'     => ['nullable', 'numeric', 'min:0'],
            'remarks'            => ['nullable', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
        ];
    }
}
