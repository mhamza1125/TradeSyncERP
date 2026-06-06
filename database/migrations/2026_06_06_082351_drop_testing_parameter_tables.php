<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('inspection_results');
        Schema::dropIfExists('sample_testing_parameters');
        Schema::dropIfExists('testing_parameters_master');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Tables are intentionally not recreated — this module has been removed.
    }
};
