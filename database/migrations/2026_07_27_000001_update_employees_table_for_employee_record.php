<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['job_title', 'hire_date']);
            $table->enum('employee_type', ['CONTRACTUAL', 'PERMANENT'])->nullable()->after('designation');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('employee_type');
            $table->string('job_title')->nullable()->after('designation');
            $table->date('hire_date')->nullable()->after('joining_date');
        });
    }
};
