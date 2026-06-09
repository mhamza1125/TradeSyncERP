<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add severity ENUM directly on defects table
        Schema::table('defects', function (Blueprint $table) {
            $table->enum('severity', ['critical', 'major', 'minor', 'functional'])
                ->nullable()
                ->after('defect_name');
        });

        // Drop FK and category column from defects (if it exists)
        if (Schema::hasColumn('defects', 'defect_category_id')) {
            Schema::table('defects', function (Blueprint $table) {
                $table->dropForeign(['defect_category_id']);
                $table->dropColumn('defect_category_id');
            });
        }

        // Drop the categories table
        Schema::dropIfExists('defect_categories');
    }

    public function down(): void
    {
        Schema::create('defect_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 4);
            $table->text('description')->nullable();
            $table->string('color', 20)->default('secondary');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('defects', function (Blueprint $table) {
            $table->dropColumn('severity');
            $table->foreignId('defect_category_id')
                ->nullable()
                ->after('id')
                ->constrained('defect_categories')
                ->nullOnDelete();
        });
    }
};
