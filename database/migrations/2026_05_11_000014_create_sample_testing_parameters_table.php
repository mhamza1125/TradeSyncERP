<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_testing_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->foreignId('parameter_id')->constrained('testing_parameters_master');
            $table->string('requirement_standard')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_testing_parameters');
    }
};
