<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->date('payment_date');
            $table->string('invoice_reference')->nullable();
            $table->string('foreign_currency', 10);
            $table->decimal('invoiced_amount_fc', 15, 2);
            $table->decimal('deduction_fc', 15, 2)->default(0);
            $table->decimal('received_fc', 15, 2);
            $table->decimal('exchange_rate', 15, 6);
            $table->decimal('expected_pkr', 15, 2);
            $table->decimal('actual_pkr_received', 15, 2);
            $table->decimal('pkr_gain_loss', 15, 2)->default(0);
            $table->decimal('fc_gain_loss', 15, 2)->default(0);
            $table->foreignId('account_id')->constrained('accounts');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
