<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('categories.edit');
    }

    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string', 'max:255', 'unique:product_categories,category_name,' . $this->route('category')],
            'status'        => ['boolean'],
        ];
    }
}
