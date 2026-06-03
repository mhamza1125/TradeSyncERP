<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_run_lines', function (Blueprint $table) {
            $table->unsignedTinyInteger('late_hours')->default(0)->after('loan_deduction');
            $table->unsignedTinyInteger('late_minutes')->default(0)->after('late_hours');
            $table->decimal('late_deduction_calculated', 10, 2)->default(0)->after('late_minutes');
            $table->decimal('late_deduction', 10, 2)->default(0)->after('late_deduction_calculated');
        });
    }

    public function down(): void
    {
        Schema::table('salary_run_lines', function (Blueprint $table) {
            $table->dropColumn(['late_hours', 'late_minutes', 'late_deduction_calculated', 'late_deduction']);
        });
    }
};
