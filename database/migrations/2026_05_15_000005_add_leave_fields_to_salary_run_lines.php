<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the generated column before altering the table
        Schema::table('salary_run_lines', function (Blueprint $table) {
            $table->dropColumn('net_payable');
        });

        Schema::table('salary_run_lines', function (Blueprint $table) {
            $table->unsignedInteger('leave_days')->default(0)->after('advance');
            $table->decimal('leave_deduction_amount', 15, 2)->default(0)->after('leave_days');
        });

        // Re-add as stored generated column with leave_deduction_amount included
        DB::statement('ALTER TABLE salary_run_lines ADD COLUMN net_payable DECIMAL(15,2) GENERATED ALWAYS AS (basic_salary + bonus - deduction - advance - leave_deduction_amount) STORED');
    }

    public function down(): void
    {
        Schema::table('salary_run_lines', function (Blueprint $table) {
            $table->dropColumn('net_payable');
            $table->dropColumn(['leave_days', 'leave_deduction_amount']);
        });

        DB::statement('ALTER TABLE salary_run_lines ADD COLUMN net_payable DECIMAL(15,2) GENERATED ALWAYS AS (basic_salary + bonus - deduction - advance) STORED');
    }
};
