<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QC proposal b1: record where the AQL found counts came from. Additive: new column whose
 * default ('manual') is true for every existing row (they were all typed in).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_run_aql', function (Blueprint $table) {
            $table->enum('counts_source', ['manual', 'defect_rows'])->default('manual')->after('found_minor');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_run_aql', function (Blueprint $table) {
            $table->dropColumn('counts_source');
        });
    }
};
