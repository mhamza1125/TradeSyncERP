<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_order_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'unit']);
            $table->foreignId('product_category_id')
                ->nullable()
                ->after('customer_order_id')
                ->constrained('product_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_order_items', function (Blueprint $table) {
            $table->dropForeign(['product_category_id']);
            $table->dropColumn('product_category_id');
            $table->string('product_name')->after('customer_order_id');
            $table->string('unit', 50)->nullable();
        });
    }
};
