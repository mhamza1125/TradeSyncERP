<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('samples.edit');
    }

    public function rules(): array
    {
        return [
            'category_id'        => ['required', 'exists:product_categories,id'],
            'customer_id'        => ['required', 'exists:customers,id'],
            'supplier_id'        => ['nullable', 'exists:suppliers,id'],
            'brand_id'           => ['required', 'exists:brands,id'],
            'product_name'       => ['required', 'string', 'max:255'],
            'sample_reference'   => ['nullable', 'string', 'max:255'],
            'physical_location'  => ['nullable', 'string', 'max:255'],
            'receive_date'       => ['required', 'date'],
            'quantity'           => ['required', 'integer', 'min:1'],
            'priority_level'     => ['required', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'alert_days'         => ['nullable', 'integer', 'min:1'],
            'status'             => ['required', Rule::in(['Received', 'In Testing', 'Completed', 'Returned'])],
            'remarks'            => ['nullable', 'string'],
        ];
    }
}
