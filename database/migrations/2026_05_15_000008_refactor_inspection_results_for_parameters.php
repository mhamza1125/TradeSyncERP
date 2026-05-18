<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_results', function (Blueprint $table) {
            $table->foreignId('sample_id')
                ->nullable()
                ->after('inspection_run_id')
                ->constrained('samples')
                ->nullOnDelete();

            $table->foreignId('testing_parameter_id')
                ->nullable()
                ->after('sample_id')
                ->constrained('testing_parameters_master')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_results', function (Blueprint $table) {
            $table->dropForeign(['sample_id']);
            $table->dropForeign(['testing_parameter_id']);
            $table->dropColumn(['sample_id', 'testing_parameter_id']);
        });
    }
};
