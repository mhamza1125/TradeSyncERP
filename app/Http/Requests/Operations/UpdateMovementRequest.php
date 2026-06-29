<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sample-movements.edit');
    }

    public function rules(): array
    {
        return [
            // Movement metadata (all editable, same as create)
            'issue_date'                 => ['required', 'date'],
            'expected_return_date'       => ['nullable', 'date', 'after_or_equal:issue_date'],
            'alert_days'                 => ['nullable', 'integer', 'min:1'],
            'remarks'                    => ['nullable', 'string'],
            'status'                     => ['required', Rule::in(['Issued', 'Returned', 'Overdue'])],
            'actual_return_date'         => ['nullable', 'date'],
            'inspection_run_id'          => ['nullable', 'exists:inspection_runs,id'],

            // Employees
            'employee_ids'               => ['required', 'array', 'min:1'],
            'employee_ids.*'             => ['exists:employees,id'],

            // Items (full replacement — same structure as create)
            'items'                      => ['required', 'array', 'min:1'],
            'items.*.sample_id'          => ['required', 'exists:samples,id'],
            'items.*.variation_id'       => ['nullable', 'exists:sample_variations,id'],
            'items.*.quantity'           => ['required', 'integer', 'min:0'],

            // Per-item return details (optional overrides)
            'items.*.actual_return_date' => ['nullable', 'date'],
            'items.*.item_status'        => ['nullable', Rule::in(['Issued', 'Returned', 'Overdue'])],
            'items.*.item_remarks'       => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'        => 'Please add at least one sample.',
            'items.min'             => 'Please add at least one sample.',
            'employee_ids.required' => 'Please assign at least one employee.',
            'employee_ids.min'      => 'Please assign at least one employee.',
        ];
    }
}
