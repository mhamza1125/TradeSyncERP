<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movement_id')->constrained('movements')->cascadeOnDelete();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->foreignId('sample_variation_id')->nullable()->constrained('sample_variations')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->date('actual_return_date')->nullable();
            $table->enum('status', ['Issued', 'Returned', 'Overdue'])->nullable(); // null = inherit parent
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movement_items');
    }
};
