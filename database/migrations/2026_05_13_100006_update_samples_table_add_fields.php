<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->string('article')->nullable()->after('product_name');
            $table->string('color')->nullable()->after('article');
            $table->string('size')->nullable()->after('color');
            $table->string('unit')->nullable()->after('size');
            $table->foreignId('supplier_id')->nullable()->after('customer_id')
                ->constrained('suppliers')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->after('supplier_id')
                ->constrained('employees')->nullOnDelete();
            $table->string('source')->nullable()->after('physical_location');
            $table->string('rack')->nullable()->after('source');
            $table->string('position')->nullable()->after('rack');
        });
    }

    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['article', 'color', 'size', 'unit', 'supplier_id', 'received_by', 'source', 'rack', 'position']);
        });
    }
};
