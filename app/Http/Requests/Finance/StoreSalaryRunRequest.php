<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalaryRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('salaryRun') ? 'salary.edit' : 'salary.create';
        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        $salaryRun = $this->route('salaryRun');

        return [
            'month'        => [
                'required',
                'regex:/^\d{4}-\d{2}$/',
                Rule::unique('salary_runs', 'month')->ignore($salaryRun),
            ],
            'account_id'   => ['required', 'exists:accounts,id'],
            'working_days' => ['nullable', 'integer', 'min:0'],
            'off_days'     => ['nullable', 'integer', 'min:0'],
            'remarks'      => ['nullable', 'string'],
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
