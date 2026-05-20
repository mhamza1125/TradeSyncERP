<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_runs', function (Blueprint $table) {
            $table->unsignedInteger('working_days')->nullable()->after('month');
            $table->unsignedInteger('off_days')->nullable()->after('working_days');
            $table->text('remarks')->nullable()->after('off_days');
        });
    }

    public function down(): void
    {
        Schema::table('salary_runs', function (Blueprint $table) {
            $table->dropColumn(['working_days', 'off_days', 'remarks']);
        });
    }
};
