<?php

namespace Database\Seeders;

use App\Models\Defect;
use Illuminate\Database\Seeder;

class DefectsSeeder extends Seeder
{
    public function run(): void
    {
        $defects = [
            [
                'defect_name'       => 'Arm Hole Wrinkle',
                'corrective_action' => 'Open arm hole and inner side stitching & re stitch to remove the wrinkle',
            ],
            [
                'defect_name'       => 'Belt Loop Uneven',
                'corrective_action' => 'Open one loop and then reattach both loops together with proper alignment',
            ],
            [
                'defect_name'       => 'Back Pocket Position Uneven',
                'corrective_action' => 'Open pocket stitching and reattach (restitch) at the correct position',
            ],
        ];

        foreach ($defects as $defect) {
            Defect::firstOrCreate(['defect_name' => $defect['defect_name']], $defect);
        }
    }
}
