<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vendor-bills.create');
    }

    public function rules(): array
    {
        return [
            'vendor_id'            => ['required', 'exists:vendors,id'],
            'bill_date'            => ['required', 'date'],
            'due_date'             => ['nullable', 'date', 'after_or_equal:bill_date'],
            'remarks'              => ['nullable', 'string'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.description'  => ['required', 'string'],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.001'],
            'items.*.unit'         => ['nullable', 'string', 'max:50'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'inspection_ids'       => ['nullable', 'array'],
            'inspection_ids.*'     => ['exists:inspections,id'],
        ];
    }
}
