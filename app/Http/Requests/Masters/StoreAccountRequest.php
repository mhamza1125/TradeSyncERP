<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('accounts.create');
    }

    public function rules(): array
    {
        return [
            'account_name'    => ['required', 'string', 'max:255'],
            'account_type'    => ['required', 'in:Cash,Bank'],
            'bank_id'         => ['nullable', 'exists:banks,id', 'required_if:account_type,Bank'],
            'account_number'  => ['nullable', 'string', 'max:100'],
            'currency'        => ['required', 'string', 'max:10'],
            'opening_balance' => ['nullable', 'numeric'],
            'status'          => ['boolean'],
        ];
    }
}
