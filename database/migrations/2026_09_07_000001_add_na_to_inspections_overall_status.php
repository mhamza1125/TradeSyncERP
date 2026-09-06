<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE inspections MODIFY overall_status ENUM('Pass', 'Fail', 'Pending', 'N/A') NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('inspections')->where('overall_status', 'N/A')->update(['overall_status' => 'Pending']);
        DB::statement("ALTER TABLE inspections MODIFY overall_status ENUM('Pass', 'Fail', 'Pending') NOT NULL DEFAULT 'Pending'");
    }
};
