<?php

namespace App\Http\Requests\Tools;

use Illuminate\Foundation\Http\FormRequest;

class StoreAqlCalculationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('aql-calculator.create');
    }

    public function rules(): array
    {
        $aqlLevels = ['not_allowed', '0.065', '0.10', '0.15', '0.25', '0.40', '0.65', '1.0', '1.5', '2.5', '4.0', '6.5'];

        return [
            'title' => ['required', 'string', 'max:255'],
            'lot_size' => ['required', 'integer', 'min:2'],
            'inspection_level' => ['required', 'string', 'in:I,II,III,S1,S2,S3,S4'],
            'aql_critical' => ['nullable', 'string', 'in:'.implode(',', $aqlLevels)],
            'aql_major' => ['nullable', 'string', 'in:'.implode(',', $aqlLevels)],
            'aql_minor' => ['nullable', 'string', 'in:'.implode(',', $aqlLevels)],
            'found_critical' => ['nullable', 'integer', 'min:0'],
            'found_major' => ['nullable', 'integer', 'min:0'],
            'found_minor' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'variations' => ['nullable', 'array'],
            'variations.*.color' => ['nullable', 'string', 'max:255'],
            'variations.*.size' => ['nullable', 'string', 'max:255'],
            'variations.*.qty' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
