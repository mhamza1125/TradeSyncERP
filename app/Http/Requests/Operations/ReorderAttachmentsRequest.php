<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class ReorderAttachmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inspections.edit');
    }

    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'distinct'],
        ];
    }
}
