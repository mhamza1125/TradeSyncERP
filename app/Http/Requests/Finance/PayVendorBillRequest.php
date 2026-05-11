<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class PayVendorBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vendor-bills.pay');
    }

    public function rules(): array
    {
        return [
            'account_id'       => ['required', 'exists:accounts,id'],
            'payment_date'     => ['required', 'date'],
            'remarks'          => ['nullable', 'string'],
        ];
    }
}
