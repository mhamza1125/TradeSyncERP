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

            'results'                                    => ['nullable', 'array'],
            'results.*.sample_testing_parameter_id'      => ['required_with:results', 'exists:sample_testing_parameters,id'],
            'results.*.actual_result'                    => ['nullable', 'string', 'max:255'],
            'results.*.pass_fail'                        => ['nullable', Rule::in(['Pass', 'Fail'])],
            'results.*.status'                           => ['nullable', Rule::in(['Approve', 'Reject', 'Review'])],
            'results.*.remarks'                          => ['nullable', 'string'],
            'results.*.attachment'                       => ['nullable', 'file', 'max:5120'],
        ];
    }
}
