<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customer-payments.edit');
    }

    public function rules(): array
    {
        return [
            'customer_id'         => ['required', 'exists:customers,id'],
            'payment_date'        => ['required', 'date'],
            'invoice_reference'   => ['nullable', 'string', 'max:255'],
            'foreign_currency'    => ['required', 'string', 'max:10'],
            'invoiced_amount_fc'  => ['required', 'numeric', 'min:0.01'],
            'received_fc'         => ['required', 'numeric', 'min:0'],
            'exchange_rate'       => ['required', 'numeric', 'min:0.000001'],
            'actual_pkr_received' => ['required', 'numeric', 'min:0'],
            'debit_account_id'    => ['required', 'exists:accounts,id'],
            'remarks'             => ['nullable', 'string'],
        ];
    }
}
