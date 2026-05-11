<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSampleMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sample-movements.create');
    }

    public function rules(): array
    {
        return [
            'sample_id'            => ['required', 'exists:samples,id'],
            'moved_by_type'        => ['required', 'in:Employee,User'],
            'moved_by_id'          => ['required', 'integer'],
            'assigned_to_type'     => ['required', Rule::in(['Employee', 'Vendor', 'Storage', 'Customer'])],
            'assigned_to_id'       => ['required', 'integer'],
            'issue_date'           => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'alert_days'           => ['nullable', 'integer', 'min:1'],
            'remarks'              => ['nullable', 'string'],
        ];
    }
}
