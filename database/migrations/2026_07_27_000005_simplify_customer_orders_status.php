<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE customer_orders MODIFY status VARCHAR(20) NOT NULL DEFAULT 'Due'");

        DB::table('customer_orders')->update(['status' => 'Due']);

        DB::statement("ALTER TABLE customer_orders MODIFY status ENUM('Due','Done') NOT NULL DEFAULT 'Due'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE customer_orders MODIFY status VARCHAR(20) NOT NULL DEFAULT 'Draft'");

        DB::table('customer_orders')->update(['status' => 'Draft']);

        DB::statement("ALTER TABLE customer_orders MODIFY status ENUM('Draft','Confirmed','Processing','Dispatched','Cancelled') NOT NULL DEFAULT 'Draft'");
    }
};
