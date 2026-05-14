<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->string('sample_code')->unique();
            $table->foreignId('category_id')->constrained('product_categories');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('brand_id')->constrained('brands');
            $table->string('product_name');
            $table->string('sample_reference')->nullable();
            $table->string('physical_location')->nullable();
            $table->string('main_image')->nullable();
            $table->date('receive_date');
            $table->unsignedInteger('quantity');
            $table->enum('priority_level', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->unsignedInteger('alert_days')->default(7);
            $table->enum('status', ['Received', 'In Testing', 'Completed', 'Returned'])->default('Received');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
