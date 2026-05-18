<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add brand text field to customers
        Schema::table('customers', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('address');
        });

        // Drop brand_id FK from samples
        Schema::table('samples', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });

        // Drop brand_id FK from customer_orders
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });

        // Drop brands table
        Schema::dropIfExists('brands');
    }

    public function down(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('brand_name');
            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::table('samples', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('customer_id')->constrained('brands')->nullOnDelete();
        });

        Schema::table('customer_orders', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('customer_id')->constrained('brands')->nullOnDelete();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
