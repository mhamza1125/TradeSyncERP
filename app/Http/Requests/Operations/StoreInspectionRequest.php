<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // permission:inspections.create is enforced by controller middleware
    }

    public function rules(): array
    {
        return [
            'inspection_type_id' => ['required', 'exists:inspection_types,id'],
            'inspection_date' => ['required', 'date'],
            'customer_order_ids' => ['nullable', 'array'],
            'customer_order_ids.*' => ['exists:customer_orders,id'],
            'inspector_ids' => ['nullable', 'array'],
            'inspector_ids.*' => ['exists:employees,id'],
            // Derived from the runs (docs/QC_RULES.md), never typed in.
            'overall_status' => ['prohibited'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return ['overall_status.prohibited' => 'The overall status is calculated from the runs and cannot be set by hand.'];
    }
}
