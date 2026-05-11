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
            'lines'                => ['required', 'array'],
            'lines.*.id'           => ['required', 'exists:salary_run_lines,id'],
            'lines.*.basic_salary' => ['required', 'numeric', 'min:0'],
            'lines.*.bonus'        => ['nullable', 'numeric', 'min:0'],
            'lines.*.deduction'    => ['nullable', 'numeric', 'min:0'],
            'lines.*.advance'      => ['nullable', 'numeric', 'min:0'],
            'lines.*.remarks'      => ['nullable', 'string'],
        ];
    }
}
