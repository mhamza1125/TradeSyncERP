<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('currencies.edit');
    }

    public function rules(): array
    {
        return [
            'currency_name' => ['required', 'string', 'max:255'],
            'currency_code' => ['required', 'string', 'max:10', 'unique:currencies,currency_code,' . $this->route('currency')],
            'symbol'        => ['required', 'string', 'max:10'],
            'exchange_rate' => ['required', 'numeric', 'min:0'],
            'is_default'    => ['boolean'],
            'status'        => ['boolean'],
        ];
    }
}
