<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('samples.create');
    }

    public function rules(): array
    {
        return [
            'category_id'        => ['required', 'exists:product_categories,id'],
            'customer_id'        => ['required', 'exists:customers,id'],
            'brand_id'           => ['required', 'exists:brands,id'],
            'product_name'       => ['required', 'string', 'max:255'],
            'shipment_reference' => ['nullable', 'string', 'max:255'],
            'receive_date'       => ['required', 'date'],
            'quantity'           => ['required', 'integer', 'min:1'],
            'priority_level'     => ['required', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'alert_days'         => ['nullable', 'integer', 'min:1'],
            'remarks'            => ['nullable', 'string'],
            'parameters'         => ['nullable', 'array'],
            'parameters.*.parameter_id'        => ['required_with:parameters', 'exists:testing_parameters_master,id'],
            'parameters.*.requirement_standard' => ['nullable', 'string', 'max:255'],
            'parameters.*.remarks'              => ['nullable', 'string'],
        ];
    }
}
