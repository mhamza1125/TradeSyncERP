<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Generic attachment upload (AttachmentController). Same allowlist as inspection run
 * uploads: images, PDF and Office files. HTML/SVG/scripts are refused because uploads
 * are served from the app's own origin (audit F-06).
 */
class StoreAttachmentRequest extends FormRequest
{
    /** Checked against the detected content type (mimes) and the file name (extensions). */
    public const ALLOWED = 'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx';

    public function authorize(): bool
    {
        return true; // permission is checked in AttachmentController against the parent type
    }

    public function rules(): array
    {
        return [
            'attachments' => ['required', 'array', 'min:1'],
            'attachments.*' => ['required', 'file', 'max:20480', 'mimes:'.self::ALLOWED, 'extensions:'.self::ALLOWED],
            'attachment_titles' => ['required', 'array'],
            'attachment_titles.*' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.*.mimes' => 'Only images, PDF and Office files (Word, Excel) can be attached.',
            'attachments.*.extensions' => 'Only images, PDF and Office files (Word, Excel) can be attached.',
        ];
    }
}
