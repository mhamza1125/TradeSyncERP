<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Inspections: remove sample_id + inspection_type_id (move to child tables) ──
        if (Schema::hasColumn('inspections', 'sample_id')) {
            Schema::table('inspections', function (Blueprint $table) {
                $table->dropForeign(['sample_id']);
                $table->dropForeign(['inspection_type_id']);
                $table->dropColumn(['sample_id', 'inspection_type_id']);
            });
        }

        // ── 2. Pivot: inspection ↔ samples ──────────────────────────────────────────────
        if (!Schema::hasTable('inspection_samples')) {
            Schema::create('inspection_samples', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
                $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['inspection_id', 'sample_id'], 'is_inspection_sample_unique');
            });
        }

        // ── 3. Pivot: inspection ↔ customer_orders ──────────────────────────────────────
        if (!Schema::hasTable('inspection_customer_orders')) {
            Schema::create('inspection_customer_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
                $table->foreignId('customer_order_id')->constrained('customer_orders')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['inspection_id', 'customer_order_id'], 'ico_inspection_order_unique');
            });
        }

        // ── 4. Inspection Runs ───────────────────────────────────────────────────────────
        if (!Schema::hasTable('inspection_runs')) {
            Schema::create('inspection_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
                $table->foreignId('inspection_type_id')
                    ->nullable()
                    ->constrained('inspection_types')
                    ->nullOnDelete();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        // ── 5. Sample Movements: link to inspection run ─────────────────────────────────
        if (!Schema::hasColumn('sample_movements', 'inspection_run_id')) {
            Schema::table('sample_movements', function (Blueprint $table) {
                $table->foreignId('inspection_run_id')
                    ->nullable()
                    ->after('sample_id')
                    ->constrained('inspection_runs')
                    ->nullOnDelete();
            });
        }

        // ── 6. Inspection Results: restructure around runs + defects ────────────────────
        if (Schema::hasColumn('inspection_results', 'inspection_id')) {
            Schema::table('inspection_results', function (Blueprint $table) {
                $table->dropForeign(['inspection_id']);
                $table->dropForeign(['sample_testing_parameter_id']);
                $table->dropColumn([
                    'inspection_id',
                    'sample_testing_parameter_id',
                    'actual_result',
                    'pass_fail',
                    'status',   // old Approve/Reject/Review enum from 2026_05_13_100010
                    'attachment',
                ]);
            });
        }

        if (!Schema::hasColumn('inspection_results', 'inspection_run_id')) {
            Schema::table('inspection_results', function (Blueprint $table) {
                $table->foreignId('inspection_run_id')
                    ->after('id')
                    ->constrained('inspection_runs')
                    ->cascadeOnDelete();
                $table->enum('status', ['Pending', 'Pass', 'Fail', 'Rejected'])->default('Pending')->after('inspection_run_id');
                $table->foreignId('defect_id')
                    ->nullable()
                    ->after('status')
                    ->constrained('defects')
                    ->nullOnDelete();
            });
        }

        // Remove status column added by old migration (now redundant — replaced above)
        // The old migration 2026_05_13_100010_add_status_to_inspection_results added a
        // separate status column; that column was dropped above along with the old columns.
    }

    public function down(): void
    {
        Schema::table('inspection_results', function (Blueprint $table) {
            $table->dropForeign(['inspection_run_id']);
            $table->dropForeign(['defect_id']);
            $table->dropColumn(['inspection_run_id', 'status', 'defect_id']);
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->foreignId('sample_testing_parameter_id')->constrained('sample_testing_parameters')->cascadeOnDelete();
            $table->string('actual_result');
            $table->enum('pass_fail', ['Pass', 'Fail']);
            $table->string('attachment')->nullable();
        });

        Schema::table('sample_movements', function (Blueprint $table) {
            $table->dropForeign(['inspection_run_id']);
            $table->dropColumn('inspection_run_id');
        });

        Schema::dropIfExists('inspection_runs');
        Schema::dropIfExists('inspection_customer_orders');
        Schema::dropIfExists('inspection_samples');

        Schema::table('inspections', function (Blueprint $table) {
            $table->foreignId('sample_id')->after('id')->constrained('samples')->cascadeOnDelete();
            $table->foreignId('inspection_type_id')->nullable()->after('sample_id')
                ->constrained('inspection_types')->nullOnDelete();
        });
    }
};
