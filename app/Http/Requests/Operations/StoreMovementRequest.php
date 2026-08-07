<?php

namespace App\Http\Requests\Operations;

use App\Models\Movement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sample-movements.create');
    }

    public function rules(): array
    {
        // Recipient table to validate recipient_id against depends on the chosen type.
        $recipientTable = match ($this->input('recipient_type')) {
            'Supplier' => 'suppliers',
            'Customer' => 'customers',
            default => null,
        };

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.sample_id' => ['required', 'exists:samples,id'],
            'items.*.variation_id' => ['nullable', 'exists:sample_variations,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'inspection_run_id' => ['nullable', 'exists:inspection_runs,id'],
            'recipient_type' => ['required', Rule::in(array_keys(Movement::RECIPIENT_TYPES))],
            'recipient_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(in_array($this->input('recipient_type'), ['Supplier', 'Customer'])),
                $recipientTable ? Rule::exists($recipientTable, 'id') : 'nullable',
            ],
            // Only required when recipient_type is Employee — Supplier/Customer recipients
            // don't need internal staff assigned.
            'employee_ids' => [Rule::requiredIf($this->input('recipient_type', 'Employee') === 'Employee'), 'array', 'min:1'],
            'employee_ids.*' => ['exists:employees,id'],
            'issue_date' => ['required', 'date'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'alert_days' => ['nullable', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Please add at least one sample.',
            'items.min' => 'Please add at least one sample.',
            'employee_ids.required' => 'Please assign at least one employee.',
            'recipient_id.required' => 'Please select a recipient.',
        ];
    }
}
