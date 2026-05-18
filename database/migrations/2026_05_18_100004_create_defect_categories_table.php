<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('defect_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // Critical, Major, Minor, Functional
            $table->string('code', 4);         // CR, MA, MI, FN
            $table->text('description')->nullable();
            $table->string('color', 20)->default('secondary');  // Bootstrap color for badges
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Add category FK to defects table
        Schema::table('defects', function (Blueprint $table) {
            $table->foreignId('defect_category_id')
                ->nullable()
                ->after('id')
                ->constrained('defect_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('defects', function (Blueprint $table) {
            $table->dropForeign(['defect_category_id']);
            $table->dropColumn('defect_category_id');
        });

        Schema::dropIfExists('defect_categories');
    }
};
