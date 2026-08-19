<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promotes defect recordings (previously a flat JSON array inside
     * InspectionRunSection.data['selections']) to a real child table, so
     * defect rates can be queried/aggregated by size, disposition, etc.
     * across inspections instead of scanning JSON blobs.
     *
     * severity is a snapshot copied from defects.severity at the time the
     * row is recorded — same pattern the old JSON shape already used —
     * so historical rows keep their classification even if the catalog
     * Defect is edited or removed later.
     */
    public function up(): void
    {
        Schema::create('inspection_run_defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_run_section_id')
                ->constrained('inspection_run_sections')
                ->cascadeOnDelete();
            $table->foreignId('defect_id')
                ->nullable()
                ->constrained('defects')
                ->nullOnDelete();
            $table->enum('severity', ['critical', 'major', 'minor', 'functional'])->nullable();
            $table->string('size', 50)->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->string('carton_no', 50)->nullable();
            $table->enum('status', ['open', 'rectified', 'rejected'])->default('open');
            $table->enum('disposition_code', ['MACDF', 'MACSO', 'MACDE'])->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_run_defects');
    }
};
