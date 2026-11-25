<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QC proposal a1: each severity's own plan (an arrow can give it a larger sample) and the
 * standard used are recorded on the run and printed. Additive: nullable columns only;
 * existing rows keep NULL until they are saved again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_run_aql', function (Blueprint $table) {
            foreach (['critical', 'major', 'minor'] as $severity) {
                $table->unsignedSmallInteger("sample_size_{$severity}")->nullable();
                $table->string("code_letter_{$severity}", 2)->nullable();
            }
            $table->string('standard_ref', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inspection_run_aql', function (Blueprint $table) {
            $table->dropColumn([
                'sample_size_critical', 'code_letter_critical',
                'sample_size_major', 'code_letter_major',
                'sample_size_minor', 'code_letter_minor',
                'standard_ref',
            ]);
        });
    }
};
