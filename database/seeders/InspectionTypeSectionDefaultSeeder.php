<?php

namespace Database\Seeders;

use App\Models\InspectionSection;
use App\Models\InspectionType;
use App\Models\InspectionTypeSectionDefault;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class InspectionTypeSectionDefaultSeeder extends Seeder
{
    /**
     * Seeds inspection type → section assignments.
     *
     * Each entry supports an optional 'category' key:
     *   - null / omitted  → Global (applies to all product categories)
     *   - 'Garments'      → Only when the sample belongs to that category
     *
     * The unique constraint is (inspection_type_id, inspection_section_id, category_id),
     * so the same section can appear once globally AND once per specific category.
     */
    public function run(): void
    {
        $sections   = InspectionSection::all()->keyBy('slug');
        $categories = ProductCategory::all()->keyBy('category_name');

        // Format: 'Type name fragment' => [ ['slug', 'required', 'category' (optional)] ]
        $map = [
            'Sample Check' => [
                ['slug' => 'product_screening',    'required' => false, 'category' => null],
                ['slug' => 'sample_conformity',    'required' => true,  'category' => null],
                ['slug' => 'workmanship_check',    'required' => true,  'category' => null],
                ['slug' => 'measurement_check',    'required' => false, 'category' => null],
                ['slug' => 'defect_recording',     'required' => false, 'category' => null],
            ],
            'Pre-Production' => [
                ['slug' => 'factory_readiness',        'required' => true,  'category' => null],
                ['slug' => 'raw_material_check',       'required' => true,  'category' => null],
                ['slug' => 'pre_production_checklist', 'required' => true,  'category' => null],
                ['slug' => 'sample_conformity',        'required' => false, 'category' => null],
                ['slug' => 'defect_recording',         'required' => false, 'category' => null],
            ],
            'Inline' => [
                ['slug' => 'product_screening',  'required' => false, 'category' => null],
                ['slug' => 'workmanship_check',  'required' => true,  'category' => null],
                ['slug' => 'defect_recording',   'required' => true,  'category' => null],
                ['slug' => 'measurement_check',  'required' => false, 'category' => null],
                ['slug' => 'packing_check',      'required' => false, 'category' => null],
            ],
            'Final Quality' => [
                // Global sections — applied to every sample category
                ['slug' => 'product_screening',  'required' => false, 'category' => null],
                ['slug' => 'workmanship_check',  'required' => true,  'category' => null],
                ['slug' => 'aql_sampling',       'required' => true,  'category' => null],
                ['slug' => 'measurement_check',  'required' => true,  'category' => null],
                ['slug' => 'packing_check',      'required' => true,  'category' => null],
                ['slug' => 'labels_check',       'required' => true,  'category' => null],
                ['slug' => 'marking_check',      'required' => false, 'category' => null],
                ['slug' => 'defect_recording',   'required' => true,  'category' => null],
                ['slug' => 'final_review',       'required' => false, 'category' => null],
                // Category-specific sections — only when sample is Garments
                ['slug' => 'functional_test',    'required' => false, 'category' => 'Garments'],
                // Category-specific sections — only when sample is Electronics
                ['slug' => 'functional_test',    'required' => true,  'category' => 'Electronics'],
                ['slug' => 'barcode_testing',    'required' => false, 'category' => 'Electronics'],
            ],
            'Re-Inspection' => [
                ['slug' => 'workmanship_check',  'required' => true,  'category' => null],
                ['slug' => 'aql_sampling',       'required' => true,  'category' => null],
                ['slug' => 'defect_recording',   'required' => true,  'category' => null],
                ['slug' => 'corrective_action',  'required' => false, 'category' => null],
                ['slug' => 'final_review',       'required' => false, 'category' => null],
            ],
            'Container Loading' => [
                ['slug' => 'product_screening',     'required' => false, 'category' => null],
                ['slug' => 'carton_verification',   'required' => true,  'category' => null],
                ['slug' => 'packaging_check',       'required' => false, 'category' => null],
                ['slug' => 'labels_check',          'required' => false, 'category' => null],
                ['slug' => 'container_details',     'required' => true,  'category' => null],
                ['slug' => 'seal_verification',     'required' => true,  'category' => null],
                ['slug' => 'shipment_verification', 'required' => true,  'category' => null],
            ],
        ];

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

                $categoryId = null;
                if (! empty($entry['category'])) {
                    $cat = $categories->get($entry['category']);
                    if (! $cat) {
                        continue; // skip if the category doesn't exist yet
                    }
                    $categoryId = $cat->id;
                }

                InspectionTypeSectionDefault::updateOrCreate(
                    [
                        'inspection_type_id'    => $type->id,
                        'inspection_section_id' => $section->id,
                        'category_id'           => $categoryId,
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
