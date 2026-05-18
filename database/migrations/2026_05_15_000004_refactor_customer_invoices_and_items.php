<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove foreign currency details from invoices
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropForeign(['foreign_currency_id']);
            $table->dropColumn(['foreign_currency_id', 'exchange_rate', 'foreign_amount']);
        });

        // Rework invoice items: supplier + inspection_type + po_invoice_no + date + amount
        Schema::table('customer_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'quantity', 'unit_price', 'line_total']);
            $table->foreignId('supplier_id')
                ->nullable()
                ->after('customer_invoice_id')
                ->constrained('suppliers')
                ->nullOnDelete();
            $table->foreignId('inspection_type_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('inspection_types')
                ->nullOnDelete();
            $table->string('po_invoice_no')->nullable()->after('inspection_type_id');
            $table->date('item_date')->nullable()->after('po_invoice_no');
            $table->decimal('amount', 15, 2)->default(0)->after('item_date');
        });
    }

    public function down(): void
    {
        Schema::table('customer_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['inspection_type_id']);
            $table->dropColumn(['supplier_id', 'inspection_type_id', 'po_invoice_no', 'item_date', 'amount']);
            $table->text('description')->after('customer_invoice_id');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
        });

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->foreignId('foreign_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('exchange_rate', 15, 6)->nullable();
            $table->decimal('foreign_amount', 15, 2)->nullable();
        });
    }
};
