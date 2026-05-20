<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transfers.create');
    }

    public function rules(): array
    {
        return [
            'from_account_id'  => ['required', 'exists:accounts,id'],
            'to_account_id'    => [
                'required',
                'exists:accounts,id',
                Rule::notIn([$this->from_account_id]),
            ],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'remarks'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_account_id.not_in' => 'Source and destination accounts must be different.',
        ];
    }
}
