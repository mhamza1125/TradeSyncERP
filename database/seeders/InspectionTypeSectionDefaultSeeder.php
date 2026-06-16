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

        // Format: 'Type name' => [ ['slug', 'required', 'category' (optional)] ]
        // Sections are listed in display order (array index drives sort_order via ($i+1)*10).
        // finish_inspection is always the last section for every type.
        $map = [
            'SMS (Sample Inspection)' => [
                ['slug' => 'general_information',      'required' => true, 'category' => null],
                ['slug' => 'packing_check_si',         'required' => true, 'category' => null],
                ['slug' => 'carton_dimensions_weight', 'required' => true, 'category' => null],
                ['slug' => 'product_screening',        'required' => true, 'category' => null],
                ['slug' => 'barcode_testing',          'required' => true, 'category' => null],
                ['slug' => 'functional_test',          'required' => false, 'category' => null],
                ['slug' => 'article_results_table',    'required' => true, 'category' => null],
                ['slug' => 'defect_recording',         'required' => true, 'category' => null],
                ['slug' => 'variations_sample',        'required' => true, 'category' => null],
                ['slug' => 'variations_techpack',      'required' => true, 'category' => null],
                ['slug' => 'overall_article_result',   'required' => true, 'category' => null],
                ['slug' => 'finish_inspection',        'required' => true, 'category' => null],
            ],
            'PPS (Pre-Production Inspection)' => [
                ['slug' => 'general_information',    'required' => true, 'category' => null],
                ['slug' => 'product_screening',      'required' => true, 'category' => null],
                ['slug' => 'barcode_testing',        'required' => true, 'category' => null],
                ['slug' => 'functional_test',        'required' => false, 'category' => null],
                ['slug' => 'article_results_table',  'required' => true, 'category' => null],
                ['slug' => 'defect_recording',       'required' => true, 'category' => null],
                ['slug' => 'variations_sample',      'required' => true, 'category' => null],
                ['slug' => 'variations_techpack',    'required' => true, 'category' => null],
                ['slug' => 'overall_article_result', 'required' => true, 'category' => null],
                ['slug' => 'finish_inspection',      'required' => true, 'category' => null],
            ],
            'Inline Inspection (DUPRO)' => [
                ['slug' => 'production_status',      'required' => true, 'category' => null],
                ['slug' => 'defect_recording',       'required' => true, 'category' => null],
                ['slug' => 'measurements_check_si',  'required' => true, 'category' => null],
                ['slug' => 'variations_sample',      'required' => true, 'category' => null],
                ['slug' => 'inspection_conclusion',  'required' => true, 'category' => null],
                ['slug' => 'finish_inspection',      'required' => true, 'category' => null],
            ],
            'Final Inspection (AQL / Percentage Based)' => [
                ['slug' => 'general_information',      'required' => true, 'category' => null],
                ['slug' => 'packing_check_si',         'required' => true, 'category' => null],
                ['slug' => 'carton_dimensions_weight', 'required' => true, 'category' => null],
                ['slug' => 'product_screening',        'required' => true, 'category' => null],
                ['slug' => 'barcode_testing',          'required' => true, 'category' => null],
                ['slug' => 'functional_test',          'required' => false, 'category' => null],
                ['slug' => 'aql_sampling',             'required' => true, 'category' => null],
                ['slug' => 'article_results_table',    'required' => true, 'category' => null],
                ['slug' => 'defect_recording',         'required' => true, 'category' => null],
                ['slug' => 'variations_sample',        'required' => true, 'category' => null],
                ['slug' => 'variations_techpack',      'required' => true, 'category' => null],
                ['slug' => 'overall_article_result',   'required' => true, 'category' => null],
                ['slug' => 'finish_inspection',        'required' => true, 'category' => null],
            ],
            'Final Inspection (100%)' => [
                ['slug' => 'article_results_table',  'required' => true, 'category' => null],
                ['slug' => 'defect_recording',       'required' => true, 'category' => null],
                ['slug' => 'overall_article_result', 'required' => true, 'category' => null],
                ['slug' => 'inspection_conclusion',  'required' => true, 'category' => null],
                ['slug' => 'finish_inspection',      'required' => true, 'category' => null],
            ],
            'Re-Inspection' => [
                ['slug' => 'general_information',    'required' => true, 'category' => null],
                ['slug' => 'packing_check_si',       'required' => true, 'category' => null],
                ['slug' => 'aql_sampling',           'required' => true, 'category' => null],
                ['slug' => 'article_results_table',  'required' => true, 'category' => null],
                ['slug' => 'overall_article_result', 'required' => true, 'category' => null],
                ['slug' => 'defect_recording',       'required' => true, 'category' => null],
                ['slug' => 'inspection_conclusion',  'required' => true, 'category' => null],
                ['slug' => 'finish_inspection',      'required' => true, 'category' => null],
            ],
            'Container Loading Inspection (CLI)' => [
                // Ch. 1 — Order vs Packing List
                ['slug' => 'order_quantity_vs_packing_list', 'required' => true, 'category' => null],
                // Ch. 2 — Loading Schedule
                ['slug' => 'loading_schedule_and_timing',    'required' => true, 'category' => null],
                // Ch. 3 — Container Details (admin) + Container Condition (assessment)
                ['slug' => 'container_details',              'required' => true, 'category' => null],
                ['slug' => 'inner_conditions_of_container',  'required' => true, 'category' => null],
                // Ch. 4 — Carton counts + quantity per carton (consolidated in carton_verification)
                ['slug' => 'carton_verification',            'required' => true, 'category' => null],
                // Ch. 5 — Overall Carton Condition (final verdict)
                ['slug' => 'overall_carton_condition',       'required' => true, 'category' => null],
                ['slug' => 'finish_inspection',              'required' => true, 'category' => null],
            ],
        ];

        // ── Cleanup: drop any pre-existing type-default assignments outside the map ─
        foreach (array_keys($map) as $typeName) {
            $type = InspectionType::where('name', $typeName)->first();
            if (! $type) {
                continue;
            }

            $keepIds = $sections->only(collect($map[$typeName])->pluck('slug')->all())->pluck('id');
            InspectionTypeSectionDefault::where('inspection_type_id', $type->id)
                ->whereNotIn('inspection_section_id', $keepIds)
                ->delete();
        }

        // ── Remove all type assignments for deprecated / removed sections ──────────
        $removedSlugIds = $sections->whereIn('slug', [
            'corrective_action',          'textile_sample_conformity',
            'denim_textile_defects',      'textile_leather_functional',
            'sample_conformity',          'marking_check',
            'measurement_check',          'packing_check',
            'protector_evaluation',       'number_of_cartons_loaded',
            'quantity_per_carton',
        ])->pluck('id');

        if ($removedSlugIds->isNotEmpty()) {
            InspectionTypeSectionDefault::whereIn('inspection_section_id', $removedSlugIds)->delete();
        }

        foreach ($map as $typeName => $sectionList) {
            $type = InspectionType::where('name', $typeName)->first();
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
                        continue;
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
