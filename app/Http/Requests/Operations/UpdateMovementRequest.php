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
            'actual_return_date'         => ['nullable', 'date'],
            'status'                     => ['required', Rule::in(['Issued', 'Returned', 'Overdue'])],
            'remarks'                    => ['nullable', 'string'],
            'employee_ids'               => ['nullable', 'array'],
            'employee_ids.*'             => ['exists:employees,id'],
            'items'                      => ['nullable', 'array'],
            'items.*.id'                 => ['required', 'exists:movement_items,id'],
            'items.*.actual_return_date' => ['nullable', 'date'],
            'items.*.status'             => ['nullable', Rule::in(['Issued', 'Returned', 'Overdue'])],
            'items.*.remarks'            => ['nullable', 'string'],
        ];
    }
}
