<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSampleMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('sample-movements.edit');
    }

    public function rules(): array
    {
        return [
            'actual_return_date'   => ['nullable', 'date'],
            'status'               => ['required', Rule::in(['Issued', 'Returned', 'Overdue'])],
            'remarks'              => ['nullable', 'string'],
        ];
    }
}
