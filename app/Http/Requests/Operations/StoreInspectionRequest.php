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
            'sample_id'       => ['required', 'exists:samples,id'],
            'inspection_date' => ['required', 'date'],
            'inspector_type'  => ['required', Rule::in(['Employee', 'Vendor'])],
            'inspector_id'    => ['required', 'integer'],
            'overall_status'  => ['required', Rule::in(['Pass', 'Fail', 'Pending'])],
            'remarks'         => ['nullable', 'string'],
            'results'         => ['nullable', 'array'],
            'results.*.sample_testing_parameter_id' => ['required_with:results', 'exists:sample_testing_parameters,id'],
            'results.*.actual_result'               => ['required_with:results', 'string', 'max:255'],
            'results.*.pass_fail'                   => ['required_with:results', Rule::in(['Pass', 'Fail'])],
            'results.*.remarks'                     => ['nullable', 'string'],
            'results.*.attachment'                  => ['nullable', 'file', 'max:5120'],
        ];
    }
}
