<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sample-movements.create');
    }

    public function rules(): array
    {
        return [
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.sample_id'          => ['required', 'exists:samples,id'],
            'items.*.variation_id'       => ['nullable', 'exists:sample_variations,id'],
            'items.*.quantity'           => ['required', 'integer', 'min:0'],
            'employee_ids'               => ['required', 'array', 'min:1'],
            'employee_ids.*'             => ['exists:employees,id'],
            'inspection_run_id'          => ['nullable', 'exists:inspection_runs,id'],
            'issue_date'                 => ['required', 'date'],
            'expected_return_date'       => ['nullable', 'date', 'after_or_equal:issue_date'],
            'alert_days'                 => ['nullable', 'integer', 'min:1'],
            'remarks'                    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'           => 'Please add at least one sample.',
            'items.min'                => 'Please add at least one sample.',
            'employee_ids.required'    => 'Please assign at least one employee.',
            'employee_ids.min'         => 'Please assign at least one employee.',
        ];
    }
}
