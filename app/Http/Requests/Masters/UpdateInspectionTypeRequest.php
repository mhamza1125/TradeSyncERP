<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInspectionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inspection-types.edit');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', Rule::unique('inspection_types', 'name')->ignore($this->inspectionType)],
            'description' => ['nullable', 'string'],
            'status'      => ['boolean'],
        ];
    }
}
