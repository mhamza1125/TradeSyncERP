<?php

namespace Database\Seeders;

use App\Models\DefectCategory;
use Illuminate\Database\Seeder;

class DefectCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Critical',
                'code'        => 'CR',
                'description' => 'Defects that render the product unsafe, illegal, or completely unusable. Zero tolerance.',
                'color'       => 'danger',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Major',
                'code'        => 'MA',
                'description' => 'Defects likely to result in product failure or rejection by end customer. Affects function or appearance significantly.',
                'color'       => 'warning',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Minor',
                'code'        => 'MI',
                'description' => 'Defects unlikely to reduce product usability but deviates from specifications. Acceptable at low quantities.',
                'color'       => 'info',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Functional',
                'code'        => 'FN',
                'description' => 'Defects affecting a specific function or feature. May or may not impact end use.',
                'color'       => 'secondary',
                'sort_order'  => 4,
            ],
        ];

        foreach ($categories as $cat) {
            DefectCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
