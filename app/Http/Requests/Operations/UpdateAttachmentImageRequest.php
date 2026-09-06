<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttachmentImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inspections.edit');
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ];
    }
}
