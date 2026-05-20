<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop generated column before altering the table
        Schema::table('salary_run_lines', function (Blueprint $table) {
            $table->dropColumn('net_payable');
        });

        Schema::table('salary_run_lines', function (Blueprint $table) {
            $table->decimal('allowances', 15, 2)->default(0)->after('advance');
            $table->unsignedInteger('total_leaves')->default(0)->after('leave_deduction_amount');
            $table->unsignedInteger('deductible_leaves')->default(0)->after('total_leaves');
            $table->decimal('loan_balance', 15, 2)->default(0)->after('deductible_leaves');
            $table->decimal('loan_deduction', 15, 2)->default(0)->after('loan_balance');
        });

        // Re-add net_payable with updated formula including allowances and loan_deduction
        DB::statement('ALTER TABLE salary_run_lines ADD COLUMN net_payable DECIMAL(15,2) GENERATED ALWAYS AS (basic_salary + bonus + allowances - deduction - advance - leave_deduction_amount - loan_deduction) STORED');
    }

    public function down(): void
    {
        Schema::table('salary_run_lines', function (Blueprint $table) {
            $table->dropColumn('net_payable');
            $table->dropColumn(['allowances', 'total_leaves', 'deductible_leaves', 'loan_balance', 'loan_deduction']);
        });

        DB::statement('ALTER TABLE salary_run_lines ADD COLUMN net_payable DECIMAL(15,2) GENERATED ALWAYS AS (basic_salary + bonus - deduction - advance - leave_deduction_amount) STORED');
    }
};
