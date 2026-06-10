<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_run_aql', function (Blueprint $table) {
            $table->json('variations')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_run_aql', function (Blueprint $table) {
            $table->dropColumn('variations');
        });
    }
};
