<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aql_calculations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedInteger('lot_size');
            $table->string('inspection_level')->default('II');

            $table->string('aql_critical')->nullable();
            $table->string('aql_major')->nullable();
            $table->string('aql_minor')->nullable();

            $table->string('code_letter')->nullable();
            $table->unsignedInteger('sample_size')->nullable();

            $table->unsignedInteger('ac_critical')->nullable();
            $table->unsignedInteger('re_critical')->nullable();
            $table->unsignedInteger('ac_major')->nullable();
            $table->unsignedInteger('re_major')->nullable();
            $table->unsignedInteger('ac_minor')->nullable();
            $table->unsignedInteger('re_minor')->nullable();

            $table->unsignedInteger('found_critical')->default(0);
            $table->unsignedInteger('found_major')->default(0);
            $table->unsignedInteger('found_minor')->default(0);

            $table->string('verdict')->default('Pending');
            $table->json('variations')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aql_calculations');
    }
};
