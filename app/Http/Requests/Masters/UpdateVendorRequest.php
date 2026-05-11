<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vendors.edit');
    }

    public function rules(): array
    {
        return [
            'vendor_name'     => ['required', 'string', 'max:255'],
            'company_name'    => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:50'],
            'email'           => ['nullable', 'email', 'max:255'],
            'address'         => ['nullable', 'string'],
            'payment_terms'   => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'status'          => ['boolean'],
        ];
    }
}
