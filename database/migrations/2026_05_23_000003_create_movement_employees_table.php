<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movement_employees', function (Blueprint $table) {
            $table->foreignId('movement_id')->constrained('movements')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->primary(['movement_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movement_employees');
    }
};
