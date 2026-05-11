<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('categories.create');
    }

    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string', 'max:255', 'unique:product_categories,category_name'],
            'status'        => ['boolean'],
        ];
    }
}
