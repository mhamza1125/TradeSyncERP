<?php

namespace Database\Seeders;

use App\Models\InspectionSection;
use Illuminate\Database\Seeder;

class InspectionSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'name'         => 'Product Screening',
                'slug'         => 'product_screening',
                'description'  => 'Capture photos of product samples, packaging, labels, and workmanship overview.',
                'section_type' => 'images',
                'icon'         => 'feather-camera',
                'sort_order'   => 10,
                'default_data' => ['notes' => ''],
            ],
            [
                'name'         => 'Workmanship Check',
                'slug'         => 'workmanship_check',
                'description'  => 'Evaluate product testing parameters against quality specifications. Records pass/fail per parameter.',
                'section_type' => 'workmanship',
                'icon'         => 'feather-check-square',
                'sort_order'   => 20,
                'default_data' => ['use_inspection_results' => true],
            ],
            [
                'name'         => 'Defect Recording',
                'slug'         => 'defect_recording',
                'description'  => 'Record and classify defects observed during inspection (Critical / Major / Minor).',
                'section_type' => 'checklist',
                'icon'         => 'feather-alert-triangle',
                'sort_order'   => 30,
                'default_data' => [
                    'items' => [
                        ['label' => 'Stitching / Seam defects',     'result' => null, 'quantity' => 0, 'severity' => null, 'remarks' => ''],
                        ['label' => 'Fabric / Material defects',    'result' => null, 'quantity' => 0, 'severity' => null, 'remarks' => ''],
                        ['label' => 'Color / Shade variation',      'result' => null, 'quantity' => 0, 'severity' => null, 'remarks' => ''],
                        ['label' => 'Measurement out of tolerance',  'result' => null, 'quantity' => 0, 'severity' => null, 'remarks' => ''],
                        ['label' => 'Soiling / Staining',           'result' => null, 'quantity' => 0, 'severity' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'AQL Sampling',
                'slug'         => 'aql_sampling',
                'description'  => 'ISO 2859-1 AQL sampling plan. Calculates sample size, acceptance/rejection numbers and final verdict.',
                'section_type' => 'aql',
                'icon'         => 'feather-bar-chart-2',
                'sort_order'   => 40,
                'default_data' => ['use_aql_table' => true],
            ],
            [
                'name'         => 'Packing Check',
                'slug'         => 'packing_check',
                'description'  => 'Verify inner packing, polybag, carton, and overall packing specifications.',
                'section_type' => 'checklist',
                'icon'         => 'feather-box',
                'sort_order'   => 50,
                'default_data' => [
                    'items' => [
                        ['label' => 'Inner packing matches specifications', 'result' => null, 'remarks' => ''],
                        ['label' => 'Polybag / inner box correct',          'result' => null, 'remarks' => ''],
                        ['label' => 'Master carton dimensions correct',     'result' => null, 'remarks' => ''],
                        ['label' => 'Carton weight within tolerance',       'result' => null, 'remarks' => ''],
                        ['label' => 'Packing quantity per carton correct',  'result' => null, 'remarks' => ''],
                        ['label' => 'Drop / crush test passed',            'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Carton Verification',
                'slug'         => 'carton_verification',
                'description'  => 'Verify carton list, carton counts, and packing details against shipping documents.',
                'section_type' => 'checklist',
                'icon'         => 'feather-package',
                'sort_order'   => 60,
                'default_data' => [
                    'total_cartons_ordered' => '',
                    'total_cartons_loaded'  => '',
                    'total_qty_ordered'     => '',
                    'total_qty_loaded'      => '',
                    'items' => [
                        ['label' => 'Carton count matches packing list',    'result' => null, 'remarks' => ''],
                        ['label' => 'Carton numbering sequential / correct','result' => null, 'remarks' => ''],
                        ['label' => 'Quantity per carton correct',          'result' => null, 'remarks' => ''],
                        ['label' => 'Carton condition — no damage',         'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Packaging Check',
                'slug'         => 'packaging_check',
                'description'  => 'Assess retail packaging, hang tags, poly bags, and presentation quality.',
                'section_type' => 'checklist',
                'icon'         => 'feather-layers',
                'sort_order'   => 70,
                'default_data' => [
                    'items' => [
                        ['label' => 'Retail packaging matches approved sample', 'result' => null, 'remarks' => ''],
                        ['label' => 'Hang tags / ticketing correct',            'result' => null, 'remarks' => ''],
                        ['label' => 'Polybag thickness meets requirements',     'result' => null, 'remarks' => ''],
                        ['label' => 'Safety warning on polybag present',        'result' => null, 'remarks' => ''],
                        ['label' => 'Packaging artwork / print quality',        'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Labels Check',
                'slug'         => 'labels_check',
                'description'  => 'Verify care labels, composition, country of origin, size, and barcode labels.',
                'section_type' => 'checklist',
                'icon'         => 'feather-tag',
                'sort_order'   => 80,
                'default_data' => [
                    'items' => [
                        ['label' => 'Care / wash label correct',       'result' => null, 'remarks' => ''],
                        ['label' => 'Composition label correct',       'result' => null, 'remarks' => ''],
                        ['label' => 'Country of origin correct',       'result' => null, 'remarks' => ''],
                        ['label' => 'Size label correct',              'result' => null, 'remarks' => ''],
                        ['label' => 'Barcode / EAN / UPC correct',    'result' => null, 'remarks' => ''],
                        ['label' => 'Brand label correctly positioned','result' => null, 'remarks' => ''],
                        ['label' => 'Price / ticketing correct',       'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Measurement Check',
                'slug'         => 'measurement_check',
                'description'  => 'Measure key product dimensions and compare against approved specification sheet.',
                'section_type' => 'checklist',
                'icon'         => 'feather-maximize-2',
                'sort_order'   => 90,
                'default_data' => [
                    'spec_reference' => '',
                    'tolerance'      => '±1 cm',
                    'items' => [
                        ['label' => 'Length', 'spec' => '', 'actual' => '', 'result' => null, 'remarks' => ''],
                        ['label' => 'Width',  'spec' => '', 'actual' => '', 'result' => null, 'remarks' => ''],
                        ['label' => 'Height', 'spec' => '', 'actual' => '', 'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Functional Test',
                'slug'         => 'functional_test',
                'description'  => 'Test product functionality, mechanisms, and performance characteristics.',
                'section_type' => 'checklist',
                'icon'         => 'feather-settings',
                'sort_order'   => 100,
                'default_data' => [
                    'items' => [
                        ['label' => 'Zippers / fasteners function correctly', 'result' => null, 'remarks' => ''],
                        ['label' => 'Buttons / snaps secure',                 'result' => null, 'remarks' => ''],
                        ['label' => 'Seams withstand pull test',              'result' => null, 'remarks' => ''],
                        ['label' => 'Colorfastness acceptable',               'result' => null, 'remarks' => ''],
                        ['label' => 'No sharp edges / points',                'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Marking Check',
                'slug'         => 'marking_check',
                'description'  => 'Verify shipping marks, carton marking, and stencilling against packing list.',
                'section_type' => 'checklist',
                'icon'         => 'feather-edit',
                'sort_order'   => 110,
                'default_data' => [
                    'items' => [
                        ['label' => 'Shipping marks match PO requirements', 'result' => null, 'remarks' => ''],
                        ['label' => 'Carton numbering correct',             'result' => null, 'remarks' => ''],
                        ['label' => 'Net / gross weight marked',            'result' => null, 'remarks' => ''],
                        ['label' => 'Dimensions marked correctly',          'result' => null, 'remarks' => ''],
                        ['label' => 'Destination port marked',              'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Sample Conformity',
                'slug'         => 'sample_conformity',
                'description'  => 'Compare production against approved counter/reference sample.',
                'section_type' => 'checklist',
                'icon'         => 'feather-copy',
                'sort_order'   => 120,
                'default_data' => [
                    'approved_sample_ref' => '',
                    'items' => [
                        ['label' => 'Color matches approved sample',    'result' => null, 'remarks' => ''],
                        ['label' => 'Style / design matches',           'result' => null, 'remarks' => ''],
                        ['label' => 'Trim / accessories match',         'result' => null, 'remarks' => ''],
                        ['label' => 'Fabric / material matches',        'result' => null, 'remarks' => ''],
                        ['label' => 'Overall appearance acceptable',    'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Container Details',
                'slug'         => 'container_details',
                'description'  => 'Record container condition, number, size, and loading details.',
                'section_type' => 'container',
                'icon'         => 'feather-truck',
                'sort_order'   => 130,
                'default_data' => [
                    'container_number'        => '',
                    'container_type'          => '',
                    'seal_number'             => '',
                    'loading_date'            => '',
                    'loading_port'            => '',
                    'discharge_port'          => '',
                    'total_cartons_loaded'    => '',
                    'total_quantity_loaded'   => '',
                    'container_condition'     => null,
                    'container_condition_note'=> '',
                    'notes'                   => '',
                ],
            ],
            [
                'name'         => 'Seal Verification',
                'slug'         => 'seal_verification',
                'description'  => 'Verify and photograph container/shipment seal numbers.',
                'section_type' => 'verification',
                'icon'         => 'feather-lock',
                'sort_order'   => 140,
                'default_data' => [
                    'seal_number'       => '',
                    'seal_intact'       => null,
                    'seal_photo_taken'  => null,
                    'verified_by'       => '',
                    'notes'             => '',
                ],
            ],
            [
                'name'         => 'Shipment Verification',
                'slug'         => 'shipment_verification',
                'description'  => 'Verify shipment quantity, PO matching, and export documents.',
                'section_type' => 'verification',
                'icon'         => 'feather-anchor',
                'sort_order'   => 150,
                'default_data' => [
                    'items' => [
                        ['label' => 'Shipment quantity matches PO',         'result' => null, 'remarks' => ''],
                        ['label' => 'Product matches order description',    'result' => null, 'remarks' => ''],
                        ['label' => 'Packing list matches actual loading',  'result' => null, 'remarks' => ''],
                        ['label' => 'No unauthorized or mixed products',    'result' => null, 'remarks' => ''],
                        ['label' => 'Documentation complete and correct',   'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Pre-Production Checklist',
                'slug'         => 'pre_production_checklist',
                'description'  => 'Verify production readiness before production begins.',
                'section_type' => 'checklist',
                'icon'         => 'feather-list',
                'sort_order'   => 160,
                'default_data' => [
                    'items' => [
                        ['label' => 'Approved sample available on factory floor', 'result' => null, 'remarks' => ''],
                        ['label' => 'Tech pack / spec sheet issued to factory',   'result' => null, 'remarks' => ''],
                        ['label' => 'All trims and accessories received',         'result' => null, 'remarks' => ''],
                        ['label' => 'Fabric received and bulk approved',          'result' => null, 'remarks' => ''],
                        ['label' => 'Pilot run completed and reviewed',           'result' => null, 'remarks' => ''],
                        ['label' => 'Workers briefed on quality requirements',    'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Factory Readiness',
                'slug'         => 'factory_readiness',
                'description'  => 'Assess factory capacity, manpower, equipment, and production schedule.',
                'section_type' => 'checklist',
                'icon'         => 'feather-home',
                'sort_order'   => 170,
                'default_data' => [
                    'factory_name'         => '',
                    'production_start_date'=> '',
                    'expected_completion'  => '',
                    'items' => [
                        ['label' => 'Production capacity confirmed',          'result' => null, 'remarks' => ''],
                        ['label' => 'Machinery / equipment operational',      'result' => null, 'remarks' => ''],
                        ['label' => 'Required manpower available',            'result' => null, 'remarks' => ''],
                        ['label' => 'Raw materials sourced and available',    'result' => null, 'remarks' => ''],
                        ['label' => 'Production plan on schedule',            'result' => null, 'remarks' => ''],
                        ['label' => 'Quality control team assigned',          'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Raw Material Check',
                'slug'         => 'raw_material_check',
                'description'  => 'Inspect raw materials (fabric, trims, accessories) against specifications.',
                'section_type' => 'checklist',
                'icon'         => 'feather-layers',
                'sort_order'   => 180,
                'default_data' => [
                    'items' => [
                        ['label' => 'Fabric / material quantity correct',    'result' => null, 'remarks' => ''],
                        ['label' => 'Color / shade approved',                'result' => null, 'remarks' => ''],
                        ['label' => 'Fabric weight / GSM within spec',       'result' => null, 'remarks' => ''],
                        ['label' => 'Trims / accessories approved',          'result' => null, 'remarks' => ''],
                        ['label' => 'No defective / damaged materials',      'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Corrective Action Plan',
                'slug'         => 'corrective_action',
                'description'  => 'Document corrective actions required to address non-conformances.',
                'section_type' => 'review',
                'icon'         => 'feather-tool',
                'sort_order'   => 190,
                'default_data' => [
                    'items' => [
                        [
                            'defect_description' => '',
                            'root_cause'         => '',
                            'corrective_action'  => '',
                            'responsible_party'  => '',
                            'target_date'        => '',
                            'status'             => 'Open',
                        ],
                    ],
                ],
            ],
            [
                'name'         => 'Final Review & Approval',
                'slug'         => 'final_review',
                'description'  => 'Inspector final verdict, overall QC remarks, and approval sign-off.',
                'section_type' => 'review',
                'icon'         => 'feather-check-circle',
                'sort_order'   => 200,
                'default_data' => [
                    'overall_verdict'   => null,
                    'qc_remarks'        => '',
                    'action_required'   => '',
                    'follow_up_date'    => '',
                    'inspector_name'    => '',
                ],
            ],
            [
                'name'         => 'Barcode Testing',
                'slug'         => 'barcode_testing',
                'description'  => 'Verify barcode readability and functionality. Records functional / non-functional / partial scan results with photos.',
                'section_type' => 'checklist',
                'icon'         => 'feather-grid',
                'sort_order'   => 210,
                'default_data' => [
                    'barcode_status' => 'functional',
                    'remarks'        => '',
                ],
            ],
            [
                'name'         => 'Protector Evaluation',
                'slug'         => 'protector_evaluation',
                'description'  => 'Evaluate protective gear (jackets, armor padding) for impact strength, flexibility, and comfort.',
                'section_type' => 'checklist',
                'icon'         => 'feather-shield',
                'sort_order'   => 220,
                'default_data' => [
                    'evaluation_result' => 'pending',
                    'impact_notes'      => '',
                    'flexibility_notes' => '',
                ],
            ],
        ];

        foreach ($sections as $sec) {
            InspectionSection::updateOrCreate(
                ['slug' => $sec['slug']],
                $sec
            );
        }
    }
}
