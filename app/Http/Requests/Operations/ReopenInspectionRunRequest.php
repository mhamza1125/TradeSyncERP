<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class ReopenInspectionRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // permission:inspections.reopen is enforced by controller middleware
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return ['reason.required' => 'Give a reason for reopening this finished run; it is kept in the activity log.'];
    }
}
