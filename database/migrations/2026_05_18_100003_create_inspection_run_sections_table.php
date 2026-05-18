<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_run_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_run_id')
                ->constrained('inspection_runs')
                ->cascadeOnDelete();
            $table->foreignId('inspection_section_id')
                ->constrained('inspection_sections')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('data')->nullable();            // section-specific field data
            $table->enum('status', ['pending', 'complete', 'na'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['inspection_run_id', 'inspection_section_id'],
                'irs_run_section_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_run_sections');
    }
};
