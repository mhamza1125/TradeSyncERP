<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('expenses.create');
    }

    public function rules(): array
    {
        return [
            'expense_head_id' => ['required', 'exists:expense_heads,id'],
            'account_id'      => ['required', 'exists:accounts,id'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'expense_date'    => ['required', 'date'],
            'description'     => ['nullable', 'string'],
            'attachment'      => ['nullable', 'file', 'max:5120'],
        ];
    }
}
