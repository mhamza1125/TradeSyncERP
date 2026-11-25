<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

/** Voiding an expense or payment needs a written reason (audit F-10). */
class VoidFinanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // the .delete permission is enforced by controller middleware
    }

    public function rules(): array
    {
        return ['void_reason' => ['required', 'string', 'min:3', 'max:500']];
    }

    public function messages(): array
    {
        return ['void_reason.required' => 'Give a reason for voiding; it is kept with the record and in the activity log.'];
    }
}
