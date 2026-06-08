<?php

namespace Database\Seeders;

use App\Models\InspectionType;
use Illuminate\Database\Seeder;

class InspectionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Sample Check P1&P2 (SMS, PPS)',          'description' => 'Initial sample check phases 1 and 2 (Salesman Sample / Pre-Production Sample)'],
            ['name' => 'Pre-Production Inspection (PPI)',         'description' => 'Inspection conducted before production begins'],
            ['name' => 'Inline Inspection (ILI/DUPRO)',           'description' => 'During production inspection / Dupro'],
            ['name' => 'Final Quality Check (AQL / 100% / % Based)', 'description' => 'Final quality check before shipment using AQL, 100%, or percentage-based sampling'],
            ['name' => 'Re-Inspection (Re-QC)',                   'description' => 'Re-inspection after previous QC failure'],
            ['name' => 'Container Loading Inspection (CLI)',       'description' => 'Inspection during container loading to verify shipment integrity'],

            // ── Independent report-format-specific types (added alongside the combined types above) ──
            ['name' => 'SMS Inspection',                          'description' => 'Specialized shipment / product verification template (SMS report format)'],
            ['name' => 'PPS Inspection',                          'description' => 'Pre-production sample (PPS) approval inspection'],
            ['name' => 'Final Inspection (100% Check)',           'description' => 'Full quantity (100%) final inspection before shipment'],
        ];

        foreach ($types as $type) {
            InspectionType::firstOrCreate(
                ['name' => $type['name']],
                ['description' => $type['description'], 'status' => true]
            );
        }
    }
}
