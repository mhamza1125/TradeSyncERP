<?php

namespace Database\Seeders;

use App\Models\SampleSize;
use Illuminate\Database\Seeder;

class SampleSizesSeeder extends Seeder
{
    public function run(): void
    {
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '28', '30', '32', '34', '36', '38', '40', '42'];

        foreach ($sizes as $size) {
            SampleSize::firstOrCreate(['name' => $size]);
        }
    }
}
