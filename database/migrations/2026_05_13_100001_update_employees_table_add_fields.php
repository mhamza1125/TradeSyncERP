<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('father_name')->nullable()->after('employee_name');
            $table->string('nic')->nullable()->after('phone');
            $table->date('dob')->nullable()->after('nic');
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable()->after('dob');
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable()->after('gender');
            $table->string('emergency_contact')->nullable()->after('marital_status');
            $table->text('address')->nullable()->after('emergency_contact');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('country');
            $table->date('hire_date')->nullable()->after('joining_date');
            $table->string('job_title')->nullable()->after('designation');
            $table->decimal('salary', 15, 2)->nullable()->after('basic_salary');
            $table->text('remarks')->nullable()->after('salary');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'father_name', 'nic', 'dob', 'gender', 'marital_status',
                'emergency_contact', 'address', 'city', 'country', 'postal_code',
                'hire_date', 'job_title', 'salary', 'remarks',
            ]);
        });
    }
};
