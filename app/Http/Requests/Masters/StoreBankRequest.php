<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('banks.create');
    }

    public function rules(): array
    {
        return [
            'bank_name'      => ['required', 'string', 'max:255'],
            'branch_name'    => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'swift_code'     => ['nullable', 'string', 'max:50'],
            'status'         => ['boolean'],
        ];
    }
}
