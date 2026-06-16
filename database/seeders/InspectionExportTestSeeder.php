<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Customer;
use App\Models\Defect;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunAql;
use App\Models\InspectionRunSection;
use App\Models\InspectionSection;
use App\Models\InspectionType;
use App\Models\InspectionTypeSectionDefault;
use App\Models\Sample;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Populates rich, image-backed inspection data for PDF export testing.
 *
 * Usage:
 *   php artisan db:seed --class=InspectionExportTestSeeder
 *
 * After seeding, open:
 *   http://127.0.0.1:8000/inspections/1/edit
 *   (adjust the ID to the first inspection created by this seeder)
 */
class InspectionExportTestSeeder extends Seeder
{
    /** Banner images available for test attachments. */
    private array $bannerPaths;

    /** Counter for rotating through banners. */
    private int $bannerIdx = 0;

    public function run(): void
    {
        $this->bannerPaths = $this->resolveBannerPaths();

        if (empty($this->bannerPaths)) {
            $this->command->warn('No banner images found — attachments will be skipped.');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->clearPreviousSeederData();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $customer  = Customer::first() ?? $this->command->error('Run TestDataSeeder first (no customers found).');
        $supplier  = Supplier::first() ?? null;
        $category  = \App\Models\ProductCategory::first();
        $inspector = Employee::where('department', 'Quality Control')->first() ?? Employee::first();
        $user      = User::first();
        $defects   = Defect::where('status', true)->get();

        if (!$customer) {
            return;
        }

        // ── Sample 1: Leather Motorcycle Jacket ────────────────────────────
        $sample1 = Sample::create([
            'sample_code'      => 'SMP-PDF-001',
            'product_name'     => 'Premium Leather Motorcycle Jacket',
            'article'          => 'ART-JACKET-2026',
            'sample_reference' => 'REF-MJ-001',
            'category_id'      => $category?->id,
            'customer_id'      => $customer->id,
            'supplier_id'      => $supplier?->id,
            'receive_date'     => now()->subDays(20)->toDateString(),
            'status'           => 'Received',
            'priority_level'   => 'High',
            'alert_days'       => 30,
            'remarks'          => 'Export test sample — leather jacket for Final AQL inspection.',
        ]);

        // ── Sample 2: Textile Cargo Pants ──────────────────────────────────
        $sample2 = Sample::create([
            'sample_code'      => 'SMP-PDF-002',
            'product_name'     => 'Cargo Pants — Multi-Pocket',
            'article'          => 'ART-CARGO-2026',
            'sample_reference' => 'REF-CP-001',
            'category_id'      => $category?->id,
            'customer_id'      => $customer->id,
            'supplier_id'      => $supplier?->id,
            'receive_date'     => now()->subDays(15)->toDateString(),
            'status'           => 'Received',
            'priority_level'   => 'Medium',
            'alert_days'       => 30,
            'remarks'          => 'Export test sample — cargo pants for SMS inspection.',
        ]);

        // ── Sample 3: Safety Helmet ────────────────────────────────────────
        $sample3 = Sample::create([
            'sample_code'      => 'SMP-PDF-003',
            'product_name'     => 'CE-Certified Safety Helmet',
            'article'          => 'ART-HELM-2026',
            'sample_reference' => 'REF-SH-001',
            'category_id'      => $category?->id,
            'customer_id'      => $customer->id,
            'supplier_id'      => $supplier?->id,
            'receive_date'     => now()->subDays(10)->toDateString(),
            'status'           => 'Received',
            'priority_level'   => 'High',
            'alert_days'       => 30,
            'remarks'          => 'Export test sample — helmet for CLI inspection.',
        ]);

        // ════════════════════════════════════════════════════════════════════
        // INSPECTION 1 — Final AQL (most complete — 2 runs)
        // ════════════════════════════════════════════════════════════════════
        $finalAqlType = InspectionType::where('name', 'like', 'Final%AQL%')->first()
            ?? InspectionType::first();

        $inspection1 = Inspection::create([
            'report_number'      => 'INS-PDF-2026-001',
            'inspection_type_id' => $finalAqlType?->id,
            'inspection_date'    => now()->subDays(5)->toDateString(),
            'overall_status'     => 'Pass',
            'remarks'            => 'Final AQL inspection for PDF export testing. Includes full data for all section types.',
        ]);

        if ($inspector) {
            $inspection1->inspectors()->attach($inspector->id);
        }

        // Run 1 — PASS
        $run1 = $this->createRun($inspection1, $sample1, 1, 'Pass', $user, $defects, $finalAqlType);

        // Run 2 — FAIL (re-inspection scenario)
        $run2 = $this->createRun($inspection1, $sample2, 2, 'Fail', $user, $defects, $finalAqlType);

        // ════════════════════════════════════════════════════════════════════
        // INSPECTION 2 — SMS (Sample Inspection)
        // ════════════════════════════════════════════════════════════════════
        $smsType = InspectionType::where('name', 'like', 'SMS%')->first()
            ?? InspectionType::first();

        $inspection2 = Inspection::create([
            'report_number'      => 'INS-PDF-2026-002',
            'inspection_type_id' => $smsType?->id,
            'inspection_date'    => now()->subDays(3)->toDateString(),
            'overall_status'     => 'Pass',
            'remarks'            => 'SMS inspection for PDF export testing — cargo pants sample.',
        ]);

        if ($inspector) {
            $inspection2->inspectors()->attach($inspector->id);
        }

        $run3 = $this->createRun($inspection2, $sample2, 1, 'Pass', $user, $defects, $smsType);

        // ════════════════════════════════════════════════════════════════════
        // INSPECTION 3 — Container Loading (CLI)
        // ════════════════════════════════════════════════════════════════════
        $cliType = InspectionType::where('name', 'like', '%Container%')->first()
            ?? InspectionType::where('name', 'like', '%CLI%')->first()
            ?? InspectionType::first();

        $inspection3 = Inspection::create([
            'report_number'      => 'INS-PDF-2026-003',
            'inspection_type_id' => $cliType?->id,
            'inspection_date'    => now()->subDays(1)->toDateString(),
            'overall_status'     => 'Pass',
            'remarks'            => 'CLI inspection for PDF export testing — container loading.',
        ]);

        if ($inspector) {
            $inspection3->inspectors()->attach($inspector->id);
        }

        $run4 = $this->createRun($inspection3, $sample3, 1, 'Pass', $user, $defects, $cliType);

        $this->command->info('InspectionExportTestSeeder complete.');
        $this->command->info('  Inspection 1 (Final AQL, 2 runs): ID = ' . $inspection1->id);
        $this->command->info('  Inspection 2 (SMS, 1 run):         ID = ' . $inspection2->id);
        $this->command->info('  Inspection 3 (CLI, 1 run):         ID = ' . $inspection3->id);
        $this->command->info('Visit: http://127.0.0.1:8000/inspections/' . $inspection1->id . '/edit');
    }

    // ── Create a single inspection run with all sections populated ────────────

    private function createRun(
        Inspection $inspection,
        Sample     $sample,
        int        $runNumber,
        string     $verdict,
        ?User      $user,
        $defects,
        ?InspectionType $type
    ): InspectionRun {

        $run = InspectionRun::create([
            'inspection_id' => $inspection->id,
            'sample_id'     => $sample->id,
            'run_number'    => $runNumber,
            'verdict'       => $verdict,
            'remarks'       => "Run {$runNumber} of inspection {$inspection->report_number}. Test data for PDF export.",
            'started_at'    => now()->subHours(8),
            'completed_at'  => now()->subHours(1),
        ]);

        $this->resolveSectionsForRun($run, $sample, $type);

        $run->load('runSections.section');

        foreach ($run->runSections as $rs) {
            if (!$rs->section) {
                continue;
            }

            $this->populateSection($rs, $run, $inspection, $verdict, $defects, $user);
        }

        return $run;
    }

    // ── Resolve and create run sections (mirrors controller logic) ────────────

    private function resolveSectionsForRun(InspectionRun $run, Sample $sample, ?InspectionType $type): void
    {
        if (!$type) {
            return;
        }

        $defaults = InspectionTypeSectionDefault::with('section')
            ->whereHas('section', fn($q) => $q->where('is_active', true))
            ->where('inspection_type_id', $type->id)
            ->where(function ($q) use ($sample) {
                $q->whereNull('category_id');
                if ($sample->category_id) {
                    $q->orWhere('category_id', $sample->category_id);
                }
            })
            ->orderBy('sort_order')
            ->get();

        foreach ($defaults as $i => $default) {
            if (!$default->section) {
                continue;
            }

            InspectionRunSection::create([
                'inspection_run_id'     => $run->id,
                'inspection_section_id' => $default->inspection_section_id,
                'sort_order'            => ($i + 1) * 10,
                'data'                  => $default->section->default_data,
                'status'                => 'pending',
            ]);
        }

        // Ensure final_review is last
        $hasFinalReview = $run->runSections()
            ->whereHas('section', fn($q) => $q->where('slug', 'final_review'))
            ->exists();

        if (!$hasFinalReview) {
            $finalReviewSection = InspectionSection::where('slug', 'final_review')
                ->where('is_active', true)
                ->first();

            if ($finalReviewSection) {
                InspectionRunSection::create([
                    'inspection_run_id'     => $run->id,
                    'inspection_section_id' => $finalReviewSection->id,
                    'sort_order'            => 9999,
                    'data'                  => $finalReviewSection->default_data,
                    'status'                => 'pending',
                ]);
            }
        }

        // Create AQL record if section is included
        $hasAql = $defaults->contains(fn($d) => $d->section?->slug === 'aql_sampling');
        if ($hasAql) {
            InspectionRunAql::create([
                'inspection_run_id' => $run->id,
                'aql_major'         => 2.5,
                'aql_minor'         => 4.0,
                'aql_critical'      => 0.065,
                'inspection_level'  => 'II',
            ]);
        }

        $run->refresh();
    }

    // ── Populate section data based on slug / type ────────────────────────────

    private function populateSection(
        InspectionRunSection $rs,
        InspectionRun        $run,
        Inspection           $inspection,
        string               $verdict,
        $defects,
        ?User                $user
    ): void {
        $sec  = $rs->section;
        $slug = $sec->slug;
        $type = $sec->section_type;

        switch (true) {

            // ─ General Information ─────────────────────────────────────────
            case $slug === 'general_information':
                $rs->update([
                    'status' => 'complete',
                    'data'   => [
                        'buyer_name'          => 'Allied Textiles Ltd',
                        'factory_name'        => 'ATROX Leather Works — Sialkot, Pakistan',
                        'po_number'           => 'PO-2026-' . str_pad($inspection->id, 4, '0', STR_PAD_LEFT),
                        'style_article_no'    => $run->sample?->article ?? 'ART-2026-001',
                        'product_description' => $run->sample?->product_name ?? 'Leather Motorcycle Jacket',
                        'order_quantity'      => 5000,
                        'inspection_date'     => $inspection->inspection_date?->toDateString() ?? now()->toDateString(),
                        'inspector_name'      => 'RIZWAN ALI',
                        'inspection_location' => 'Factory Floor — Main Production Hall, Sialkot, Pakistan',
                    ],
                ]);
                break;

            // ─ Product Screening (images) ──────────────────────────────────
            case $slug === 'product_screening':
                $rs->update([
                    'status' => 'complete',
                    'notes'  => 'Product samples show consistent colour and stitching. Packaging conforms to approved spec.',
                    'data'   => ['notes' => 'Product samples visually conform to approved specification.'],
                ]);
                $this->attachImages($rs, $user, null, 4);
                break;

            // ─ Defect Recording ────────────────────────────────────────────
            case $slug === 'defect_recording':
                $this->populateDefects($rs, $defects, $verdict, $user);
                break;

            // ─ AQL Sampling ────────────────────────────────────────────────
            case $slug === 'aql_sampling':
                $this->populateAql($run, $verdict);
                $rs->update(['status' => 'complete']);
                break;

            // ─ Carton Verification (consolidated: counts + qty per carton) ─
            case $slug === 'carton_verification':
                $rs->update([
                    'status' => 'complete',
                    'data'   => [
                        'total_qty_ordered'     => 5000,
                        'total_qty_loaded'      => 5000,
                        'total_cartons_ordered' => 250,
                        'total_cartons_loaded'  => 250,
                        'items'                 => [
                            ['label' => 'Carton count matches packing list',     'result' => 'Pass', 'remarks' => 'Verified 250 cartons.'],
                            ['label' => 'Carton numbering sequential / correct', 'result' => 'Pass', 'remarks' => ''],
                            ['label' => 'Quantity per carton correct',           'result' => 'Pass', 'remarks' => '20 pcs / carton.'],
                            ['label' => 'Carton condition — no damage',          'result' => 'Pass', 'remarks' => 'All cartons in good condition.'],
                        ],
                    ],
                ]);
                $this->attachImages($rs, $user, null, 2);
                break;

            // ─ Carton Dimensions & Weight ──────────────────────────────────
            case $slug === 'carton_dimensions_weight':
                $rs->update([
                    'status' => 'complete',
                    'data'   => [
                        'dim_unit'    => 'cm',
                        'weight_unit' => 'kg',
                        'cartons'     => [
                            ['carton_type' => 'Master Carton',  'length' => 62, 'width' => 42, 'height' => 38, 'gross_weight' => 14.5, 'net_weight' => 13.2, 'remarks' => ''],
                            ['carton_type' => 'Inner Carton',   'length' => 32, 'width' => 22, 'height' => 19, 'gross_weight' => 7.2,  'net_weight' => 6.5,  'remarks' => $verdict === 'Fail' ? 'Weight slightly over spec.' : ''],
                        ],
                    ],
                ]);
                $this->attachImages($rs, $user, 'carton_dim_0', 1);
                break;

            // ─ Container Details ───────────────────────────────────────────
            case $type === 'container':
                $rs->update([
                    'status' => 'complete',
                    'notes'  => 'Container inspected before loading. Clean and dry interior.',
                    'data'   => [
                        'container_number'         => 'ABCD' . rand(1000000, 9999999),
                        'container_type'           => "40'HC",
                        'seal_number'              => 'SEAL-' . rand(100000, 999999),
                        'loading_date'             => now()->subDays(1)->toDateString(),
                        'loading_start_time'       => '08:00',
                        'loading_end_time'         => '16:30',
                        'loading_port'             => 'Karachi, Pakistan',
                        'discharge_port'           => 'Rotterdam, Netherlands',
                        'total_cartons_loaded'     => 250,
                        'total_qty_loaded'         => 5000,
                        'container_condition'      => 'Good — No damage, rust, or odour.',
                        'container_condition_note' => '',
                        'notes'                    => 'Container pre-inspected. No issues found.',
                    ],
                ]);
                $this->attachImages($rs, $user, null, 3);
                break;

            // ─ Loading Schedule ─────────────────────────────────────────────
            case $slug === 'loading_schedule_and_timing':
                $rs->update([
                    'status' => 'complete',
                    'data'   => [
                        'scheduled_date'       => now()->subDays(1)->toDateString(),
                        'actual_date'          => now()->subDays(1)->toDateString(),
                        'scheduled_start_time' => '08:00',
                        'actual_start_time'    => '08:15',
                        'scheduled_end_time'   => '17:00',
                        'actual_end_time'      => '16:30',
                        'remarks'              => 'Loading completed on schedule without incident.',
                    ],
                ]);
                break;

            // ─ Overall Carton Condition ────────────────────────────────────
            case $slug === 'overall_carton_condition':
                $rs->update([
                    'status' => 'complete',
                    'data'   => [
                        'overall_condition' => $verdict === 'Fail' ? 'Fail' : 'Good',
                        'remarks'           => $verdict === 'Fail'
                            ? 'Some cartons showed minor corner damage.'
                            : 'All cartons in good condition. No damage or moisture issues.',
                    ],
                ]);
                $this->attachImages($rs, $user, null, 2);
                break;

            // ─ Factory Readiness ───────────────────────────────────────────
            case $slug === 'factory_readiness':
                $this->populateChecklist($rs, $verdict);
                break;

            // ─ Final Review ────────────────────────────────────────────────
            case $slug === 'final_review':
                $verdictLabel = match($verdict) {
                    'Pass'        => 'Pass',
                    'Fail'        => 'Fail',
                    'Conditional' => 'Conditional Pass',
                    default       => 'Pass',
                };
                $rs->update([
                    'status' => 'complete',
                    'data'   => [
                        'overall_verdict' => $verdictLabel,
                        'inspector_name'  => 'RIZWAN ALI',
                        'follow_up_date'  => $verdict === 'Fail' ? now()->addDays(7)->toDateString() : null,
                        'notes'           => $verdict === 'Fail'
                            ? 'Re-inspection required within 7 days. Supplier to address defects listed in defect recording section.'
                            : 'Inspection completed satisfactorily. All sections within acceptable limits. Product approved for shipment.',
                    ],
                ]);
                break;

            // ─ Barcode Testing ──────────────────────────────────────────────
            case $slug === 'barcode_testing':
                $rs->update([
                    'status' => 'complete',
                    'notes'  => 'All barcodes scan correctly. EAN-13 format verified.',
                    'data'   => ['items' => [
                        ['label' => 'EAN/UPC barcode present',     'result' => 'Pass', 'remarks' => 'EAN-13 verified.'],
                        ['label' => 'Barcode scans correctly',      'result' => 'Pass', 'remarks' => 'Tested on 10 units.'],
                        ['label' => 'Barcode positioned correctly', 'result' => 'Pass', 'remarks' => ''],
                        ['label' => 'Data matches purchase order',  'result' => 'Pass', 'remarks' => ''],
                    ]],
                ]);
                break;

            // ─ Variations ──────────────────────────────────────────────────
            case str_starts_with($slug, 'variations_'):
                $rs->update([
                    'status' => 'complete',
                    'notes'  => 'Variations within approved tolerance range.',
                    'data'   => [
                        'items' => [
                            ['label' => 'Colour variation within tolerance', 'result' => 'Pass', 'remarks' => 'Delta E < 1.0'],
                            ['label' => 'Trim / accessory match',            'result' => 'Pass', 'remarks' => ''],
                            ['label' => 'Logo / branding correct',           'result' => 'Pass', 'remarks' => ''],
                        ],
                    ],
                ]);
                $this->attachImages($rs, $user, null, 2);
                break;

            // ─ Overall Article Result ──────────────────────────────────────
            case $slug === 'overall_article_result':
                $rs->update([
                    'status' => 'complete',
                    'data'   => [
                        'result'  => $verdict,
                        'remarks' => $verdict === 'Pass' ? 'Article meets all quality requirements.' : 'Article has critical defects — see defect recording section.',
                    ],
                ]);
                break;

            // ─ Article Results Table ────────────────────────────────────────
            case $slug === 'article_results_table':
                $rs->update([
                    'status' => 'complete',
                    'data'   => ['items' => [
                        ['label' => 'Construction & Workmanship', 'result' => 'Pass',                              'remarks' => 'Stitching uniform and secure.'],
                        ['label' => 'Colour & Appearance',        'result' => 'Pass',                              'remarks' => 'Matches approved sample.'],
                        ['label' => 'Labels & Tags',              'result' => $verdict === 'Fail' ? 'Fail' : 'Pass','remarks' => $verdict === 'Fail' ? 'Size label missing on 2 units.' : ''],
                        ['label' => 'Packaging',                  'result' => 'Pass',                              'remarks' => ''],
                        ['label' => 'Dimensions & Measurements',  'result' => 'Pass',                              'remarks' => 'Within ±2% tolerance.'],
                        ['label' => 'Functional Tests',           'result' => 'Pass',                              'remarks' => 'Zips, snaps, and closures all functional.'],
                    ]],
                ]);
                break;

            // ─ Production Status ───────────────────────────────────────────
            case $slug === 'production_status':
                $rs->update([
                    'status' => 'complete',
                    'data'   => [
                        'selections' => [
                            ['stage' => 'AT CUTTING STAGE',    'percentage' => 100, 'quantity' => '5200', 'comment' => 'Cutting fully completed.'],
                            ['stage' => 'AT STITCHING STAGE',  'percentage' => 92,  'quantity' => '4800', 'comment' => 'Stitching in progress, on schedule.'],
                            ['stage' => 'AT PACKING STAGE',    'percentage' => 85,  'quantity' => '5000', 'comment' => $verdict === 'Fail' ? 'Packing delayed — rework required.' : 'Packing underway, no issues.'],
                        ],
                        'notes' => 'Production on schedule. No delays reported.',
                    ],
                ]);
                $this->attachImages($rs, $user, 'ps_at_cutting_stage', 1);
                break;

            // ─ Inner Conditions / Seal Verification / generic verification ──
            case $type === 'verification':
                $rs->update([
                    'status' => 'complete',
                    'data'   => ['items' => [
                        ['label' => 'Cleanliness inside container',      'result' => 'Pass', 'remarks' => 'Clean and dry.'],
                        ['label' => 'No signs of pest infestation',      'result' => 'Pass', 'remarks' => ''],
                        ['label' => 'No structural damage to container', 'result' => 'Pass', 'remarks' => ''],
                        ['label' => 'Dunnage bags in place',             'result' => 'Pass', 'remarks' => ''],
                    ]],
                ]);
                $this->attachImages($rs, $user, null, 2);
                break;

            // ─ Order Qty vs Packing List ────────────────────────────────────
            case $slug === 'order_quantity_vs_packing_list':
                $rs->update([
                    'status' => 'complete',
                    'data'   => [
                        'order_quantity'        => 5000,
                        'packing_list_quantity' => 5000,
                        'items' => [
                            ['label' => 'Packing list quantity matches order quantity',  'result' => 'Pass', 'remarks' => '5,000 pcs confirmed.'],
                            ['label' => 'Packing list matches actual cartons presented', 'result' => 'Pass', 'remarks' => '250 cartons confirmed.'],
                        ],
                    ],
                ]);
                break;

            // ─ Conclusion / Finish ─────────────────────────────────────────
            case $type === 'conclusion':
            case $type === 'finish':
            case $slug === 'finish_inspection':
            case $slug === 'inspection_conclusion':
                $rs->update([
                    'status' => 'complete',
                    'notes'  => $verdict === 'Pass'
                        ? 'Inspection completed. All sections reviewed and approved. Goods cleared for shipment.'
                        : 'Inspection completed with findings. Re-inspection scheduled.',
                    'data'   => [
                        'conclusion' => $verdict === 'Pass' ? 'PASS — Goods cleared for shipment.' : 'FAIL — Corrective actions required.',
                        'remarks'    => 'Inspector: RIZWAN ALI | Date: ' . now()->format('d M Y'),
                    ],
                ]);
                break;

            // ─ Checkpoint sections (packing_check_si, marking_check_si, etc.) ─
            case $type === 'checkpoint':
                $this->populateCheckpoint($rs, $verdict, $user);
                break;

            // ─ Generic checklist fallback ──────────────────────────────────
            default:
                if (!empty($sec->default_data['items'])) {
                    $this->populateChecklist($rs, $verdict);
                } else {
                    $rs->update(['status' => 'complete']);
                }
                break;
        }
    }

    // ── Populate defect recording section ──────────────────────────────────────

    private function populateDefects(InspectionRunSection $rs, $defects, string $verdict, ?User $user): void
    {
        if ($defects->isEmpty()) {
            $rs->update(['status' => 'complete', 'data' => ['selections' => []]]);
            return;
        }

        $defectList = $defects->shuffle()->take($verdict === 'Fail' ? 4 : 2);
        $selections = [];

        foreach ($defectList as $defect) {
            $selections[] = [
                'defect_id'   => $defect->id,
                'defect_name' => $defect->defect_name,
                'severity'    => $defect->severity,
                'quantity'    => rand(1, 5),
                'comment'     => $this->sampleComment($defect->severity),
                'selected'    => 1,
            ];

            $this->attachImages($rs, $user, 'defect_' . $defect->id, rand(1, 2));
        }

        $rs->update([
            'status' => 'complete',
            'data'   => ['selections' => $selections],
        ]);
    }

    // ── Populate AQL record ───────────────────────────────────────────────────

    private function populateAql(InspectionRun $run, string $verdict): void
    {
        $aql = $run->aql;
        if (!$aql) {
            return;
        }

        $aql->update([
            'lot_size'         => 5000,
            'inspection_level' => 'II',
            'code_letter'      => 'N',
            'sample_size'      => 200,
            'aql_critical'     => 0.065,
            'aql_major'        => 2.5,
            'aql_minor'        => 4.0,
            'ac_critical'      => 0,
            're_critical'      => 1,
            'ac_major'         => 10,
            're_major'         => 11,
            'ac_minor'         => 14,
            're_minor'         => 15,
            'found_critical'   => $verdict === 'Fail' ? 2 : 0,
            'found_major'      => $verdict === 'Fail' ? 4 : 1,
            'found_minor'      => rand(2, 8),
            'verdict'          => $verdict,
            'notes'            => "Lot of 5000 pcs. Sample size 200 pcs inspected per ISO 2859-1 Level II.",
        ]);
    }

    // ── Populate checklist items ──────────────────────────────────────────────

    private function populateChecklist(InspectionRunSection $rs, string $verdict): void
    {
        $data  = $rs->data ?? $rs->section->default_data ?? [];
        $items = $data['items'] ?? [];

        if (empty($items)) {
            $rs->update(['status' => 'complete']);
            return;
        }

        $failIdx = $verdict === 'Fail' ? rand(0, count($items) - 1) : -1;

        foreach ($items as $i => &$item) {
            $result          = ($i === $failIdx) ? 'Fail' : 'Pass';
            $item['result']  = $result;
            $item['remarks'] = $result === 'Fail' ? 'Non-conformance found — action required.' : '';
        }
        unset($item);

        $data['items'] = $items;

        $rs->update([
            'status' => 'complete',
            'data'   => $data,
        ]);
    }

    // ── Populate checkpoint section (all SI checkpoint types) ─────────────────

    private function populateCheckpoint(InspectionRunSection $rs, string $verdict, ?User $user): void
    {
        $taskDefs = $rs->section->default_data['tasks'] ?? [];

        if (empty($taskDefs)) {
            $rs->update(['status' => 'complete']);
            return;
        }

        $tasks   = [];
        $failIdx = $verdict === 'Fail' ? 0 : -1;

        foreach ($taskDefs as $i => $td) {
            $key    = $td['key'] ?? "task_{$i}";
            $result = ($i === $failIdx) ? 'Fail' : 'Pass';
            $tasks[$key] = [
                'selected' => $result,
                'comments' => $result === 'Fail' ? 'Non-conformance noted.' : '',
            ];

            $this->attachImages($rs, $user, $key, 1);
        }

        $rs->update([
            'status' => 'complete',
            'data'   => array_merge($rs->data ?? [], ['tasks' => $tasks]),
        ]);
    }

    // ── Copy a banner image to storage and create an Attachment record ─────────

    private function attachImages(
        InspectionRunSection $rs,
        ?User                $user,
        ?string              $taskKey,
        int                  $count = 1
    ): void {
        if (empty($this->bannerPaths)) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $srcPath  = $this->nextBanner();
            $fileName = basename($srcPath);
            $destDir  = "inspection-sections/{$rs->id}";
            $destFile = $destDir . '/' . $taskKey . '_' . $fileName;

            Storage::disk('public')->makeDirectory($destDir);

            if (!Storage::disk('public')->exists($destFile)) {
                Storage::disk('public')->put($destFile, file_get_contents($srcPath));
            }

            Attachment::create([
                'attachable_type' => InspectionRunSection::class,
                'attachable_id'   => $rs->id,
                'title'           => pathinfo($fileName, PATHINFO_FILENAME),
                'file_name'       => $fileName,
                'file_path'       => $destFile,
                'mime_type'       => mime_content_type($srcPath) ?: 'image/jpeg',
                'file_size'       => filesize($srcPath),
                'attachment_type' => 'gallery',
                'task_key'        => $taskKey,
                'uploaded_by'     => $user?->id,
            ]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveBannerPaths(): array
    {
        $paths = [];

        $bannersDir = public_path('assets/images/banners');
        if (is_dir($bannersDir)) {
            for ($i = 1; $i <= 6; $i++) {
                foreach (['jpg', 'png', 'jpeg'] as $ext) {
                    $path = $bannersDir . DIRECTORY_SEPARATOR . $i . '.' . $ext;
                    if (file_exists($path)) {
                        $paths[] = $path;
                    }
                }
            }
        }

        if (empty($paths)) {
            $avatarDir = public_path('assets/images/avatar');
            if (is_dir($avatarDir)) {
                for ($i = 1; $i <= 6; $i++) {
                    $path = $avatarDir . DIRECTORY_SEPARATOR . $i . '.png';
                    if (file_exists($path)) {
                        $paths[] = $path;
                    }
                }
            }
        }

        return $paths;
    }

    private function nextBanner(): string
    {
        $path = $this->bannerPaths[$this->bannerIdx % count($this->bannerPaths)];
        $this->bannerIdx++;
        return $path;
    }

    private function sampleComment(string $severity): string
    {
        return match($severity) {
            'critical'   => 'Critical defect found on multiple units. Requires immediate corrective action.',
            'major'      => 'Major defect observed. Supplier to investigate root cause and provide CAPA.',
            'functional' => 'Functional issue noted. Does not affect appearance but impacts usability.',
            default      => 'Minor defect. Supplier to correct in next production run.',
        };
    }

    // ── Clear previous seeder data ────────────────────────────────────────────

    private function clearPreviousSeederData(): void
    {
        $existingNumbers = ['INS-PDF-2026-001', 'INS-PDF-2026-002', 'INS-PDF-2026-003'];
        $inspections     = Inspection::whereIn('report_number', $existingNumbers)->get();

        foreach ($inspections as $inspection) {
            foreach ($inspection->runs as $run) {
                foreach ($run->runSections as $rs) {
                    foreach ($rs->attachments as $att) {
                        Storage::disk('public')->delete($att->file_path);
                    }
                    $rs->attachments()->delete();
                }
                $run->runSections()->delete();
                $run->aql?->delete();
                $run->delete();
            }
            $inspection->delete();
        }

        Sample::whereIn('sample_code', ['SMP-PDF-001', 'SMP-PDF-002', 'SMP-PDF-003'])->forceDelete();
    }
}
