<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── inspection_runs: add lifecycle and verdict columns ───────────────────
        Schema::table('inspection_runs', function (Blueprint $table) {
            $table->unsignedTinyInteger('run_number')->default(1)->after('inspection_type_id');
            $table->enum('verdict', ['Pending', 'Pass', 'Fail', 'Conditional'])
                ->default('Pending')
                ->after('run_number');
            $table->timestamp('started_at')->nullable()->after('remarks');
            $table->timestamp('completed_at')->nullable()->after('started_at');
        });

        // ── inspection_results: add defect severity for AQL classification ───────
        Schema::table('inspection_results', function (Blueprint $table) {
            $table->enum('defect_severity', ['Critical', 'Major', 'Minor', 'Functional'])
                ->nullable()
                ->after('defect_id');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_results', function (Blueprint $table) {
            $table->dropColumn('defect_severity');
        });

        Schema::table('inspection_runs', function (Blueprint $table) {
            $table->dropColumn(['run_number', 'verdict', 'started_at', 'completed_at']);
        });
    }
};
