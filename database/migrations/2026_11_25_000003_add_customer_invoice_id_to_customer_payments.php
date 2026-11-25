<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Finance review FN-3: a payment is linked to its invoice by id, not only by text.
 * Additive: one nullable column. Existing rows keep NULL (no backfill); their text
 * reference is still honoured by CustomerInvoiceController::syncInvoiceStatus().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->foreignId('customer_invoice_id')->nullable()->after('customer_id')
                ->constrained('customer_invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_invoice_id');
        });
    }
};
