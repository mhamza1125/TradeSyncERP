<?php

namespace Database\Seeders;

use App\Models\InspectionSection;
use App\Models\InspectionType;
use App\Models\InspectionTypeSectionDefault;
use Illuminate\Database\Seeder;

class InspectionTypeSectionDefaultSeeder extends Seeder
{
    public function run(): void
    {
        // Maps inspection type name fragment → ordered section slugs
        // is_required = true means the user cannot uncheck this section
        $map = [
            'Sample Check' => [
                ['slug' => 'product_screening',    'required' => false],
                ['slug' => 'sample_conformity',    'required' => true],
                ['slug' => 'workmanship_check',    'required' => true],
                ['slug' => 'measurement_check',    'required' => false],
                ['slug' => 'defect_recording',     'required' => false],
            ],
            'Pre-Production' => [
                ['slug' => 'factory_readiness',         'required' => true],
                ['slug' => 'raw_material_check',        'required' => true],
                ['slug' => 'pre_production_checklist',  'required' => true],
                ['slug' => 'sample_conformity',         'required' => false],
                ['slug' => 'defect_recording',          'required' => false],
            ],
            'Inline' => [
                ['slug' => 'product_screening',  'required' => false],
                ['slug' => 'workmanship_check',  'required' => true],
                ['slug' => 'defect_recording',   'required' => true],
                ['slug' => 'measurement_check',  'required' => false],
                ['slug' => 'packing_check',      'required' => false],
            ],
            'Final Quality' => [
                ['slug' => 'product_screening',  'required' => false],
                ['slug' => 'workmanship_check',  'required' => true],
                ['slug' => 'aql_sampling',       'required' => true],
                ['slug' => 'measurement_check',  'required' => true],
                ['slug' => 'functional_test',    'required' => false],
                ['slug' => 'packing_check',      'required' => true],
                ['slug' => 'labels_check',       'required' => true],
                ['slug' => 'marking_check',      'required' => false],
                ['slug' => 'defect_recording',   'required' => true],
                ['slug' => 'final_review',       'required' => false],
            ],
            'Re-Inspection' => [
                ['slug' => 'workmanship_check',  'required' => true],
                ['slug' => 'aql_sampling',       'required' => true],
                ['slug' => 'defect_recording',   'required' => true],
                ['slug' => 'corrective_action',  'required' => false],
                ['slug' => 'final_review',       'required' => false],
            ],
            'Container Loading' => [
                ['slug' => 'product_screening',      'required' => false],
                ['slug' => 'carton_verification',    'required' => true],
                ['slug' => 'packaging_check',        'required' => false],
                ['slug' => 'labels_check',           'required' => false],
                ['slug' => 'container_details',      'required' => true],
                ['slug' => 'seal_verification',      'required' => true],
                ['slug' => 'shipment_verification',  'required' => true],
            ],
        ];

        // Pre-load all sections by slug for fast lookup
        $sections = InspectionSection::all()->keyBy('slug');

        foreach ($map as $typeNameFragment => $sectionList) {
            $type = InspectionType::where('name', 'like', "%{$typeNameFragment}%")->first();
            if (! $type) {
                continue;
            }

            foreach ($sectionList as $order => $entry) {
                $section = $sections->get($entry['slug']);
                if (! $section) {
                    continue;
                }

                InspectionTypeSectionDefault::updateOrCreate(
                    [
                        'inspection_type_id'    => $type->id,
                        'inspection_section_id' => $section->id,
                    ],
                    [
                        'sort_order'  => ($order + 1) * 10,
                        'is_required' => $entry['required'],
                    ]
                );
            }
        }
    }
}
