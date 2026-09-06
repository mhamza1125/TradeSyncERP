<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inspection_sections', function (Blueprint $table) {
            // mandatory   — must be completed, no Skip Section action on the run
            // optional    — does not have to be completed, but has no explicit Skip action either
            // skippable   — the run-edit page shows a "Skip Section" action (with confirmation)
            $table->enum('skip_policy', ['mandatory', 'optional', 'skippable'])
                ->default('mandatory')
                ->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_sections', function (Blueprint $table) {
            $table->dropColumn('skip_policy');
        });
    }
};
