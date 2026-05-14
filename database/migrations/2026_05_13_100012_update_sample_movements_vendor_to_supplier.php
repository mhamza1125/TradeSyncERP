<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: modify the enum to replace Vendor with Supplier
        DB::statement("ALTER TABLE sample_movements MODIFY assigned_to_type ENUM('Employee','Supplier','Storage','Customer') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sample_movements MODIFY assigned_to_type ENUM('Employee','Vendor','Storage','Customer') NOT NULL");
    }
};
