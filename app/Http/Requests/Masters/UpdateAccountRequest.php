<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('accounts.edit');
    }

    public function rules(): array
    {
        return [
            'account_name'    => ['required', 'string', 'max:255'],
            'account_type'    => ['required', Rule::in(['Cash', 'Bank', 'Ledger'])],
            'bank_id'         => ['nullable', 'exists:banks,id'],
            'currency'        => ['required', 'string', 'max:10'],
            'opening_balance' => ['nullable', 'numeric'],
            'status'          => ['boolean'],
        ];
    }
}
