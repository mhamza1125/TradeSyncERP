<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('expense-heads.create');
    }

    public function rules(): array
    {
        return [
            'expense_name' => ['required', 'string', 'max:255', 'unique:expense_heads,expense_name'],
            'status'       => ['boolean'],
        ];
    }
}
