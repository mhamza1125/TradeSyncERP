<?php

namespace Database\Seeders;

use App\Models\InspectionType;
use Illuminate\Database\Seeder;

class InspectionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'SMS (Sample Inspection)',                 'description' => 'Sample inspection covering salesman / pre-production samples against tech pack and approved references'],
            ['name' => 'PPS (Pre-Production Inspection)',         'description' => 'Inspection conducted before bulk production begins — factory readiness, raw materials, and pre-production checklist'],
            ['name' => 'Inline Inspection (DUPRO)',               'description' => 'During-production inspection (Dupro) monitoring workmanship, measurements, and variations against the approved sample'],
            ['name' => 'Final Inspection (AQL / Percentage Based)', 'description' => 'Pre-shipment final quality inspection using AQL or percentage-based sampling'],
            ['name' => 'Final Inspection (100%)',                 'description' => 'Full quantity (100%) final inspection before shipment'],
            ['name' => 'Re-Inspection',                           'description' => 'Re-inspection after a previous QC failure'],
            ['name' => 'Container Loading Inspection (CLI)',      'description' => 'Inspection during container loading to verify shipment integrity'],
        ];

        foreach ($types as $type) {
            InspectionType::firstOrCreate(
                ['name' => $type['name']],
                ['description' => $type['description'], 'status' => true]
            );
        }
    }
}
