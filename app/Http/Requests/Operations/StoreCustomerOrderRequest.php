<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customer-orders.create');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'order_number' => ['nullable', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'required_by' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['required', 'in:Due,Done'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_category_id' => ['nullable', 'exists:product_categories,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.remarks' => ['nullable', 'string'],
        ];
    }
}
