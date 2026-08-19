<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-size input detail feeding an inspection_run_aql row. InspectionRunAql
     * stays the 1:1 aggregate/output as it is today (lot_size, sample_size,
     * found_critical/major/minor, verdict) — this table is additive: it does
     * not replace or auto-populate those columns, it only persists the
     * size×quantity grid an inspector fills in and exposes summed
     * checked_qty/error_qty for display.
     */
    public function up(): void
    {
        Schema::create('inspection_run_aql_size_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_aql_id')
                ->constrained('inspection_run_aql')
                ->cascadeOnDelete();
            $table->string('size_label', 50);
            $table->unsignedInteger('order_qty')->default(0);
            $table->unsignedInteger('checked_qty')->default(0);
            $table->unsignedInteger('error_qty')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_run_aql_size_breakdowns');
    }
};
