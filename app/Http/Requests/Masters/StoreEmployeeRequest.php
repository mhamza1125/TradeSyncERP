<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('employees.create');
    }

    public function rules(): array
    {
        return [
            'employee_name' => ['required', 'string', 'max:255'],
            'department'    => ['required', 'string', 'max:255'],
            'designation'   => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:50'],
            'joining_date'  => ['required', 'date'],
            'basic_salary'  => ['required', 'numeric', 'min:0'],
            'status'        => ['boolean'],
        ];
    }
}
