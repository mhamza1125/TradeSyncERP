<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->string('moved_by_type');
            $table->unsignedBigInteger('moved_by_id');
            $table->enum('assigned_to_type', ['Employee', 'Vendor', 'Storage', 'Customer']);
            $table->unsignedBigInteger('assigned_to_id');
            $table->date('issue_date');
            $table->date('expected_return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->unsignedInteger('alert_days')->nullable();
            $table->enum('status', ['Issued', 'Returned', 'Overdue'])->default('Issued');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_movements');
    }
};
