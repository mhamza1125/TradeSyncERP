<?php

namespace App\Http\Requests\Admin;

use App\Services\Finance\InvoiceNumberService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // General Information
            'company_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],

            // Contact Information
            'phone' => ['nullable', 'string', 'max:50'],
            'fax' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],

            // Tax & Registration
            'registration_number' => ['nullable', 'string', 'max:100'],
            'ntn_number' => ['nullable', 'string', 'max:100'],
            'strn_number' => ['nullable', 'string', 'max:100'],

            // Management
            'ceo_name' => ['nullable', 'string', 'max:255'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:50'],
            'contact_person_email' => ['nullable', 'email', 'max:255'],

            // Document defaults
            'default_terms' => ['nullable', 'string', 'max:2000'],

            // Invoice numbering
            'invoice_number_pattern' => [
                'required', 'string', 'max:100',
                function ($attribute, $value, $fail) {
                    if (! app(InvoiceNumberService::class)->hasIdPlaceholder($value)) {
                        $fail('The invoice number pattern must include an {id} placeholder so every invoice number stays unique.');
                    }
                },
            ],
        ];
    }
}
