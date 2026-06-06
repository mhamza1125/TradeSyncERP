<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. inspections: add inspection_type_id ───────────────────────────────
        if (!Schema::hasColumn('inspections', 'inspection_type_id')) {
            Schema::table('inspections', function (Blueprint $table) {
                $table->foreignId('inspection_type_id')
                    ->nullable()
                    ->after('report_number')
                    ->constrained('inspection_types')
                    ->nullOnDelete();
            });
        }

        // ── 2. inspection_runs: add sample_id, drop inspection_type_id ──────────
        Schema::table('inspection_runs', function (Blueprint $table) {
            if (!Schema::hasColumn('inspection_runs', 'sample_id')) {
                $table->foreignId('sample_id')
                    ->nullable()
                    ->after('inspection_id')
                    ->constrained('samples')
                    ->nullOnDelete();
            }

            if (Schema::hasColumn('inspection_runs', 'inspection_type_id')) {
                $table->dropForeign(['inspection_type_id']);
                $table->dropColumn('inspection_type_id');
            }
        });

        // ── 3. inspection_type_section_defaults: add category_id ────────────────
        if (!Schema::hasColumn('inspection_type_section_defaults', 'category_id')) {
            // Must drop FK constraints before dropping the unique index they depend on
            Schema::table('inspection_type_section_defaults', function (Blueprint $table) {
                $table->dropForeign(['inspection_type_id']);
                $table->dropForeign(['inspection_section_id']);
                $table->dropUnique('itsd_type_section_unique');
            });

            Schema::table('inspection_type_section_defaults', function (Blueprint $table) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('inspection_section_id')
                    ->constrained('product_categories')
                    ->nullOnDelete();

                // Recreate FK constraints (non-unique indexes added automatically)
                $table->foreign('inspection_type_id')
                    ->references('id')->on('inspection_types')
                    ->cascadeOnDelete();

                $table->foreign('inspection_section_id')
                    ->references('id')->on('inspection_sections')
                    ->cascadeOnDelete();

                // New unique: type + section + category
                $table->unique(
                    ['inspection_type_id', 'inspection_section_id', 'category_id'],
                    'itsd_type_section_category_unique'
                );
            });
        }
    }

    public function down(): void
    {
        // Reverse category_id from section defaults
        if (Schema::hasColumn('inspection_type_section_defaults', 'category_id')) {
            Schema::table('inspection_type_section_defaults', function (Blueprint $table) {
                try {
                    $table->dropUnique('itsd_type_section_category_unique');
                } catch (\Exception $e) {}
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
                $table->unique(
                    ['inspection_type_id', 'inspection_section_id'],
                    'itsd_type_section_unique'
                );
            });
        }

        // Restore inspection_type_id on inspection_runs
        Schema::table('inspection_runs', function (Blueprint $table) {
            if (Schema::hasColumn('inspection_runs', 'sample_id')) {
                $table->dropForeign(['sample_id']);
                $table->dropColumn('sample_id');
            }

            if (!Schema::hasColumn('inspection_runs', 'inspection_type_id')) {
                $table->foreignId('inspection_type_id')
                    ->nullable()
                    ->after('inspection_id')
                    ->constrained('inspection_types')
                    ->nullOnDelete();
            }
        });

        // Remove inspection_type_id from inspections
        if (Schema::hasColumn('inspections', 'inspection_type_id')) {
            Schema::table('inspections', function (Blueprint $table) {
                $table->dropForeign(['inspection_type_id']);
                $table->dropColumn('inspection_type_id');
            });
        }
    }
};
