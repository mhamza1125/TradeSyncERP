<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_type_section_defaults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_type_id')
                ->constrained('inspection_types')
                ->cascadeOnDelete();
            $table->foreignId('inspection_section_id')
                ->constrained('inspection_sections')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(false);  // if true, user cannot deselect
            $table->timestamps();

            $table->unique(
                ['inspection_type_id', 'inspection_section_id'],
                'itsd_type_section_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_type_section_defaults');
    }
};
