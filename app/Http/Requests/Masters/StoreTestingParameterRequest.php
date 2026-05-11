<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestingParameterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('parameters.create');
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
