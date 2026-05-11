<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['Expense', 'Salary', 'VendorPayment', 'CustomerReceipt', 'JournalEntry']);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('debit_account_id')->constrained('accounts');
            $table->foreignId('credit_account_id')->constrained('accounts');
            $table->decimal('amount', 15, 2);
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
