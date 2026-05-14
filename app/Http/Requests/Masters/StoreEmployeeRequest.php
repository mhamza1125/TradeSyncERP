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
            'employee_name'     => ['required', 'string', 'max:255'],
            'father_name'       => ['nullable', 'string', 'max:255'],
            'department'        => ['nullable', 'string', 'max:255'],
            'designation'       => ['nullable', 'string', 'max:255'],
            'job_title'         => ['nullable', 'string', 'max:255'],
            'phone'             => ['required', 'string', 'max:50'],
            'nic'               => ['nullable', 'string', 'max:50'],
            'dob'               => ['nullable', 'date'],
            'gender'            => ['nullable', 'in:Male,Female,Other'],
            'marital_status'    => ['nullable', 'in:Single,Married,Divorced,Widowed'],
            'emergency_contact' => ['nullable', 'string', 'max:50'],
            'address'           => ['nullable', 'string'],
            'city'              => ['nullable', 'string', 'max:100'],
            'country'           => ['nullable', 'string', 'max:100'],
            'postal_code'       => ['nullable', 'string', 'max:20'],
            'joining_date'      => ['nullable', 'date'],
            'hire_date'         => ['nullable', 'date'],
            'basic_salary'      => ['nullable', 'numeric', 'min:0'],
            'salary'            => ['nullable', 'numeric', 'min:0'],
            'status'            => ['boolean'],
            'remarks'           => ['nullable', 'string'],

            'experiences'                       => ['nullable', 'array'],
            'experiences.*.company_name'        => ['required_with:experiences', 'string', 'max:255'],
            'experiences.*.designation'         => ['nullable', 'string', 'max:255'],
            'experiences.*.start_date'          => ['nullable', 'date'],
            'experiences.*.end_date'            => ['nullable', 'date'],
            'experiences.*.responsibilities'    => ['nullable', 'string'],
        ];
    }
}
