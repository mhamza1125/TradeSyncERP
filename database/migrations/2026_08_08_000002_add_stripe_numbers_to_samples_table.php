<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->string('company_stripe_number')->nullable()->after('sample_reference');
            $table->string('customer_stripe_number')->nullable()->after('company_stripe_number');
        });
    }

    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->dropColumn(['company_stripe_number', 'customer_stripe_number']);
        });
    }
};
