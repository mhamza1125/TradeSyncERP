<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('salary.create');
    }

    public function rules(): array
    {
        return [
            'month'      => ['required', 'regex:/^\d{4}-\d{2}$/', 'unique:salary_runs,month'],
            'account_id' => ['required', 'exists:accounts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.regex'  => 'Month must be in YYYY-MM format.',
            'month.unique' => 'A salary run already exists for this month.',
        ];
    }
}
