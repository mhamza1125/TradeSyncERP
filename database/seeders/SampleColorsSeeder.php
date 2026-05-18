<?php

namespace Database\Seeders;

use App\Models\SampleColor;
use Illuminate\Database\Seeder;

class SampleColorsSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            'Black', 'White', 'Navy', 'Red', 'Blue', 'Green', 'Yellow',
            'Orange', 'Brown', 'Grey', 'Beige', 'Khaki', 'Maroon', 'Pink',
        ];

        foreach ($colors as $color) {
            SampleColor::firstOrCreate(['name' => $color]);
        }
    }
}
