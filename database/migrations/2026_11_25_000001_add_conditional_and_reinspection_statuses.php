<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Audit F-04 / owner assumption A3: Conditional and Re-Inspection are their own statuses
 * and never roll up to Pass. Additive only: widens two enums, touches no rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE inspections MODIFY overall_status ENUM('Pass', 'Fail', 'Pending', 'N/A', 'Conditional', 'Re-Inspection') NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE inspection_runs MODIFY verdict ENUM('Pending', 'Pass', 'Fail', 'Conditional', 'Re-Inspection') NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Narrowing would destroy data, so it refuses while any row uses a new value
     * (rows are never rewritten by this migration — fix them deliberately first).
     */
    public function down(): void
    {
        $inUse = DB::table('inspections')->whereIn('overall_status', ['Conditional', 'Re-Inspection'])->count()
            + DB::table('inspection_runs')->where('verdict', 'Re-Inspection')->count();

        if ($inUse > 0) {
            throw new RuntimeException("Cannot roll back: {$inUse} row(s) use Conditional/Re-Inspection.");
        }

        DB::statement("ALTER TABLE inspection_runs MODIFY verdict ENUM('Pending', 'Pass', 'Fail', 'Conditional') NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE inspections MODIFY overall_status ENUM('Pass', 'Fail', 'Pending', 'N/A') NOT NULL DEFAULT 'Pending'");
    }
};
