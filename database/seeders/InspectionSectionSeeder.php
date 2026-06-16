<?php

namespace Database\Seeders;

use App\Models\InspectionSection;
use App\Models\InspectionTypeSectionDefault;
use Illuminate\Database\Seeder;

class InspectionSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            // ── Core / shared sections ─────────────────────────────────────────────

            [
                'name'         => 'General Information',
                'slug'         => 'general_information',
                'description'  => 'Capture report header details — buyer, factory, order, style, quantity, and inspection particulars.',
                'section_type' => 'general_info',
                'icon'         => 'feather-info',
                'sort_order'   => 5,
                'default_data' => [
                    'buyer_name'          => '',
                    'factory_name'        => '',
                    'po_number'           => '',
                    'style_article_no'    => '',
                    'product_description' => '',
                    'order_quantity'      => '',
                    'inspection_date'     => '',
                    'inspection_location' => '',
                    'inspector_name'      => '',
                ],
            ],
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
                'description'  => 'Search and record defects found during inspection, with severity, comments and photo evidence.',
                'section_type' => 'defects',
                'icon'         => 'feather-alert-triangle',
                'sort_order'   => 30,
                'default_data' => [
                    'selections' => [],
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
                'name'         => 'Carton Verification',
                'slug'         => 'carton_verification',
                'description'  => 'Verify carton counts, quantity per carton, and packing details against shipping documents.',
                'section_type' => 'checklist',
                'icon'         => 'feather-package',
                'sort_order'   => 60,
                'default_data' => [
                    'total_cartons_ordered'  => '',
                    'total_cartons_loaded'   => '',
                    'total_qty_ordered'      => '',
                    'total_qty_loaded'       => '',
                    'items' => [
                        ['label' => 'Carton count matches packing list',     'result' => null, 'remarks' => ''],
                        ['label' => 'Carton numbering sequential / correct', 'result' => null, 'remarks' => ''],
                        ['label' => 'Quantity per carton correct',           'result' => null, 'remarks' => ''],
                        ['label' => 'Carton condition — no damage',          'result' => null, 'remarks' => ''],
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
                        ['label' => 'Care / wash label correct',        'result' => null, 'remarks' => ''],
                        ['label' => 'Composition label correct',        'result' => null, 'remarks' => ''],
                        ['label' => 'Country of origin correct',        'result' => null, 'remarks' => ''],
                        ['label' => 'Size label correct',               'result' => null, 'remarks' => ''],
                        ['label' => 'Barcode / EAN / UPC correct',     'result' => null, 'remarks' => ''],
                        ['label' => 'Brand label correctly positioned', 'result' => null, 'remarks' => ''],
                        ['label' => 'Price / ticketing correct',        'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Container Details',
                'slug'         => 'container_details',
                'description'  => 'Record container number, type, seal, and loading logistics.',
                'section_type' => 'container',
                'icon'         => 'feather-truck',
                'sort_order'   => 130,
                'default_data' => [
                    'container_number'         => '',
                    'container_type'           => '',
                    'seal_number'              => '',
                    'loading_date'             => '',
                    'loading_start_time'       => '',
                    'loading_end_time'         => '',
                    'loading_port'             => '',
                    'discharge_port'           => '',
                    'total_cartons_loaded'     => '',
                    'total_quantity_loaded'    => '',
                    'container_condition'      => null,
                    'container_condition_note' => '',
                    'notes'                    => '',
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
                    'seal_number'      => '',
                    'seal_intact'      => null,
                    'seal_photo_taken' => null,
                    'verified_by'      => '',
                    'notes'            => '',
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
                'name'         => 'Order Quantity vs. Packing List',
                'slug'         => 'order_quantity_vs_packing_list',
                'description'  => 'Compare ordered quantity against the packing list quantity declared for loading.',
                'section_type' => 'checklist',
                'icon'         => 'feather-clipboard',
                'sort_order'   => 151,
                'default_data' => [
                    'order_quantity'        => '',
                    'packing_list_quantity' => '',
                    'items' => [
                        ['label' => 'Packing list quantity matches order quantity',  'result' => null, 'remarks' => ''],
                        ['label' => 'Packing list matches actual cartons presented', 'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Loading Schedule & Timing',
                'slug'         => 'loading_schedule_and_timing',
                'description'  => 'Record planned vs. actual loading schedule and timing for the container.',
                'section_type' => 'checklist',
                'icon'         => 'feather-clock',
                'sort_order'   => 152,
                'default_data' => [
                    'planned_loading_date' => '',
                    'actual_loading_date'  => '',
                    'loading_start_time'   => '',
                    'loading_end_time'     => '',
                    'items' => [
                        ['label' => 'Loading started on schedule',    'result' => null, 'remarks' => ''],
                        ['label' => 'Loading completed within window', 'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Container Condition',
                'slug'         => 'inner_conditions_of_container',
                'description'  => 'Inspect the interior of the container before loading — cleanliness, dryness, odor, and structural condition.',
                'section_type' => 'checklist',
                'icon'         => 'feather-truck',
                'sort_order'   => 153,
                'default_data' => [
                    'items' => [
                        ['label' => 'Container interior clean and dry',       'result' => null, 'remarks' => ''],
                        ['label' => 'No foul odor present',                   'result' => null, 'remarks' => ''],
                        ['label' => 'No holes, leaks, or structural damage',  'result' => null, 'remarks' => ''],
                        ['label' => 'Floor and walls free of debris / pests', 'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Overall Carton Condition',
                'slug'         => 'overall_carton_condition',
                'description'  => 'Record the overall condition verdict for the loaded cartons with closing remarks.',
                'section_type' => 'review',
                'icon'         => 'feather-check-circle',
                'sort_order'   => 156,
                'default_data' => [
                    'overall_condition' => null,
                    'remarks'           => '',
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
                    'factory_name'          => '',
                    'production_start_date' => '',
                    'expected_completion'   => '',
                    'items' => [
                        ['label' => 'Production capacity confirmed',       'result' => null, 'remarks' => ''],
                        ['label' => 'Machinery / equipment operational',   'result' => null, 'remarks' => ''],
                        ['label' => 'Required manpower available',         'result' => null, 'remarks' => ''],
                        ['label' => 'Raw materials sourced and available', 'result' => null, 'remarks' => ''],
                        ['label' => 'Production plan on schedule',         'result' => null, 'remarks' => ''],
                        ['label' => 'Quality control team assigned',       'result' => null, 'remarks' => ''],
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
                        ['label' => 'Fabric / material quantity correct', 'result' => null, 'remarks' => ''],
                        ['label' => 'Color / shade approved',             'result' => null, 'remarks' => ''],
                        ['label' => 'Fabric weight / GSM within spec',   'result' => null, 'remarks' => ''],
                        ['label' => 'Trims / accessories approved',       'result' => null, 'remarks' => ''],
                        ['label' => 'No defective / damaged materials',   'result' => null, 'remarks' => ''],
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
                'name'         => 'Variations vs. Approved Sample',
                'slug'         => 'variations_sample',
                'description'  => 'Document differences found between production output and the approved reference sample.',
                'section_type' => 'checklist',
                'icon'         => 'feather-git-pull-request',
                'sort_order'   => 230,
                'default_data' => [
                    'items' => [
                        ['label' => 'Color / shade matches approved sample',   'result' => null, 'remarks' => ''],
                        ['label' => 'Style / silhouette matches sample',       'result' => null, 'remarks' => ''],
                        ['label' => 'Fabric / material matches sample',        'result' => null, 'remarks' => ''],
                        ['label' => 'Trims / accessories match sample',        'result' => null, 'remarks' => ''],
                        ['label' => 'Print / embroidery placement matches',    'result' => null, 'remarks' => ''],
                        ['label' => 'Packaging / presentation matches sample', 'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Variations vs. Tech Pack',
                'slug'         => 'variations_techpack',
                'description'  => "Document differences found between production output and the buyer's technical specification pack.",
                'section_type' => 'checklist',
                'icon'         => 'feather-file-text',
                'sort_order'   => 240,
                'default_data' => [
                    'tech_pack_reference' => '',
                    'result'              => null,
                    'notes'               => '',
                ],
            ],
            [
                'name'         => 'Carton Dimensions & Weight',
                'slug'         => 'carton_dimensions_weight',
                'description'  => "Record measured dimensions and weight for each carton type. Add multiple rows for different carton variants.",
                'section_type' => 'checklist',
                'icon'         => 'feather-box',
                'sort_order'   => 250,
                'default_data' => [
                    'dim_unit'    => 'cm',
                    'weight_unit' => 'kg',
                    'cartons'     => [
                        [
                            'carton_type'  => '',
                            'length'       => '',
                            'width'        => '',
                            'height'       => '',
                            'gross_weight' => '',
                            'net_weight'   => '',
                            'remarks'      => '',
                        ],
                    ],
                ],
            ],
            [
                'name'         => 'Article Inspection Results Table',
                'slug'         => 'article_results_table',
                'description'  => 'Per-article / per-style inspection results with quantities, verdicts, and remarks.',
                'section_type' => 'article_results',
                'icon'         => 'feather-grid',
                'sort_order'   => 260,
                'default_data' => [
                    'articles' => [
                        ['article_no' => '', 'color' => '', 'size' => '', 'qty_ordered' => '', 'qty_inspected' => '', 'result' => null, 'remarks' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Overall Article Result',
                'slug'         => 'overall_article_result',
                'description'  => 'Record the overall pass / fail verdict for the inspected article(s) with closing remarks.',
                'section_type' => 'review',
                'icon'         => 'feather-award',
                'sort_order'   => 265,
                'default_data' => [
                    'overall_result' => null,
                    'remarks'        => '',
                ],
            ],
            [
                'name'         => 'Conclusion',
                'slug'         => 'inspection_conclusion',
                'description'  => 'Summarize presented vs. unpresented styles and record a closing inspector note.',
                'section_type' => 'conclusion',
                'icon'         => 'feather-flag',
                'sort_order'   => 270,
                'default_data' => [
                    'presented_styles'   => '',
                    'unpresented_styles' => '',
                    'note'               => '',
                ],
            ],
            [
                'name'         => 'Final Review & Approval',
                'slug'         => 'final_review',
                'description'  => 'Inspector final verdict, overall QC remarks, and approval sign-off.',
                'section_type' => 'review',
                'icon'         => 'feather-check-circle',
                'sort_order'   => 9990,
                'default_data' => [
                    'overall_verdict' => null,
                    'qc_remarks'      => '',
                    'action_required' => '',
                    'follow_up_date'  => '',
                    'inspector_name'  => '',
                ],
            ],
            [
                'name'         => 'Finish Inspection',
                'slug'         => 'finish_inspection',
                'description'  => 'Final comments and close the inspection run.',
                'section_type' => 'finish',
                'icon'         => 'feather-check-circle',
                'sort_order'   => 9999,
                'default_data' => [
                    'comments' => '',
                    'finished' => false,
                ],
            ],

            // ── SI (Standard Inspection) specific sections ─────────────────────────

            [
                'name'         => 'Quantity & Sampling Count',
                'slug'         => 'quantity_sampling',
                'description'  => 'Record product quantities, packed goods, cartons, and AQL sampling level.',
                'section_type' => 'quantity_sampling',
                'icon'         => 'feather-hash',
                'sort_order'   => 10,
                'default_data' => [
                    'product_quantity'   => '',
                    'packed_goods_qty'   => '',
                    'packed_cartons_qty' => '',
                    'aql_level'          => '',
                ],
            ],
            [
                'name'         => 'Selected Cartons SI',
                'slug'         => 'selected_cartons_si',
                'description'  => 'Log all inspected cartons with size, color, and sample quantity per carton.',
                'section_type' => 'cartons',
                'icon'         => 'feather-package',
                'sort_order'   => 20,
                'default_data' => [
                    'cartons' => [
                        ['box_number' => '', 'size' => '', 'color' => '', 'qty_inspected' => ''],
                    ],
                ],
            ],
            [
                'name'         => 'Cover Photo',
                'slug'         => 'cover_photo',
                'description'  => 'Upload the main cover photo for this inspection.',
                'section_type' => 'cover_photo',
                'icon'         => 'feather-camera',
                'sort_order'   => 50,
                'default_data' => [],
            ],
            [
                'name'         => 'Files To Review',
                'slug'         => 'files_to_review',
                'description'  => 'Attach reference PDFs and documents for inspector review.',
                'section_type' => 'files_review',
                'icon'         => 'feather-file-text',
                'sort_order'   => 100,
                'default_data' => [
                    'acknowledged' => false,
                    'notes'        => '',
                ],
            ],

            // ── Checkpoint sections (converted from task_list) ─────────────────────
            // All checkpoint sections support per-task attachments and comments.

            [
                'name'         => 'Packing Check SI',
                'slug'         => 'packing_check_si',
                'description'  => 'Verify box and packaging quality against specifications.',
                'section_type' => 'checkpoint',
                'icon'         => 'feather-box',
                'sort_order'   => 30,
                'default_data' => [
                    'tasks' => [
                        [
                            'key'             => 'box_check',
                            'label'           => 'Box Checking',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                    ],
                ],
            ],
            [
                'name'         => 'Packing Check (CE) SI',
                'slug'         => 'packing_check_ce_si',
                'description'  => 'CE-specific packing and user manual verification.',
                'section_type' => 'checkpoint',
                'icon'         => 'feather-box',
                'sort_order'   => 40,
                'default_data' => [
                    'tasks' => [
                        [
                            'key'             => 'packing_check',
                            'label'           => 'Packing Check',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                        [
                            'key'             => 'ce_user_manual',
                            'label'           => 'CE User Manual Check',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                    ],
                ],
            ],
            [
                'name'         => 'Labels Check (CE) SI',
                'slug'         => 'labels_check_ce_si',
                'description'  => 'Verify label conformity against CE requirements.',
                'section_type' => 'checkpoint',
                'icon'         => 'feather-tag',
                'sort_order'   => 60,
                'default_data' => [
                    'tasks' => [
                        [
                            'key'             => 'label_conformity',
                            'label'           => 'Label Conformity',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                    ],
                ],
            ],
            [
                'name'         => 'Marking Check SI',
                'slug'         => 'marking_check_si',
                'description'  => 'Verify shipping marks and carton marking against packing list.',
                'section_type' => 'checkpoint',
                'icon'         => 'feather-edit-2',
                'sort_order'   => 80,
                'default_data' => [
                    'tasks' => [
                        [
                            'key'             => 'marking_check',
                            'label'           => 'Marking Check',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                    ],
                ],
            ],
            [
                'name'         => 'Functional Test',
                'slug'         => 'functional_test',
                'description'  => 'Functional tests for product accessories and performance characteristics.',
                'section_type' => 'checkpoint',
                'icon'         => 'feather-settings',
                'sort_order'   => 90,
                'default_data' => [
                    'tasks' => [
                        [
                            'key'             => 'zippers',
                            'label'           => 'Functionality Zippers',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                        [
                            'key'             => 'velcro',
                            'label'           => 'Velcro',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                        [
                            'key'             => 'lining',
                            'label'           => 'Lining',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                        [
                            'key'             => 'other_accessories',
                            'label'           => 'Other Accessories',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                        [
                            'key'             => 'protectors_availability',
                            'label'           => 'Protectors Availability and Direction',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                        [
                            'key'             => 'hand_pockets',
                            'label'           => 'Hand Pockets',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                    ],
                ],
            ],
            [
                'name'         => 'Measurements Check SI',
                'slug'         => 'measurements_check_si',
                'description'  => 'Verify product measurements and symmetry against specifications.',
                'section_type' => 'checkpoint',
                'icon'         => 'feather-maximize-2',
                'sort_order'   => 110,
                'default_data' => [
                    'tasks' => [
                        [
                            'key'             => 'measurement_check',
                            'label'           => 'Measurement Check',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                        [
                            'key'             => 'symmetry_check',
                            'label'           => 'Symmetry Check',
                            'options'         => ['Pass', 'Fail', 'N/A'],
                            'has_attachments' => true,
                            'has_comment'     => true,
                        ],
                    ],
                ],
            ],

            // ── Production Status (distinct structure — not a checkpoint) ───────────

            [
                'name'         => 'Production Status',
                'slug'         => 'production_status',
                'description'  => 'Track production progress by adding stages. Each stage records percentage completion, quantity output, and photo evidence.',
                'section_type' => 'production_stages',
                'icon'         => 'feather-activity',
                'sort_order'   => 140,
                'default_data' => [
                    'selections' => [],
                    'notes'      => '',
                ],
            ],
        ];

        foreach ($sections as $sec) {
            InspectionSection::updateOrCreate(
                ['slug' => $sec['slug']],
                $sec
            );
        }

        // ── Cleanup: remove deprecated sections and their type assignments ─────────
        $deprecated = [
            'sample_conformity',          // merged → variations_sample
            'marking_check',              // merged → marking_check_si (checkpoint)
            'measurement_check',          // merged → measurements_check_si (checkpoint)
            'packing_check',              // merged → packing_check_si (checkpoint)
            'protector_evaluation',       // covered by functional_test (checkpoint)
            'number_of_cartons_loaded',   // consolidated into carton_verification
            'quantity_per_carton',        // consolidated into carton_verification
            'textile_leather_functional', // renamed → functional_test
        ];

        $deprecatedIds = InspectionSection::whereIn('slug', $deprecated)->pluck('id');

        if ($deprecatedIds->isNotEmpty()) {
            InspectionTypeSectionDefault::whereIn('inspection_section_id', $deprecatedIds)->delete();
            InspectionSection::whereIn('slug', $deprecated)->delete();
        }
    }
}
