<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('sample_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('sample_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('sample_colors')->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained('sample_sizes')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });

        // Remove old single-value color/size/unit/quantity from samples
        Schema::table('samples', function (Blueprint $table) {
            $table->dropColumn(['color', 'size', 'unit', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->string('color')->nullable()->after('article');
            $table->string('size')->nullable()->after('color');
            $table->string('unit')->nullable()->after('size');
            $table->unsignedInteger('quantity')->default(1)->after('receive_date');
        });

        Schema::dropIfExists('sample_variations');
        Schema::dropIfExists('sample_sizes');
        Schema::dropIfExists('sample_colors');
    }
};
