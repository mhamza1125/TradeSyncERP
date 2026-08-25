<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->decimal('wh_tax_percent', 5, 2)->default(1)->after('received_fc');
            $table->decimal('wh_tax_amount_fc', 15, 2)->default(0)->after('wh_tax_percent');
            $table->decimal('remittance_charges', 15, 2)->default(0)->after('actual_pkr_received');
        });
    }

    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropColumn(['wh_tax_percent', 'wh_tax_amount_fc', 'remittance_charges']);
        });
    }
};
