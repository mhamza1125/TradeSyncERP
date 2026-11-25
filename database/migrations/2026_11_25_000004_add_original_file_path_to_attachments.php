<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QC proposal i2: when a photo is cropped/rotated, the original file is kept and its
 * path recorded here. Additive: one nullable column; existing rows unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('original_file_path')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn('original_file_path');
        });
    }
};
