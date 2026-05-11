<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_head_id')->constrained('expense_heads');
            $table->foreignId('account_id')->constrained('accounts');
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
