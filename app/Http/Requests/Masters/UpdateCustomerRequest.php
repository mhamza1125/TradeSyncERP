<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customers.edit');
    }

    public function rules(): array
    {
        return [
            'customer_name'            => ['required', 'string', 'max:255'],
            'contact_person'           => ['required', 'string', 'max:255'],
            'phone'                    => ['required', 'string', 'max:50'],
            'email'                    => ['nullable', 'email', 'max:255'],
            'address'                  => ['nullable', 'string'],
            'currency_id'              => ['nullable', 'exists:currencies,id'],
            'default_currency'         => ['required', 'string', 'max:10'],
            'opening_balance'          => ['nullable', 'numeric', 'min:0'],
            'opening_balance_currency' => ['required', 'string', 'max:10'],
            'status'                   => ['boolean'],
        ];
    }
}
