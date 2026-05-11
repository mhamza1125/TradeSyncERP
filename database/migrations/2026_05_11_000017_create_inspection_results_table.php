<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->foreignId('sample_testing_parameter_id')->constrained('sample_testing_parameters')->cascadeOnDelete();
            $table->string('actual_result');
            $table->enum('pass_fail', ['Pass', 'Fail']);
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_results');
    }
};
