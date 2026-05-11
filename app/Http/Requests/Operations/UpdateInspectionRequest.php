<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inspections.edit');
    }

    public function rules(): array
    {
        return [
            'inspection_date' => ['required', 'date'],
            'inspector_type'  => ['required', Rule::in(['Employee', 'Vendor'])],
            'inspector_id'    => ['required', 'integer'],
            'overall_status'  => ['required', Rule::in(['Pass', 'Fail', 'Pending'])],
            'remarks'         => ['nullable', 'string'],
        ];
    }
}
