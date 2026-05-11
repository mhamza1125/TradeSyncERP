<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('brands.create');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'brand_name'  => ['required', 'string', 'max:255'],
            'remarks'     => ['nullable', 'string'],
            'status'      => ['boolean'],
        ];
    }
}
