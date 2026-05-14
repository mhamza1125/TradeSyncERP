<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Add inspection_type_id, drop old inspector_type/inspector_id
            $table->foreignId('inspection_type_id')->nullable()->after('sample_id')
                ->constrained('inspection_types')->nullOnDelete();
            $table->dropColumn(['inspector_type', 'inspector_id']);
        });
    }

    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropForeign(['inspection_type_id']);
            $table->dropColumn('inspection_type_id');
            $table->enum('inspector_type', ['Employee', 'Vendor'])->after('inspection_date');
            $table->unsignedBigInteger('inspector_id')->after('inspector_type');
        });
    }
};
