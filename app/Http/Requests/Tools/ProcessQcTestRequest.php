<?php

namespace App\Http\Requests\Tools;

use Illuminate\Foundation\Http\FormRequest;

class ProcessQcTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('qc-test.index');
    }

    public function rules(): array
    {
        return [
            'image' => ['required_without:existing_path', 'nullable', 'image', 'mimes:jpg,jpeg,png,bmp,gif,webp', 'max:10240'],
            'existing_path' => ['nullable', 'string'],
            'contains_text' => ['nullable', 'boolean'],
            'psm' => ['required', 'integer', 'in:6,11'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required_without' => 'Upload an image, or use a previously uploaded one to re-run.',
        ];
    }
}
