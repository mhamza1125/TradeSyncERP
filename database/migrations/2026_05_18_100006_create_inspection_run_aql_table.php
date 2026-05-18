<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_run_aql', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_run_id')
                ->unique()
                ->constrained('inspection_runs')
                ->cascadeOnDelete();

            $table->unsignedInteger('lot_size')->default(0);
            $table->enum('inspection_level', ['I', 'II', 'III', 'S1', 'S2', 'S3', 'S4'])
                ->default('II');
            $table->unsignedSmallInteger('sample_size')->default(0);
            $table->string('code_letter', 2)->nullable();   // A, B, C … Q

            // AQL levels (e.g., 0.065, 2.5, 4.0)
            $table->decimal('aql_critical', 5, 3)->nullable();
            $table->decimal('aql_major', 5, 3)->nullable();
            $table->decimal('aql_minor', 5, 3)->nullable();

            // Accept / Reject numbers per level
            $table->unsignedTinyInteger('ac_critical')->nullable();
            $table->unsignedTinyInteger('re_critical')->nullable();
            $table->unsignedTinyInteger('ac_major')->nullable();
            $table->unsignedTinyInteger('re_major')->nullable();
            $table->unsignedTinyInteger('ac_minor')->nullable();
            $table->unsignedTinyInteger('re_minor')->nullable();

            // Defects found
            $table->unsignedSmallInteger('found_critical')->default(0);
            $table->unsignedSmallInteger('found_major')->default(0);
            $table->unsignedSmallInteger('found_minor')->default(0);

            $table->enum('verdict', ['Pending', 'Pass', 'Fail'])->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_run_aql');
    }
};
