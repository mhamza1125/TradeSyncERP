<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Discard any existing free-text values that don't match one of the
        // new enum options — the column is nullable, so they just become NULL.
        DB::table('samples')
            ->whereNotNull('sample_reference')
            ->whereNotIn('sample_reference', ['From Shipment', 'Gold Seal', 'Red Seal', 'Silver Seal'])
            ->update(['sample_reference' => null]);

        DB::statement("ALTER TABLE samples MODIFY sample_reference ENUM('From Shipment', 'Gold Seal', 'Red Seal', 'Silver Seal') NULL");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE samples MODIFY sample_reference VARCHAR(255) NULL');
    }
};
