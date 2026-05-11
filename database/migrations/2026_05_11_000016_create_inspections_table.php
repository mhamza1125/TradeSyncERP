<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->string('report_number')->unique();
            $table->date('inspection_date');
            $table->enum('inspector_type', ['Employee', 'Vendor']);
            $table->unsignedBigInteger('inspector_id');
            $table->enum('overall_status', ['Pass', 'Fail', 'Pending'])->default('Pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
