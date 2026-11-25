<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customer-payments.create');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_date' => ['required', 'date'],
            // Must be an existing invoice of the selected customer (finance review FN-3).
            'invoice_reference' => ['nullable', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail) {
                $exists = \App\Models\CustomerInvoice::where('invoice_number', $value)
                    ->where('customer_id', $this->input('customer_id'))
                    ->exists();
                if (! $exists) {
                    $fail('Choose an existing invoice of this customer.');
                }
            }],
            'foreign_currency' => ['required', 'string', 'max:10'],
            'invoiced_amount_fc' => ['required', 'numeric', 'min:0.01'],
            'deduction_fc' => ['nullable', 'numeric', 'min:0'],
            'received_fc' => ['required', 'numeric', 'min:0'],
            'wh_tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'exchange_rate' => ['required', 'numeric', 'min:0.000001'],
            'actual_pkr_received' => ['required', 'numeric', 'min:0'],
            'remittance_charges' => ['nullable', 'numeric', 'min:0'],
            'debit_account_id' => ['required', 'exists:accounts,id'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
