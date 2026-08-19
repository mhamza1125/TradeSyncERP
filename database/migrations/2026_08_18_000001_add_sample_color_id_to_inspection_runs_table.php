<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the color-variant reference to inspection_runs.
     *
     * A run already answers "which style" via sample_id. This adds "which
     * color" via a nullable FK to sample_colors — nullable and NOT backfilled,
     * since there is no reliable way to infer which color a pre-existing run
     * was actually inspecting (the create-run form never captured it before
     * this change).
     */
    public function up(): void
    {
        Schema::table('inspection_runs', function (Blueprint $table) {
            $table->foreignId('sample_color_id')
                ->nullable()
                ->after('sample_id')
                ->constrained('sample_colors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_runs', function (Blueprint $table) {
            $table->dropForeign(['sample_color_id']);
            $table->dropColumn('sample_color_id');
        });
    }
};
