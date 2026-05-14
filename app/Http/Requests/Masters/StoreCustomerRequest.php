<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customers.create');
    }

    public function rules(): array
    {
        return [
            'customer_name'            => ['required', 'string', 'max:255'],
            'contact_person'           => ['required', 'string', 'max:255'],
            'phone'                    => ['required', 'string', 'max:50'],
            'email'                    => ['nullable', 'email', 'max:255'],
            'address'                  => ['nullable', 'string'],
            'currency_id'     => ['nullable', 'exists:currencies,id'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'status'                   => ['boolean'],
        ];
    }
}
