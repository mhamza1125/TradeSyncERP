<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->string('order_number')->nullable()->after('recipient_id');
            $table->foreignId('inspection_type_id')->nullable()->after('order_number')
                ->constrained('inspection_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['inspection_type_id']);
            $table->dropColumn(['order_number', 'inspection_type_id']);
        });
    }
};
