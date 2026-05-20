<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalaryRunLinesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('salary.edit');
    }

    public function rules(): array
    {
        return [
            'account_id'                     => ['nullable', 'exists:accounts,id'],
            'working_days'                   => ['nullable', 'integer', 'min:0'],
            'off_days'                       => ['nullable', 'integer', 'min:0'],
            'remarks'                        => ['nullable', 'string'],
            'lines'                          => ['required', 'array'],
            'lines.*.id'                     => ['required', 'exists:salary_run_lines,id'],
            'lines.*.basic_salary'           => ['required', 'numeric', 'min:0'],
            'lines.*.bonus'                  => ['nullable', 'numeric', 'min:0'],
            'lines.*.deduction'              => ['nullable', 'numeric', 'min:0'],
            'lines.*.advance'                => ['nullable', 'numeric', 'min:0'],
            'lines.*.allowances'             => ['nullable', 'numeric', 'min:0'],
            'lines.*.leave_days'             => ['nullable', 'integer', 'min:0'],
            'lines.*.leave_deduction_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.total_leaves'           => ['nullable', 'integer', 'min:0'],
            'lines.*.deductible_leaves'      => ['nullable', 'integer', 'min:0'],
            'lines.*.loan_balance'           => ['nullable', 'numeric', 'min:0'],
            'lines.*.loan_deduction'         => ['nullable', 'numeric', 'min:0'],
            'lines.*.remarks'                => ['nullable', 'string'],
        ];
    }
}
