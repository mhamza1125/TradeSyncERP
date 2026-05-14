<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('customer-orders.edit');
    }

    public function rules(): array
    {
        return [
            'customer_id'             => ['required', 'exists:customers,id'],
            'brand_id'                => ['nullable', 'exists:brands,id'],
            'order_date'              => ['required', 'date'],
            'required_by'             => ['nullable', 'date', 'after_or_equal:order_date'],
            'status'                  => ['required', 'in:Draft,Confirmed,Processing,Dispatched,Cancelled'],
            'remarks'                 => ['nullable', 'string'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_name'    => ['required', 'string', 'max:255'],
            'items.*.description'     => ['nullable', 'string'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
            'items.*.unit'            => ['nullable', 'string', 'max:50'],
            'items.*.remarks'         => ['nullable', 'string'],
        ];
    }
}
