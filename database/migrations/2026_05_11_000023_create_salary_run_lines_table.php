<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_run_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_run_id')->constrained('salary_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees');
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('deduction', 15, 2)->default(0);
            $table->decimal('advance', 15, 2)->default(0);
            $table->decimal('net_payable', 15, 2)->storedAs('basic_salary + bonus - deduction - advance');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['salary_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_run_lines');
    }
};
