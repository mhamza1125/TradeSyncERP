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
            'product_name'       => ['required', 'string', 'max:255'],
            'article'            => ['nullable', 'string', 'max:255'],
            'sample_reference'   => ['nullable', 'string', 'max:255'],
            'physical_location'  => ['nullable', 'string', 'max:255'],
            'receive_date'       => ['required', 'date'],
            'alert_days'         => ['nullable', 'integer', 'min:1'],
            'remarks'            => ['nullable', 'string'],
            'variations'             => ['nullable', 'array'],
            'variations.*.color_id'  => ['nullable', 'exists:sample_colors,id'],
            'variations.*.size_id'   => ['nullable', 'exists:sample_sizes,id'],
            'variations.*.quantity'  => ['required_with:variations', 'integer', 'min:1'],
        ];
    }
}
