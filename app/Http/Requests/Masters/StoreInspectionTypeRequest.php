<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspectionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inspection-types.create');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'unique:inspection_types,name'],
            'description' => ['nullable', 'string'],
            'status'      => ['boolean'],
        ];
    }
}
