<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('expense-heads.edit');
    }

    public function rules(): array
    {
        return [
            'parent_id'    => ['nullable', 'exists:expense_heads,id'],
            'expense_name' => ['required', 'string', 'max:255', 'unique:expense_heads,expense_name,' . $this->route('expense_head')],
            'status'       => ['boolean'],
        ];
    }
}
