<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inspections.create');
    }

    public function rules(): array
    {
        return [
            'inspection_type_id' => ['nullable', 'exists:inspection_types,id'],
            'inspection_date'    => ['required', 'date'],
            'inspector_ids'      => ['nullable', 'array'],
            'inspector_ids.*'    => ['exists:employees,id'],
            'overall_status'     => ['nullable', Rule::in(['Pass', 'Fail', 'Pending'])],
            'remarks'            => ['nullable', 'string'],
        ];
    }
}
