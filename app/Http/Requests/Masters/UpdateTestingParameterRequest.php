<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestingParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('parameters.edit');
    }

    public function rules(): array
    {
        return [
            'category_id'    => ['required', 'exists:product_categories,id'],
            'parameter_name' => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'status'         => ['boolean'],
        ];
    }
}
