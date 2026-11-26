<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * salary_run_lines.net_payable left out late_deduction, while the run total (what is paid)
 * subtracted it, so a line's net pay disagreed with the run total whenever an employee had a
 * late deduction. The stored column is recomputed for every row (MySQL generated column).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE salary_run_lines DROP COLUMN net_payable');
        DB::statement('ALTER TABLE salary_run_lines ADD COLUMN net_payable DECIMAL(15,2) GENERATED ALWAYS AS '
            .'(basic_salary + bonus + allowances - deduction - advance - leave_deduction_amount - loan_deduction - late_deduction) STORED');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE salary_run_lines DROP COLUMN net_payable');
        DB::statement('ALTER TABLE salary_run_lines ADD COLUMN net_payable DECIMAL(15,2) GENERATED ALWAYS AS '
            .'(basic_salary + bonus + allowances - deduction - advance - leave_deduction_amount - loan_deduction) STORED');
    }
};
