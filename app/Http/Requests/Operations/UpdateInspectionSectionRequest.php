<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInspectionSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inspection-sections.edit');
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100'],
            'slug'         => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/',
                'unique:inspection_sections,slug,' . $this->route('inspectionSection')?->id],
            'description'  => ['nullable', 'string', 'max:500'],
            'section_type' => ['required', 'in:general_info,images,workmanship,defects,aql,checklist,container,verification,review,article_results,conclusion,finish,quantity_sampling,cartons,cover_photo,files_review,checkpoint,production_stages'],
            'icon'         => ['nullable', 'string', 'max:100'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'is_active'    => ['nullable', 'boolean'],
            'skip_policy'  => ['nullable', 'in:mandatory,optional,skippable'],
            'default_data' => ['nullable', 'json'],
        ];
    }
}
